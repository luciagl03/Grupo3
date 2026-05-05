<?php
// ------------------------------------------------------------
// Profile update endpoint (form POST from mi_perfil.php)
// ------------------------------------------------------------
session_start();
require_once __DIR__ . "/../sesion/conexion.php";

// If session is missing, send user to login.
if (!isset($_SESSION['dni'])) {
    header("Location: ../sesion/login.php");
    exit;
}

$dni = $_SESSION['dni'];

// Keep the original behavior: direct POST values with trimming.
$nombre = trim($_POST['nombre'] ?? '');
$apellidos = trim($_POST['apellidos'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');

if ($nombre === '' || $apellidos === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ./mi_perfil.php?error=1");
    exit;
}

$sql = "UPDATE USUARIO
        SET Nombre=?, Apellidos=?, Email=?, Telefono=?, Direccion=?
        WHERE DNI=?";

$stmt = $_conexion->prepare($sql);
$stmt->bind_param("ssssss", $nombre, $apellidos, $email, $telefono, $direccion, $dni);
$stmt->execute();
$stmt->close();

header("Location: ./mi_perfil.php?updated=1");
exit;