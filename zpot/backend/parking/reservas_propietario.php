<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../sesion/login.php');
    exit;
}

require_once '../sesion/conexion.php';

$dni = $_SESSION['dni'];

// Obtener IDs de reservas que tienen notificaciones no leídas (primera vez que se ven)
$stmtNuevas = $_conexion->prepare(
    "SELECT ID_ref FROM NOTIFICACION 
     WHERE DNI = ? AND Tipo = 'nueva_reserva_propietario' AND Leida = 0"
);
$stmtNuevas->bind_param('s', $dni);
$stmtNuevas->execute();
$resultNuevas = $stmtNuevas->get_result();
$reservasNuevas = [];
while ($rowNueva = $resultNuevas->fetch_assoc()) {
    if ($rowNueva['ID_ref']) {
        $reservasNuevas[] = (int)$rowNueva['ID_ref'];
    }
}
$stmtNuevas->close();

// Marcar como leídas las notificaciones de nuevas reservas al entrar a esta página
$stmtMarkRead = $_conexion->prepare(
    "UPDATE NOTIFICACION SET Leida = 1 
     WHERE DNI = ? AND Tipo = 'nueva_reserva_propietario' AND Leida = 0"
);
$stmtMarkRead->bind_param('s', $dni);
$stmtMarkRead->execute();
$stmtMarkRead->close();

// Reservas confirmadas en plazas del propietario, con nombre del inquilino y mensajes no leídos
$sql = "SELECT r.ID_reserva, r.Fecha, r.Hora_entrada, r.Hora_salida, r.Precio, r.Estado,
               p.ID_plaza, p.Direccion,
               u.Nombre AS nombre_inquilino, u.Apellidos AS apellidos_inquilino,
               (SELECT COUNT(*) FROM MENSAJE m
                WHERE m.ID_reserva = r.ID_reserva
                  AND m.DNI_emisor != ?
                  AND m.Leido = 0) AS no_leidos
        FROM RESERVA r
        JOIN PLAZA p  ON r.ID_plaza = p.ID_plaza
        JOIN USUARIO u ON r.DNI = u.DNI
        WHERE p.DNI = ? AND r.Estado IN ('confirmada', 'cancelada')
        ORDER BY r.Fecha DESC, r.Hora_entrada DESC";

$stmt = $_conexion->prepare($sql);
$stmt->bind_param('ss', $dni, $dni);
$stmt->execute();
$result = $stmt->get_result();

$total_no_leidos = 0;
$reservas = [];
while ($row = $result->fetch_assoc()) {
    $total_no_leidos += (int)$row['no_leidos'];
    $reservas[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservas en mis plazas — Zpot</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>

    <link rel="stylesheet" href="../app.css">
    <link rel="stylesheet" href="../styles/mis_reservas.css">
    <script src="../translations.js"></script>
    <style>
        .estado-completada { background:#d1fae5; color:#065f46; }

        /* Animación sutil para reservas nuevas */
        @keyframes nuevaReservaGlow {
            0%, 100% { 
                box-shadow: 0 2px 8px rgba(0,0,0,0.08), 0 0 0 0 rgba(244, 221, 73, 0);
            }
            50% { 
                box-shadow: 0 6px 20px rgba(0,0,0,0.15), 0 0 0 6px rgba(244, 221, 73, 0.25);
            }
        }
        
        .reserva-card.nueva {
            animation: nuevaReservaGlow 2s ease-in-out 3;
            background: linear-gradient(135deg, #fffbeb 0%, #fef9e7 100%);
            border: 2px solid rgba(244, 221, 73, 0.5);
            position: relative;
        }
        
        .reserva-card.nueva::before {
            content: attr(data-new-label);
            position: absolute;
            top: 12px;
            right: 12px;
            background: linear-gradient(135deg, #f4dd49 0%, #f5e642 100%);
            color: var(--brand-dark);
            font-size: 0.72rem;
            font-weight: 800;
            padding: 0.3rem 0.75rem;
            border-radius: 999px;
            z-index: 1;
            box-shadow: 0 3px 12px rgba(244, 221, 73, 0.4), 0 0 0 2px #fff;
            letter-spacing: 0.02em;
        }

        .chat-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.45rem 0.9rem;
            background: var(--brand-dark);
            color: var(--brand-yellow);
            border-radius: 999px;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 600;
            transition: background 0.15s;
            position: relative;
        }
        .chat-btn:hover { background: #1a1915; }

        .badge-unread {
            position: absolute;
            top: -6px;
            right: -6px;
            background: #ef4444;
            color: #fff;
            border-radius: 999px;
            font-size: 0.65rem;
            font-weight: 700;
            min-width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 4px;
        }

        .inquilino-info {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            margin-bottom: 0.75rem;
        }
        .inquilino-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--brand-yellow);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.85rem;
            color: var(--brand-dark);
            flex-shrink: 0;
        }
        .inquilino-nombre {
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--brand-dark);
        }
        .inquilino-label {
            font-size: 0.72rem;
            color: var(--text-muted);
        }
    </style>
</head>
<body class="my-reservations-page">

<div class="layout">
    <div class="layout-container">

        <header class="page-header">
            <a href="mis_plazas.php" class="back-link">
                <i data-lucide="arrow-left"></i> <span data-i18n="backToMySpots">Volver a mis plazas</span>
            </a>
            <h1 class="headline" data-i18n="receivedReservationsTitle">Reservas en mis plazas</h1>
            <p class="support" data-i18n="receivedReservationsSubtitle">Gestiona las reservas que han hecho otros usuarios en tus plazas y chatea con ellos.</p>
        </header>

        <?php if (empty($reservas)): ?>
            <div class="empty-state">
                <i data-lucide="calendar-off"></i>
                <p data-i18n="noReceivedReservations">Aún no hay reservas confirmadas en tus plazas.</p>
                <a href="mis_plazas.php" class="btn-primary-link" data-i18n="viewMySpots">Ver mis plazas</a>
            </div>
        <?php else: ?>

        <div class="reservas-grid">
            <?php foreach ($reservas as $row):
                $estado = $row['Estado'];
                $fechaReserva = strtotime($row['Fecha'] . ' ' . $row['Hora_salida']);
                $estaCompletada = ($estado === 'confirmada' && $fechaReserva < time());
                $estadoMostrado = $estaCompletada ? 'completada' : $estado;

                // Etiquetas de estado traducibles
                $estadoLabelKeys = [
                    'confirmada'  => 'statusConfirmed',
                    'cancelada'   => 'statusCancelled',
                    'completada'  => 'statusCompleted',
                ];
                $estadoClass = [
                    'confirmada'  => 'estado-confirmada',
                    'cancelada'   => 'estado-cancelada',
                    'completada'  => 'estado-completada',
                ];

                $inicialInquilino = mb_strtoupper(mb_substr($row['nombre_inquilino'], 0, 1));
                $nombreCorto = htmlspecialchars($row['nombre_inquilino'] . ' ' . mb_substr($row['apellidos_inquilino'], 0, 1) . '.');
                $noLeidos = (int)$row['no_leidos'];
                
                // Verificar si es una reserva nueva (recién vista)
                $esNueva = in_array((int)$row['ID_reserva'], $reservasNuevas);
            ?>
            <div class="card reserva-card<?php echo $esNueva ? ' nueva' : ''; ?>" <?php if ($esNueva): ?>data-new-label="<?php echo htmlspecialchars('Nueva'); ?>"<?php endif; ?>>
                <div class="reserva-header">
                    <div class="plaza-tag">
                        <?php echo htmlspecialchars($row['Direccion'] ?? 'Plaza #' . $row['ID_plaza']); ?>
                    </div>
                    <div class="price-tag"><?php echo number_format($row['Precio'], 2); ?> €</div>
                </div>

                <div class="estado-badge <?php echo $estadoClass[$estadoMostrado] ?? 'estado-confirmada'; ?>" data-i18n="<?php echo $estadoLabelKeys[$estadoMostrado] ?? ''; ?>">
                    <?php 
                    $defaultLabels = [
                        'confirmada'  => 'Confirmada',
                        'cancelada'   => 'Cancelada',
                        'completada'  => 'Completada',
                    ];
                    echo $defaultLabels[$estadoMostrado] ?? $estadoMostrado;
                    ?>
                </div>

                <div class="reserva-body">
                    <div class="inquilino-info">
                        <div class="inquilino-avatar"><?php echo $inicialInquilino; ?></div>
                        <div>
                            <div class="inquilino-nombre"><?php echo $nombreCorto; ?></div>
                            <div class="inquilino-label" data-i18n="tenantLabel">Inquilino</div>
                        </div>
                    </div>

                    <div class="info-row">
                        <i data-lucide="calendar"></i>
                        <div>
                            <span data-i18n="dateLabel">Fecha</span>
                            <strong><?php echo date('d/m/Y', strtotime($row['Fecha'])); ?></strong>
                        </div>
                    </div>

                    <div class="info-row">
                        <i data-lucide="clock"></i>
                        <div>
                            <span data-i18n="scheduleLabel">Horario</span>
                            <strong>
                                <?php echo substr($row['Hora_entrada'], 0, 5); ?> –
                                <?php echo substr($row['Hora_salida'],  0, 5); ?>
                            </strong>
                        </div>
                    </div>
                </div>

                <div class="reserva-footer">
                    <?php if ($estado === 'confirmada' || $estaCompletada): ?>
                        <a href="../chat/chat.php?id_reserva=<?php echo $row['ID_reserva']; ?>"
                           class="chat-btn">
                            <i data-lucide="message-circle" width="14" height="14"></i>
                            <span data-i18n="chatButton">Chat</span>
                            <?php if ($noLeidos > 0): ?>
                                <span class="badge-unread"><?php echo $noLeidos; ?></span>
                            <?php endif; ?>
                        </a>
                    <?php else: ?>
                        <span style="font-size:0.82rem;color:#c0392b;font-weight:600;" data-i18n="statusCancelled">Cancelada</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php endif; ?>
    </div>
</div>

<script>
    lucide.createIcons();
    
    // Actualizar el atributo data-new-label cuando cambie el idioma
    window.addEventListener('languageChanged', function() {
        const currentLang = getCurrentLanguage();
        const newLabel = t('newReservationBadge', currentLang);
        document.querySelectorAll('.reserva-card.nueva').forEach(card => {
            card.setAttribute('data-new-label', newLabel);
        });
    });
    
    // Inicializar el label al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        const currentLang = getCurrentLanguage();
        const newLabel = t('newReservationBadge', currentLang);
        document.querySelectorAll('.reserva-card.nueva').forEach(card => {
            card.setAttribute('data-new-label', newLabel);
        });
    });
</script>
</body>
</html>
