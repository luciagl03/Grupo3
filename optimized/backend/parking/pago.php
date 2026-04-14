<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../sesion/login.php');
    exit;
}

require_once '../sesion/conexion.php'; 

// DATOS
$id_plaza = (int) $_POST['id_plaza'];
$fecha_inicio = $_POST['fecha_inicio'];
$fecha_fin = $_POST['fecha_fin'];

$dni = $_SESSION['usuario'];

// VALIDAR FECHAS
if (strtotime($fecha_fin) <= strtotime($fecha_inicio)) {
    die("Error: la fecha fin debe ser mayor que la de inicio.");
}

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

// INSERTAR RESERVA
$sql = "INSERT INTO reserva 
        (DNI, ID_plaza, Precio, Duracion, Hora_entrada, Hora_salida, Fecha) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";

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
    <title>Pagar reserva</title>
</head>
<body>

<h2>Total a pagar: <?php echo $total; ?> €</h2>

<h3>Precio por hora: <?php echo $precio_hora; ?> €</h3>
<p>Duración: <?php echo $duracion; ?> horas</p>
<p>Fecha: <?php echo $fecha; ?></p>
<p>Hora entrada: <?php echo $hora_entrada; ?></p>
<p>Hora salida: <?php echo $hora_salida; ?></p>

<br>

<!-- PAYPAL -->
<script src="https://www.paypal.com/sdk/js?client-id=TU_CLIENT_ID&currency=EUR"></script>

<div id="paypal-button-container"></div>

<script>
paypal.Buttons({
    createOrder: function(data, actions) {
        return actions.order.create({
            purchase_units: [{
                amount: {
                    value: '<?php echo $total; ?>'
                }
            }]
        });
    },
    onApprove: function(data, actions) {
        return actions.order.capture().then(function(details) {
            window.location.href = "confirmacion.php?id_reserva=<?php echo $id_reserva; ?>";
        });
    }
}).render('#paypal-button-container');
</script>

</body>
</html>