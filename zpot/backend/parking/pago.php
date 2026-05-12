<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../sesion/login.php');
    exit;
}

require_once '../sesion/conexion.php'; 

// DATOS
$id_plaza = (int) $_POST['id_plaza'];
$fecha_inicio = $_POST['hora_entrada'];
$fecha_fin = $_POST['hora_salida'];

$dni = $_SESSION['dni'] ?? '';

// VALIDAR FECHAS
if (empty($fecha_inicio) || empty($fecha_fin)) {
    die("Error: debes indicar fecha de entrada y salida.");
}
if (strtotime($fecha_inicio) < time()) {
    die("Error: la fecha de entrada no puede ser en el pasado.");
}
if (strtotime($fecha_fin) <= strtotime($fecha_inicio)) {
    die("Error: la fecha de salida debe ser posterior a la de entrada.");
}

$fecha_inicio = str_replace('T', ' ', $_POST['hora_entrada']);
$fecha_fin = str_replace('T', ' ', $_POST['hora_salida']);

$inicio = new DateTime($fecha_inicio);
$fin = new DateTime($fecha_fin);

$fecha = $inicio->format('Y-m-d');
$hora_entrada = $inicio->format('H:i:s');
$hora_salida = $fin->format('H:i:s');

// DURACIÓN
$intervalo = $inicio->diff($fin);
$horas = ($intervalo->days * 24) + $intervalo->h;

if ($horas <= 0) {
    $horas = 1;
}

$duracion = $horas;

// PRECIO
$sql = "SELECT Precio FROM plaza WHERE ID_plaza = ?";
$stmt = $_conexion->prepare($sql);
$stmt->bind_param("i", $id_plaza);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Error: plaza no encontrada.");
}

$plaza = $result->fetch_assoc();
$precio_hora = $plaza['Precio'];

$total = $horas * $precio_hora;

// COMPROBAR DISPONIBILIDAD
$sql = "SELECT * FROM reserva 
        WHERE ID_plaza = ? 
        AND Fecha = ?
        AND (Hora_entrada < ? AND Hora_salida > ?)";

$stmt = $_conexion->prepare($sql);
$stmt->bind_param("isss", $id_plaza, $fecha, $hora_salida, $hora_entrada);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    die("Esta plaza ya está reservada en ese horario.");
}

// INSERTAR RESERVA (estado pendiente hasta confirmar pago)
$sql = "INSERT INTO reserva
        (DNI, ID_plaza, Precio, Duracion, Hora_entrada, Hora_salida, Fecha, Estado)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'pendiente')";

$stmt = $_conexion->prepare($sql);

$stmt->bind_param(
    "sidisss",
    $dni,
    $id_plaza,
    $total,
    $duracion,
    $hora_entrada,
    $hora_salida,
    $fecha
);

$stmt->execute();

$id_reserva = $_conexion->insert_id;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Pagar reserva — Zpot</title>

     <!-- Fuentes e Iconos -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <link rel="stylesheet" href="../app.css">
    <link rel="stylesheet" href="../styles/pago.css">
    
</head>
<body class="payment-page">

<div class="layout">
    <div class="payment-card">
        <img src="../../frontend/assets/images/logo.png" alt="Zpot" class="logo-img">
        <h2>Resumen del pago</h2>
        
        <div class="price-tag">
            <?php echo number_format($total, 2); ?><span>€</span>
        </div>

        <div class="details-box">
            <div class="detail-item"><span>Duración:</span> <span><?php echo $duracion; ?> horas</span></div>
            <div class="detail-item"><span>Fecha:</span> <span><?php echo $fecha; ?></span></div>
            <div class="detail-item"><span>Total:</span> <span><?php echo number_format($total, 2); ?> €</span></div>
        </div>

        <!-- PASAMOS DATOS AL JS AQUÍ -->
        <div id="paypal-button-container"
             data-total="<?php echo number_format($total, 2, '.', ''); ?>"
             data-id-reserva="<?php echo $id_reserva; ?>">
        </div>
        
        <a href="../index.php" class="back-link"><i data-lucide="arrow-left"></i> Cancelar y volver</a>
    </div>
</div>

<!-- SDK DE PAYPAL-->
<script src="https://www.paypal.com/sdk/js?client-id=AY_hkQ1T9mIhXUfY2Eu7TXdORPVLmI-SF6UaaorGnCYcgYsZ6Zt40_KL-fPTzCzE812wHUxd3JCzYWEP&currency=EUR&intent=capture"></script>

<!-- TU JS SEPARADO -->
<script src="../../scripts/pago.js"></script>
</body>
</html>