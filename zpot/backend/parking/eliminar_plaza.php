<?php
session_start();
require_once '../sesion/conexion.php';

if (!isset($_SESSION['dni'])) {
    header("Location: ../sesion/login.php");
    exit;
}

$id_plaza = (int) $_POST['id_plaza'];
$dni = $_SESSION['dni'];

$sql = "DELETE FROM PLAZA WHERE ID_plaza = ? AND DNI = ?";

$stmt = $_conexion->prepare($sql);

if (!$stmt) {
    die("Error SQL: " . $_conexion->error);
}

$stmt->bind_param("is", $id_plaza, $dni);
$stmt->execute();

header("Location: mis_plazas.php");
exit;