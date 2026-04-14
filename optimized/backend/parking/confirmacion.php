<?php
require_once '../conexion.php';

$id_reserva = isset($_GET['id_reserva']) ? (int)$_GET['id_reserva'] : 0;

if ($id_reserva <= 0) {
    die("ID de reserva no válido");
}

$sql = "SELECT * FROM reserva WHERE ID_reserva = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_reserva);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Reserva no encontrada");
}
?>

<h2>Pago completado</h2>
<p>Tu reserva ha sido confirmada correctamente.</p>

<a href="../app.html">Volver</a>