<?php
// ------------------------------------------------------------
// Profile page:
// - loads current user data from session DNI
// - renders editable form
// ------------------------------------------------------------
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once __DIR__ . "/../sesion/conexion.php";

// Guard route if user session is missing.
if (!isset($_SESSION['dni'])) {
    header("Location: ../sesion/login.php");
    exit;
}

$dni = $_SESSION['dni']; 

// Query full user record to populate form fields.
$sql = "SELECT * FROM USUARIO WHERE DNI = ?";
$stmt = $_conexion->prepare($sql);
$stmt->bind_param("s", $dni);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Mi perfil — Zpot</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- Lucide Icons para el formulario -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <link rel="stylesheet" href="../app.css">
    <link rel="stylesheet" href="../styles/miperfil.css">
</head>

<body class="profile-page">
    <div class="layout">
        <div class="layout-container">
            <header class="profile-header">
                <a href="../index.php" class="back-link"><i data-lucide="arrow-left"></i> Volver al mapa</a>
                
                <div class="header-content">
                    <div class="profile-avatar">
                        <?php 
                            $inicial = substr($user['Nombre'] ?? 'U', 0, 1);
                            echo htmlspecialchars(strtoupper($inicial));
                        ?>
                    </div>
                    <div class="header-text">
                        <h1 class="headline">Mi perfil</h1>
                        <p class="support">Gestiona tu información personal en Zpot</p>
                    </div>
                </div>
            </header>

            <main class="card profile-card">
                <form action="actualizar_perfil_api.php" method="POST" class="profile-form">
                        
                    <div class="form-grid">
                        <div class="form-group">
                            <label><i data-lucide="user"></i> Nombre</label>
                            <input type="text" name="nombre" value="<?= htmlspecialchars($user['Nombre'] ?? '') ?>" placeholder="Tu nombre">
                        </div>

                        <div class="form-group">
                            <label><i data-lucide="user"></i> Apellidos</label>
                            <input type="text" name="apellidos" value="<?= htmlspecialchars($user['Apellidos'] ?? '') ?>" placeholder="Tus apellidos">
                        </div>

                        <div class="form-group full-width">
                            <label><i data-lucide="mail"></i> Correo electrónico</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($user['Email'] ?? '') ?>" placeholder="tu@email.com">
                        </div>

                        <div class="form-group">
                            <label><i data-lucide="phone"></i> Teléfono</label>
                            <input type="text" name="telefono" value="<?= htmlspecialchars($user['Telefono'] ?? '') ?>" placeholder="Número de contacto">
                        </div>

                        <div class="form-group">
                            <label><i data-lucide="map-pin"></i> Dirección</label>
                            <input type="text" name="direccion" value="<?= htmlspecialchars($user['Direccion'] ?? '') ?>" placeholder="Tu dirección">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-save">
                            <i data-lucide="save"></i> Guardar cambios
                        </button>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <!-- Inicializar iconos -->
    <script>
      lucide.createIcons();
    </script>
</body>
</html>