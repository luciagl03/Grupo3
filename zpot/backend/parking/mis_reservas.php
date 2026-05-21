<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: sesion/login.php');
    exit;
}

require_once '../sesion/conexion.php';

$dni = $_SESSION['dni'];

// Limpieza automática de reservas pendientes con fecha/hora pasada (más de 1 hora)
$sql_limpieza = "DELETE FROM RESERVA 
                 WHERE DNI = ? 
                 AND Estado = 'pendiente' 
                 AND CONCAT(Fecha, ' ', Hora_entrada) < DATE_SUB(NOW(), INTERVAL 1 HOUR)";
$stmt_limpieza = $_conexion->prepare($sql_limpieza);
if ($stmt_limpieza) {
    $stmt_limpieza->bind_param("s", $dni);
    $stmt_limpieza->execute();
    $stmt_limpieza->close();
}

$sql = "SELECT r.*, p.Direccion AS PlazaDireccion
        FROM RESERVA r
        LEFT JOIN PLAZA p ON r.ID_plaza = p.ID_plaza
        WHERE r.DNI = ?
        ORDER BY r.Fecha DESC, r.Hora_entrada DESC";

$stmt = $_conexion->prepare($sql);

if (!$stmt) {
    die("Error SQL: " . $_conexion->error);
}

$stmt->bind_param("s", $dni);
$stmt->execute();

$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis reservas</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>

    <link rel="stylesheet" href="../app.css">
    <link rel="stylesheet" href="../styles/mis_reservas.css">
    <script src="../translations.js"></script>
    
<style>
    .estado-completada { background:#d1fae5; color:#065f46; }
</style>
</head>
<body class="my-reservations-page">

<div class="layout">
    <div class="layout-container">
        
        <header class="page-header">
            <a href="../index.php" class="back-link"><i data-lucide="arrow-left"></i> <span data-i18n="backToMap">Volver al mapa</span></a>
            <h1 class="headline" data-i18n="myReservationsTitle">Mis reservas</h1>
            <p class="support" data-i18n="myReservationsSubtitle">Gestiona tus próximas estancias y revisa tu historial.</p>
        </header>

        <?php if ($result->num_rows === 0): ?>
            <div class="empty-state">
                <i data-lucide="calendar-off"></i>
                <p data-i18n="noReservationsYet">Aún no has realizado ninguna reserva.</p>
                <a href="../app.html" class="btn-primary-link" data-i18n="searchSpot">Buscar una plaza</a>
            </div>
        <?php else: ?>

        <div class="reservas-grid">
            <?php while ($row = $result->fetch_assoc()): ?>
                
                <?php
                    $estado = $row['Estado'] ?? 'pendiente';
                    // Si la reserva está confirmada y la fecha+hora_salida ya pasó, mostrarla como completada
                    $fechaReserva = strtotime($row['Fecha'] . ' ' . $row['Hora_salida']);
                    $estaCompletada = ($estado === 'confirmada' && $fechaReserva < time());
                    $estadoMostrado = $estaCompletada ? 'completada' : $estado;
                    // Etiquetas de estado traducibles
                    $estadoLabelKeys = [
                        'confirmada'  => 'statusConfirmed',
                        'pendiente'   => 'statusPending',
                        'cancelada'   => 'statusCancelled',
                        'completada'  => 'statusCompleted',
                    ];
                    $estadoClass = [
                        'confirmada'  => 'estado-confirmada',
                        'pendiente'   => 'estado-pendiente',
                        'cancelada'   => 'estado-cancelada',
                        'completada'  => 'estado-completada',
                    ];
                ?>
                <div class="card reserva-card">
                    <div class="reserva-header">
                        <div class="plaza-tag">
                            <?php echo htmlspecialchars($row['PlazaDireccion'] ?? 'Plaza #' . $row['ID_plaza']); ?>
                        </div>
                        <div class="price-tag"><?php echo number_format($row['Precio'], 2); ?> €</div>
                    </div>
                    <div class="estado-badge <?php echo $estadoClass[$estadoMostrado] ?? 'estado-pendiente'; ?>" data-i18n="<?php echo $estadoLabelKeys[$estadoMostrado] ?? ''; ?>">
                        <?php 
                        $defaultLabels = [
                            'confirmada'  => 'Confirmada',
                            'pendiente'   => 'Pendiente de pago',
                            'cancelada'   => 'Cancelada',
                            'completada'  => 'Completada',
                        ];
                        echo $defaultLabels[$estadoMostrado] ?? $estadoMostrado;
                        ?>
                    </div>

                    <div class="reserva-body">
                        <div class="info-row">
                            <i data-lucide="calendar"></i>
                            <div>
                                <span data-i18n="dateLabel">Fecha</span>
                                <strong><?php echo date("d/m/Y", strtotime($row['Fecha'])); ?></strong>
                            </div>
                        </div>

                        <div class="info-row">
                            <i data-lucide="clock"></i>
                            <div>
                                <span data-i18n="scheduleLabel">Horario</span>
                                <strong><?php echo substr($row['Hora_entrada'], 0, 5); ?> - <?php echo substr($row['Hora_salida'], 0, 5); ?></strong>
                            </div>
                        </div>

                        <div class="info-row">
                            <i data-lucide="timer"></i>
                            <div>
                                <span data-i18n="durationLabel">Duración</span>
                                <strong><?php echo $row['Duracion']; ?> <span data-i18n="hoursLabel">horas</span></strong>
                            </div>
                        </div>
                    </div>

                    <div class="reserva-footer">
                        <div style="display:flex;gap:0.5rem;align-items:center;flex-wrap:wrap;">
                            <?php if ($estado === 'confirmada' && !$estaCompletada): ?>
                                <a href="../chat/chat.php?id_reserva=<?php echo $row['ID_reserva']; ?>" class="btn-cancel" style="text-decoration:none;display:inline-flex;align-items:center;gap:0.4rem;">
                                    <i data-lucide="message-circle" width="14" height="14"></i> <span data-i18n="chatButton">Chat</span>
                                </a>
                            <?php endif; ?>
                            <?php if ($estaCompletada): ?>
                                <a href="../chat/chat.php?id_reserva=<?php echo $row['ID_reserva']; ?>" class="btn-cancel" style="text-decoration:none;display:inline-flex;align-items:center;gap:0.4rem;">
                                    <i data-lucide="message-circle" width="14" height="14"></i> <span data-i18n="chatButton">Chat</span>
                                </a>
                                <div style="display:flex;align-items:center;gap:0.4rem;font-size:0.82rem;color:#065f46;font-weight:600;padding:0.4rem 0;">
                                    <i data-lucide="check-circle-2" width="15" height="15"></i>
                                    <span data-i18n="statusCompleted">Completada</span>
                                </div>
                            <?php elseif ($estado === 'cancelada'): ?>
                                <div style="font-size:0.82rem;color:#c0392b;font-weight:600;padding:0.4rem 0;" data-i18n="statusCancelled">Cancelada</div>
                            <?php elseif ($estado === 'pendiente'): ?>
                                <a href="pago.php?id_reserva=<?php echo $row['ID_reserva']; ?>" class="btn-cancel" style="text-decoration:none;display:inline-flex;align-items:center;gap:0.4rem;background:var(--brand-yellow);color:var(--brand-dark);border-color:var(--brand-yellow);">
                                    <i data-lucide="credit-card" width="14" height="14"></i> <span data-i18n="payNowButton">Pagar ahora</span>
                                </a>
                                <form method="POST" action="eliminar_reserva.php"
                                      onsubmit="return confirmCancel();">
                                    <input type="hidden" name="id_reserva" value="<?php echo $row['ID_reserva']; ?>">
                                    <button type="submit" class="btn-cancel">
                                        <i data-lucide="trash-2"></i> <span data-i18n="cancelButton">Cancelar</span>
                                    </button>
                                </form>
                            <?php else: ?>
                                <form method="POST" action="eliminar_reserva.php"
                                      onsubmit="return confirmCancel();">
                                    <input type="hidden" name="id_reserva" value="<?php echo $row['ID_reserva']; ?>">
                                    <button type="submit" class="btn-cancel">
                                        <i data-lucide="trash-2"></i> <span data-i18n="cancelButton">Cancelar</span>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            <?php endwhile; ?>
        </div>

        <?php endif; ?>
    </div>
</div>

<script>
    lucide.createIcons();
    
    // Función para confirmar cancelación con traducción
    function confirmCancel() {
        const currentLang = getCurrentLanguage();
        const message = t('confirmCancelReservation', currentLang);
        return confirm(message);
    }
</script>

</body>
</html>