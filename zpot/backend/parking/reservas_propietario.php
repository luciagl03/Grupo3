<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../sesion/login.php');
    exit;
}

require_once '../sesion/conexion.php';

$dni = $_SESSION['dni'];

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
    <style>
        .estado-completada { background:#d1fae5; color:#065f46; }

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
                <i data-lucide="arrow-left"></i> Volver a mis plazas
            </a>
            <h1 class="headline">Reservas en mis plazas</h1>
            <p class="support">Gestiona las reservas que han hecho otros usuarios en tus plazas y chatea con ellos.</p>
        </header>

        <?php if (empty($reservas)): ?>
            <div class="empty-state">
                <i data-lucide="calendar-off"></i>
                <p>Aún no hay reservas confirmadas en tus plazas.</p>
                <a href="mis_plazas.php" class="btn-primary-link">Ver mis plazas</a>
            </div>
        <?php else: ?>

        <div class="reservas-grid">
            <?php foreach ($reservas as $row):
                $estado = $row['Estado'];
                $fechaReserva = strtotime($row['Fecha'] . ' ' . $row['Hora_salida']);
                $estaCompletada = ($estado === 'confirmada' && $fechaReserva < time());
                $estadoMostrado = $estaCompletada ? 'completada' : $estado;

                $estadoLabel = [
                    'confirmada'  => 'Confirmada',
                    'cancelada'   => 'Cancelada',
                    'completada'  => 'Completada',
                ];
                $estadoClass = [
                    'confirmada'  => 'estado-confirmada',
                    'cancelada'   => 'estado-cancelada',
                    'completada'  => 'estado-completada',
                ];

                $inicialInquilino = mb_strtoupper(mb_substr($row['nombre_inquilino'], 0, 1));
                $nombreCorto = htmlspecialchars($row['nombre_inquilino'] . ' ' . mb_substr($row['apellidos_inquilino'], 0, 1) . '.');
                $noLeidos = (int)$row['no_leidos'];
            ?>
            <div class="card reserva-card">
                <div class="reserva-header">
                    <div class="plaza-tag">
                        <?php echo htmlspecialchars($row['Direccion'] ?? 'Plaza #' . $row['ID_plaza']); ?>
                    </div>
                    <div class="price-tag"><?php echo number_format($row['Precio'], 2); ?> €</div>
                </div>

                <div class="estado-badge <?php echo $estadoClass[$estadoMostrado] ?? 'estado-confirmada'; ?>">
                    <?php echo $estadoLabel[$estadoMostrado] ?? $estadoMostrado; ?>
                </div>

                <div class="reserva-body">
                    <div class="inquilino-info">
                        <div class="inquilino-avatar"><?php echo $inicialInquilino; ?></div>
                        <div>
                            <div class="inquilino-nombre"><?php echo $nombreCorto; ?></div>
                            <div class="inquilino-label">Inquilino</div>
                        </div>
                    </div>

                    <div class="info-row">
                        <i data-lucide="calendar"></i>
                        <div>
                            <span>Fecha</span>
                            <strong><?php echo date('d/m/Y', strtotime($row['Fecha'])); ?></strong>
                        </div>
                    </div>

                    <div class="info-row">
                        <i data-lucide="clock"></i>
                        <div>
                            <span>Horario</span>
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
                            Chat
                            <?php if ($noLeidos > 0): ?>
                                <span class="badge-unread"><?php echo $noLeidos; ?></span>
                            <?php endif; ?>
                        </a>
                    <?php else: ?>
                        <span style="font-size:0.82rem;color:#c0392b;font-weight:600;">Cancelada</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php endif; ?>
    </div>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
