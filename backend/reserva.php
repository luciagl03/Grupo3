<?php
/**
 * Booking / reservation page.
 * Integration point: connect to existing RESERVA table and flow when ready.
 * For now shows a placeholder and the selected plaza id.
 */
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: sesion/login.php');
    exit;
}
$id_plaza = isset($_GET['id_plaza']) ? (int) $_GET['id_plaza'] : 0;
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservar — Zpot</title>
    <link rel="stylesheet" href="app.css">
    <style>
        .reserva-wrap { max-width: 480px; margin: 4rem auto; padding: 0 1rem; }
        .reserva-wrap h1 { font-size: 1.5rem; margin-bottom: 0.5rem; }
        .reserva-wrap p { color: var(--text-muted); margin-bottom: 1.5rem; }
        .reserva-wrap a { color: var(--accent); }
    </style>
</head>
<body>
    <div class="reserva-wrap">
        <h1>Reservar plaza</h1>
        <p>Plaza seleccionada: #<?php echo $id_plaza; ?>.</p>
        <p>Aquí se conectará el flujo de reserva (tabla RESERVA, fechas, precio, etc.).</p>
        <a href="app.html">← Volver al mapa</a>
    </div>
</body>
</html>
