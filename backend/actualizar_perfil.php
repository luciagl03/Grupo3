<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include("sesion/conexion.php");

$dni = $_SESSION['dni'];

$nombre = $_POST['nombre'];
$apellidos = $_POST['apellidos'];
$email = $_POST['email'];
$telefono = $_POST['telefono'];
$direccion = $_POST['direccion'];

$sql = "UPDATE USUARIO 
        SET Nombre=?, Apellidos=?, Email=?, Telefono=?, Direccion=? 
        WHERE DNI=?";

$stmt = $_conexion->prepare($sql);
$stmt->bind_param("ssssss", $nombre, $apellidos, $email, $telefono, $direccion, $dni);

$stmt->execute();

header("Location: mi_perfil.php");