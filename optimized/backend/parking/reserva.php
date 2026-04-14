<?php
/**
 * Booking / reservation page.
 * Integration point: connect to existing RESERVA table and flow when ready.
 * For now shows a placeholder and the selected plaza id.
 */
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../sesion/login.php');
    exit;
}

require_once '../conexion.php';

$id_plaza = isset($_GET['id_plaza']) ? (int) $_GET['id_plaza'] : 0;
$dni = $_SESSION['usuario']['dni'] ?? '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservar — Zpot</title>
    <link rel="stylesheet" href="../app.css">
</head>
<body>

<div class="reserva-wrap">
    <h1>Reservar plaza</h1>

    <p>Plaza seleccionada: #<?php echo $id_plaza; ?>.</p>

    <form method="POST" action="pago.php">

        <input type="hidden" name="id_plaza" value="<?php echo $id_plaza; ?>">
        <input type="hidden" name="dni" value="<?php echo htmlspecialchars($dni); ?>">

        <label>Hora entrada:</label>
        <input type="datetime-local" name="hora_entrada" required>

        <label>Hora salida:</label>
        <input type="datetime-local" name="hora_salida" required>

        <label>Precio total (€):</label>
        <input type="number" name="precio" required>

        <button type="submit">Reservar</button>
    </form>

    <a href="../app.html">← Volver al mapa</a>
</div>

</body>
</html>
