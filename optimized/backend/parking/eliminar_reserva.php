<?php
session_start();
require_once '../sesion/conexion.php';

if (!isset($_SESSION['dni'])) {
    header("Location: ../sesion/login.php");
    exit;
}

$id_reserva = (int) $_POST['id_reserva'];
$dni = $_SESSION['dni'];

$sql = "DELETE FROM RESERVA WHERE ID_reserva = ? AND DNI = ?";

$stmt = $_conexion->prepare($sql);

if (!$stmt) {
    die("Error SQL: " . $_conexion->error);
}

$stmt->bind_param("is", $id_reserva, $dni);
$stmt->execute();

header("Location: ../parking/mis_reservas.php");
exit;