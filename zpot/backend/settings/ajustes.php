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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Ajustes — Zpot</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <link rel="stylesheet" href="../app.css">
    <link rel="stylesheet" href="../styles/ajustes.css">
    <script src="../translations.js"></script>
</head>

<body class="settings-page">
    <div class="layout">
        <div class="layout-container">
            <header class="settings-header">
                <a href="../index.php" class="back-link"><i data-lucide="arrow-left"></i> Volver al mapa</a>
                
                <div class="header-content">
                    <div class="settings-icon">
                        <i data-lucide="settings"></i>
                    </div>
                    <div class="header-text">
                        <h1 class="headline">Ajustes</h1>
                        <p class="support">Configura tu experiencia en Zpot</p>
                    </div>
                </div>
            </header>

            <main class="settings-content">
                
                <!-- Privacidad y Seguridad -->
                <section class="settings-section">
                    <div class="section-header">
                        <i data-lucide="shield"></i>
                        <h2 class="section-title">Privacidad y Seguridad</h2>
                    </div>
                    <div class="settings-card">
                        <div class="setting-item setting-item-action">
                            <div class="setting-info">
                                <i data-lucide="key" class="setting-icon"></i>
                                <div class="setting-text">
                                    <h3 class="setting-label">Cambiar contraseña</h3>
                                    <p class="setting-description">Actualiza tu contraseña de acceso</p>
                                </div>
                            </div>
                            <button type="button" class="btn-action" id="changePasswordBtn">
                                <i data-lucide="chevron-right"></i>
                            </button>
                        </div>
                        
                        <div class="setting-divider"></div>
                        
                        <div class="setting-item setting-item-action setting-item-danger">
                            <div class="setting-info">
                                <i data-lucide="trash-2" class="setting-icon"></i>
                                <div class="setting-text">
                                    <h3 class="setting-label">Borrar cuenta</h3>
                                    <p class="setting-description">Eliminar permanentemente tu cuenta</p>
                                </div>
                            </div>
                            <button type="button" class="btn-action btn-action-danger" id="deleteAccountBtn">
                                <i data-lucide="chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </section>

                <!-- Notificaciones -->
                <section class="settings-section">
                    <div class="section-header">
                        <i data-lucide="bell"></i>
                        <h2 class="section-title">Notificaciones</h2>
                    </div>
                    <div class="settings-card">
                        <div class="setting-item">
                            <div class="setting-info">
                                <i data-lucide="smartphone" class="setting-icon"></i>
                                <div class="setting-text">
                                    <h3 class="setting-label">Notificaciones push</h3>
                                    <p class="setting-description">Recibe alertas sobre tus reservas</p>
                                </div>
                            </div>
                            <label class="switch">
                                <input type="checkbox" id="pushNotifications">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                </section>

                <!-- Pantalla e Idiomas -->
                <section class="settings-section">
                    <div class="section-header">
                        <i data-lucide="monitor"></i>
                        <h2 class="section-title">Pantalla e Idiomas</h2>
                    </div>
                    <div class="settings-card">
                        <div class="setting-item">
                            <div class="setting-info">
                                <i data-lucide="moon" class="setting-icon"></i>
                                <div class="setting-text">
                                    <h3 class="setting-label">Modo oscuro</h3>
                                    <p class="setting-description">Tema oscuro para la interfaz</p>
                                </div>
                            </div>
                            <label class="switch">
                                <input type="checkbox" id="darkMode">
                                <span class="slider"></span>
                            </label>
                        </div>
                        
                        <div class="setting-divider"></div>
                        
                        <div class="setting-item">
                            <div class="setting-info">
                                <i data-lucide="globe" class="setting-icon"></i>
                                <div class="setting-text">
                                    <h3 class="setting-label">Idioma</h3>
                                    <p class="setting-description">Selecciona tu idioma preferido</p>
                                </div>
                            </div>
                            <select class="language-select" id="languageSelect">
                                <option value="es" selected>Español</option>
                                <option value="en">English</option>
                                <option value="fr">Français</option>
                                <option value="de">Deutsch</option>
                            </select>
                        </div>
                    </div>
                </section>

                <!-- Soporte y Legal -->
                <section class="settings-section">
                    <div class="section-header">
                        <i data-lucide="life-buoy"></i>
                        <h2 class="section-title">Soporte y Legal</h2>
                    </div>
                    <div class="settings-card">
                        <a href="#" class="setting-item setting-item-link">
                            <div class="setting-info">
                                <i data-lucide="file-text" class="setting-icon"></i>
                                <div class="setting-text">
                                    <h3 class="setting-label">Términos y condiciones</h3>
                                </div>
                            </div>
                            <i data-lucide="external-link" class="link-icon"></i>
                        </a>
                        
                        <div class="setting-divider"></div>
                        
                        <a href="#" class="setting-item setting-item-link">
                            <div class="setting-info">
                                <i data-lucide="shield-check" class="setting-icon"></i>
                                <div class="setting-text">
                                    <h3 class="setting-label">Política de privacidad</h3>
                                </div>
                            </div>
                            <i data-lucide="external-link" class="link-icon"></i>
                        </a>
                    </div>
                </section>

                <!-- Centro de Ayuda -->
                <section class="settings-section">
                    <div class="section-header">
                        <i data-lucide="help-circle"></i>
                        <h2 class="section-title">Centro de Ayuda</h2>
                    </div>
                    <div class="settings-card">
                        <a href="#" class="setting-item setting-item-link">
                            <div class="setting-info">
                                <i data-lucide="message-circle" class="setting-icon"></i>
                                <div class="setting-text">
                                    <h3 class="setting-label">Preguntas frecuentes (FAQ)</h3>
                                </div>
                            </div>
                            <i data-lucide="external-link" class="link-icon"></i>
                        </a>
                        
                        <div class="setting-divider"></div>
                        
                        <a href="#" class="setting-item setting-item-link">
                            <div class="setting-info">
                                <i data-lucide="mail" class="setting-icon"></i>
                                <div class="setting-text">
                                    <h3 class="setting-label">Contactar soporte</h3>
                                </div>
                            </div>
                            <i data-lucide="external-link" class="link-icon"></i>
                        </a>
                    </div>
                </section>

                <!-- App Info -->
                <div class="app-info">
                    <p class="app-version">Zpot v1.0.0</p>
                    <p class="app-copyright">© 2026 Grupo 3 - TFG</p>
                </div>

            </main>
        </div>
    </div>

    <script>
        //Lucide icons
        lucide.createIcons();

        // Placeholder event listeners (functionality to be implemented later)
        document.getElementById('changePasswordBtn')?.addEventListener('click', function() {
            console.log('Cambiar contraseña - Funcionalidad pendiente');
        });

        document.getElementById('deleteAccountBtn')?.addEventListener('click', function() {
            console.log('Borrar cuenta - Funcionalidad pendiente');
        });

        document.getElementById('pushNotifications')?.addEventListener('change', function() {
            console.log('Push notifications:', this.checked);
        });

        document.getElementById('darkMode')?.addEventListener('change', function() {
            console.log('Dark mode:', this.checked);
        });

        document.getElementById('languageSelect')?.addEventListener('change', function() {
            changeLanguage(this.value);
        });
    </script>
</body>
</html>
