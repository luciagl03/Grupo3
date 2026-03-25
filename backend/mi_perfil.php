<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include("sesion/conexion.php"); 

$dni = $_SESSION['dni']; 

$sql = "SELECT * FROM USUARIO WHERE DNI = ?";
$stmt = $_conexion->prepare($sql);
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
    <link rel="stylesheet" href="app.css">
    <link rel="stylesheet" href="../frontend/miperfil.css">
</head>
<body class="profile-page">

    <div class="layout-container">
        <header class="profile-header">
            <a href="index.php" class="back-link">← Volver al mapa</a>
            <h1>Mi perfil</h1>
            <p>Gestiona tu información personal de Zpot</p>
        </header>

        <div class="profile-layout">
            <main class="profile-card">
                <form action="actualizar_perfil.php" method="POST" class="profile-form">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Nombre</label>
                            <input type="text" name="nombre" value="<?= htmlspecialchars($user['Nombre'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label>Apellidos</label>
                            <input type="text" name="apellidos" value="<?= htmlspecialchars($user['Apellidos'] ?? '') ?>">
                        </div>

                        <div class="form-group full-width">
                            <label>Correo electrónico</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($user['Email'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label>Teléfono</label>
                            <input type="text" name="telefono" value="<?= htmlspecialchars($user['Telefono'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label>Dirección</label>
                            <input type="text" name="direccion" value="<?= htmlspecialchars($user['Direccion'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-save">Guardar cambios</button>
                    </div>
                </form>
            </main>
        </div>
    </div>

</body>
</html>