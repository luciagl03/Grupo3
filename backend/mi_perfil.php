<?php
session_start();
include("conexion.php"); 

$dni = $_SESSION['dni']; 

$sql = "SELECT * FROM USUARIO WHERE DNI = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $dni);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi perfil</title>
</head>
<body>

<h1>Mi perfil</h1>

<form action="actualizar_perfil.php" method="POST">
    <label>Nombre:</label>
    <input type="text" name="nombre" value="<?= $user['Nombre'] ?>"><br>

    <label>Apellidos:</label>
    <input type="text" name="apellidos" value="<?= $user['Apellidos'] ?>"><br>

    <label>Email:</label>
    <input type="email" name="email" value="<?= $user['Email'] ?>"><br>

    <label>Teléfono:</label>
    <input type="text" name="telefono" value="<?= $user['Telefono'] ?>"><br>

    <label>Dirección:</label>
    <input type="text" name="direccion" value="<?= $user['Direccion'] ?>"><br>

    <button type="submit">Guardar cambios</button>
</form>

</body>
</html>