<?php
session_start();
require_once '../sesion/conexion.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: ../sesion/login.php');
    exit;
}

$id_reserva = isset($_GET['id_reserva']) ? (int)$_GET['id_reserva'] : 0;
$order_id   = isset($_GET['order_id']) ? trim($_GET['order_id']) : '';
$dni        = $_SESSION['dni'] ?? '';

if ($id_reserva <= 0) {
    header('Location: ../index.php');
    exit;
}

// Verify reservation belongs to the logged-in user
$stmt = $_conexion->prepare(
    "SELECT r.*, p.Direccion FROM RESERVA r
     LEFT JOIN PLAZA p ON r.ID_plaza = p.ID_plaza
     WHERE r.ID_reserva = ? AND r.DNI = ?"
);
$stmt->bind_param("is", $id_reserva, $dni);
$stmt->execute();
$reserva = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$reserva) {
    header('Location: ../index.php');
    exit;
}

// Mark as confirmed when PayPal order_id is present (payment completed)
if ($order_id !== '' && $reserva['Estado'] === 'pendiente') {
    $upd = $_conexion->prepare("UPDATE RESERVA SET Estado = 'confirmada' WHERE ID_reserva = ? AND DNI = ?");
    $upd->bind_param("is", $id_reserva, $dni);
    $upd->execute();
    $upd->close();
    $reserva['Estado'] = 'confirmada';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva confirmada — Zpot</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../app.css">
    <style>
        .confirm-wrap { display:flex; align-items:center; justify-content:center; min-height:100vh; padding:2rem; }
        .confirm-card { max-width:420px; width:100%; padding:2rem; background:#fff; border-radius:16px; box-shadow:0 4px 24px rgba(0,0,0,0.08); text-align:center; }
        .confirm-icon { font-size:3rem; margin-bottom:1rem; }
        .confirm-card h2 { margin:0 0 0.5rem; color:#3a382f; }
        .confirm-card p { color:#666; margin:0.25rem 0; }
        .confirm-card .price { font-size:1.4rem; font-weight:800; color:#3a382f; margin-top:0.75rem; }
        .confirm-card .btn { display:inline-block; margin-top:1.5rem; padding:0.75rem 1.5rem; background:#f4dd49; color:#3a382f; border-radius:8px; font-weight:600; text-decoration:none; }
        .estado-badge { display:inline-block; margin-top:1rem; padding:4px 12px; border-radius:20px; font-size:0.8rem; font-weight:700; }
        .estado-confirmada { background:#d1fae5; color:#065f46; }
        .estado-pendiente  { background:#fef3c7; color:#92400e; }
    </style>
</head>
<body style="background:#f1f0ea; font-family:'DM Sans',sans-serif;">
    <div class="confirm-wrap">
        <div class="confirm-card">
            <?php if ($reserva['Estado'] === 'confirmada'): ?>
                <div class="confirm-icon">&#10003;</div>
                <h2>Reserva confirmada</h2>
            <?php else: ?>
                <div class="confirm-icon">&#8987;</div>
                <h2>Reserva pendiente</h2>
            <?php endif; ?>

            <p><strong><?= htmlspecialchars($reserva['Direccion'] ?? 'Plaza #' . $reserva['ID_plaza']) ?></strong></p>
            <p><?= date('d/m/Y', strtotime($reserva['Fecha'])) ?> &middot; <?= substr($reserva['Hora_entrada'], 0, 5) ?> &ndash; <?= substr($reserva['Hora_salida'], 0, 5) ?></p>
            <p class="price"><?= number_format($reserva['Precio'], 2) ?> &euro;</p>

            <span class="estado-badge estado-<?= htmlspecialchars($reserva['Estado']) ?>">
                <?= $reserva['Estado'] === 'confirmada' ? 'Pagado' : 'Pago pendiente' ?>
            </span>

            <br>
            <a href="../index.php" class="btn">Volver al mapa</a>
        </div>
    </div>
</body>
</html>
