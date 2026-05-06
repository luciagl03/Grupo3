<?php
// ------------------------------------------------------------
// Pagina Ajustes:
// - Preferencias del usuario y gestion de cuenta
// - Privacidad, notificaciones, soporte
// ------------------------------------------------------------
session_start();
require_once __DIR__ . "/../sesion/conexion.php";

if (!isset($_SESSION['dni'])) {
    header("Location: ../sesion/login.php");
    exit;
}

$dni = $_SESSION['dni']; 

$sql = "SELECT Nombre, Apellidos FROM USUARIO WHERE DNI = ?";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Términos y Condiciones - TFG</title>
    
    <!-- Fuentes y Iconos -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- CSS -->
    <link rel="stylesheet" href="../app.css">
    <link rel="stylesheet" href="../styles/legal.css">
</head>
<body class="legal-page">
<div class="layout">
    <div class="layout-container">
        
        <header class="legal-header">
            <a href="javascript:history.back()" class="back-link">
                <i data-lucide="arrow-left"></i> Volver
            </a>
            <h1 class="headline">Términos y <em>Condiciones</em></h1>
            <p class="support">Marco legal y académico del proyecto.</p>
        </header>

        <main class="card-legal">

            <section class="legal-section">
                <div class="section-header">
                    <div class="feature-dot"></div>
                    <h2>Prototipo Académico</h2>
                </div>
                <div class="legal-content">
                    <p>Esta plataforma es un <i>Trabajo de Fin de Grado</i> y no tiene fines comerciales. Toda la información mostrada es para evaluación docente.</p>
                </div>
            </section>

            <section class="legal-section">
                <div class="section-header">
                    <div class="feature-dot"></div>
                    <h2>Tratamiento de Datos</h2>
                </div>
                <div class="legal-content">
                    <p>En cumplimiento con el entorno de pruebas:</p>
                    <ul>
                        <li>Los datos se almacenan en una base de datos local controlada.</li>
                        <li>Las contraseñas utilizan encriptación segura.</li>
                        <li>No se comparten datos con aplicaciones externas.</li>
                    </ul>
                </div>
            </section>

            <!-- Footer con info del TFG -->
            <footer class="legal-footer">
                <div class="university-tag">Convocatoria 2026 · TFG Desarrollo de Aplicaciones Web</div>
                <p style="margin-top: 1rem; font-size: 0.9rem; color: #8a8880;">
                    Desarrollado por Grupo 3
                </p>
            </footer>
        </main>

    </div>
</div>
<script>lucide.createIcons();</script>
</body>
</html>