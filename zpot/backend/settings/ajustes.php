<?php
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
     <!-- AIUDA CON EL CSS SUSI ns si es mejor ponerlo en app.css o q -->
    <style>
   .modal {
        position: fixed;
        inset: 0;
        background: rgba(58,56,47,0.4);
        backdrop-filter: blur(6px);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 2000;
    }

    .modal-content {
        background: var(--bg);
        padding: 1.5rem;
        border-radius: var(--radius);
        width: 320px;
        box-shadow: var(--shadow-lg);
        border: 1px solid var(--border);
        text-align: center;
    }

    .modal-content h3 {
        margin: 0 0 0.5rem;
        font-size: 1.1rem;
        color: var(--brand-dark);
    }

    .modal-content p {
        font-size: 0.85rem;
        color: var(--text-muted);
        margin-bottom: 1rem;
    }

    .modal-content input {
        width: 100%;
        padding: 0.6rem;
        border-radius: 8px;
        border: 1px solid var(--border);
        margin-bottom: 1rem;
        font-family: inherit;
    }

    .modal-content input:focus {
        outline: none;
        border-color: var(--brand-dark);
        box-shadow: 0 0 0 3px var(--accent-focus);
    }

    .modal-actions {
        display: flex;
        gap: 0.5rem;
    }

    .modal-actions button {
        flex: 1;
        padding: 0.6rem;
        border-radius: 8px;
        font-weight: 600;
        font-family: inherit;
        cursor: pointer;
        border: none;
    }

    #cancelDelete {
        background: var(--brand-bg);
        color: var(--text);
    }

    #cancelDelete:hover {
        background: var(--border);
    }

    #confirmDelete {
        background: #c0392b;
        color: white;
    }

    #confirmDelete:hover {
        background: #a93226;
    }

    .modal {
        display: none; 
    }

    .modal.open {
        display: flex;
    }

    .ajuste-content form {
        display: flex;
        flex-direction: column;
        gap: 0.6rem;
        margin-top: 0.8rem;
    }

    .ajuste-content input {
        width: 100%;
        padding: 0.55rem;
        border-radius: 8px;
        border: 1px solid var(--border);
        font-family: inherit;
        font-size: 0.85rem;
        background: #fff;
    }

    .ajuste-content input:focus {
        outline: none;
        border-color: var(--brand-dark);
        box-shadow: 0 0 0 3px var(--accent-focus);
    }

    .ajuste-content button {
        margin-top: 0.5rem;
        background: var(--brand-dark);
        color: var(--brand-yellow);
        border: none;
        border-radius: 8px;
        padding: 0.6rem;
        font-weight: 700;
        cursor: pointer;
        font-size: 0.85rem;
    }

    .ajuste-content button:hover {
        background: #1a1915;
    }

    #passMsg {
        font-size: 0.75rem;
        min-height: 1rem;
    }
    .ajuste-content {
        max-height: 0;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .ajuste-content.open {
        max-height: 300px;
    }

    .ajuste-content {
        max-height: 0;
        overflow: hidden;
        transition: all 0.3s ease;
        padding: 0 1rem;
    }

    .ajuste-content.open {
        max-height: 300px;
        padding: 1rem; 
    }

    #deleteBox {
        display: flex;
        justify-content: center;
    }

    #deleteBox button {
        width: 100%;
        max-width: 220px;
    }
    </style>
</head>

<body class="settings-page">
    <div class="layout">
        <div class="layout-container">
            <header class="settings-header">
                <a href="../index.php" class="back-link"><i data-lucide="arrow-left"></i> <span data-i18n="backToMap">Volver al mapa</span></a>
                
                <div class="header-content">
                    <div class="settings-icon">
                        <i data-lucide="settings"></i>
                    </div>
                    <div class="header-text">
                        <h1 class="headline" data-i18n="settings">Ajustes</h1>
                        <p class="support" data-i18n="settingsSubtitle">Configura tu experiencia en Zpot</p>
                    </div>
                </div>
            </header>

            <main class="settings-content">
                
                <!-- Privacidad y Seguridad -->
                <section class="settings-section">
                    <div class="section-header">
                        <i data-lucide="shield"></i>
                        <h2 class="section-title" data-i18n="privacySecurity">Privacidad y Seguridad</h2>
                    </div>

                    <div class="settings-card">

                        <!-- CAMBIAR CONTRASEÑA -->
                        <div class="setting-item setting-item-action" onclick="toggleAjuste('passwordBox', this)">
                            <div class="setting-info">
                                <i data-lucide="key" class="setting-icon"></i>
                                <div class="setting-text">
                                    <h3 class="setting-label" data-i18n="changePassword">Cambiar contraseña</h3>
                                    <p class="setting-description" data-i18n="changePasswordDesc">Actualiza tu contraseña de acceso</p>
                                </div>
                            </div>
                            <button type="button" class="btn-action">
                                <i data-lucide="chevron-right" class="arrow-icon"></i>
                            </button>
                        </div>

                        <!-- DESPLEGABLE PASSWORD -->
                        <div id="passwordBox" class="ajuste-content">
                            <form id="formPassword">
                                <input type="password" id="currentPass" data-i18n="currentPassword" placeholder="Contraseña actual" required>
                                <input type="password" id="newPass" data-i18n="newPassword" placeholder="Nueva contraseña" required>
                                <input type="password" id="confirmPass" data-i18n="confirmPassword" placeholder="Confirmar contraseña" required>
                                <button type="submit" data-i18n="updatePassword">Actualizar contraseña</button>
                                <p id="passMsg"></p>
                            </form>
                        </div>

                        <div class="setting-divider"></div>

                        <!-- ELIMINAR CUENTA -->
                        <div class="setting-item setting-item-action setting-item-danger" onclick="toggleAjuste('deleteBox', this)">
                            <div class="setting-info">
                                <i data-lucide="trash-2" class="setting-icon"></i>
                                <div class="setting-text">
                                    <h3 class="setting-label" data-i18n="deleteAccount">Borrar cuenta</h3>
                                    <p class="setting-description" data-i18n="deleteAccountDesc">Eliminar permanentemente tu cuenta</p>
                                </div>
                            </div>
                            <button type="button" class="btn-action btn-action-danger">
                                <i data-lucide="chevron-right" class="arrow-icon"></i>
                            </button>
                        </div>

                        <!-- DESPLEGABLE DELETE -->
                        <div id="deleteBox" class="ajuste-content">
                            <button id="openDeleteModal" class="btn-action btn-action-danger" data-i18n="deleteMyAccount">
                                Eliminar mi cuenta
                            </button>
                        </div>

                    </div>
                </section>

                <!-- Notificaciones -->
                <section class="settings-section">
                    <div class="section-header">
                        <i data-lucide="bell"></i>
                        <h2 class="section-title" data-i18n="notifications">Notificaciones</h2>
                    </div>
                    <div class="settings-card">
                        <div class="setting-item">
                            <div class="setting-info">
                                <i data-lucide="smartphone" class="setting-icon"></i>
                                <div class="setting-text">
                                    <h3 class="setting-label" data-i18n="pushNotifications">Notificaciones push</h3>
                                    <p class="setting-description" data-i18n="pushNotificationsDesc">Recibe alertas sobre tus reservas</p>
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
                        <h2 class="section-title" data-i18n="displayLanguage">Pantalla e Idiomas</h2>
                    </div>
                    <div class="settings-card">
                        <div class="setting-item">
                            <div class="setting-info">
                                <i data-lucide="moon" class="setting-icon"></i>
                                <div class="setting-text">
                                    <h3 class="setting-label" data-i18n="darkMode">Modo oscuro</h3>
                                    <p class="setting-description" data-i18n="darkModeDesc">Tema oscuro para la interfaz</p>
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
                                    <h3 class="setting-label" data-i18n="language">Idioma</h3>
                                    <p class="setting-description" data-i18n="languageDesc">Selecciona tu idioma preferido</p>
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
                        <h2 class="section-title" data-i18n="supportlegal">Soporte y Legal</h2>
                    </div>
                    <div class="settings-card">
                        <a href="terminos.php" class="setting-item setting-item-link">
                            <div class="setting-info">
                                <i data-lucide="file-text" class="setting-icon"></i>
                                <div class="setting-text">
                                    <h3 class="setting-label" data-i18n="termsconditions">Términos y condiciones</h3>
                                </div>
                            </div>
                            <i data-lucide="external-link" class="link-icon"></i>
                        </a>
                        
                        <div class="setting-divider"></div>
                        
                        <a href="privacidad.php" class="setting-item setting-item-link">
                            <div class="setting-info">
                                <i data-lucide="shield-check" class="setting-icon"></i>
                                <div class="setting-text">
                                    <h3 class="setting-label" data-i18n="privacypolicy">Política de privacidad</h3>
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
                        <h2 class="section-title" data-i18n="helpcenter">Centro de Ayuda</h2>
                    </div>
                    <div class="settings-card">
                        <!-- FAQ Accordion -->
                        <div class="faq-section">
                            <div class="faq-header">
                                <i data-lucide="message-circle" class="setting-icon"></i>
                                <h3 class="setting-label" data-i18n="faqTitle">Preguntas frecuentes (FAQ)</h3>
                            </div>
                            
                            <!-- FAQ Item 1 -->
                            <div class="faq-item">
                                <button class="faq-question" onclick="toggleFAQ(this)">
                                    <span data-i18n="faqQ1">¿Cómo reservo una plaza?</span>
                                    <i data-lucide="chevron-down" class="faq-arrow"></i>
                                </button>
                                <div class="faq-answer">
                                    <p data-i18n="faqA1">Selecciona el punto en el mapa, elige tus horas y confirma.</p>
                                </div>
                            </div>
                            
                            <!-- FAQ Item 2 -->
                            <div class="faq-item">
                                <button class="faq-question" onclick="toggleFAQ(this)">
                                    <span data-i18n="faqQ2">¿Puedo cancelar una reserva?</span>
                                    <i data-lucide="chevron-down" class="faq-arrow"></i>
                                </button>
                                <div class="faq-answer">
                                    <p data-i18n="faqA2">Sí, desde el apartado 'Mis Reservas' hasta 1 hora antes.</p>
                                </div>
                            </div>
                            
                            <!-- FAQ Item 3 -->
                            <div class="faq-item">
                                <button class="faq-question" onclick="toggleFAQ(this)">
                                    <span data-i18n="faqQ3">¿Cómo publico mi garaje?</span>
                                    <i data-lucide="chevron-down" class="faq-arrow"></i>
                                </button>
                                <div class="faq-answer">
                                    <p data-i18n="faqA3">Ve a 'Mis Plazas' y rellena el formulario con fotos y dirección.</p>
                                </div>
                            </div>
                            
                            <!-- FAQ Item 4 -->
                            <div class="faq-item">
                                <button class="faq-question" onclick="toggleFAQ(this)">
                                    <span data-i18n="faqQ4">¿Es seguro el pago?</span>
                                    <i data-lucide="chevron-down" class="faq-arrow"></i>
                                </button>
                                <div class="faq-answer">
                                    <p data-i18n="faqA4">Sí, usamos pasarelas de pago cifradas para tu seguridad.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="setting-divider"></div>
                        
                        <!-- CONTACTAR SOPORTE -->
                        <div class="setting-item setting-item-action" onclick="toggleAjuste('supportBox', this)">
                            <div class="setting-info">
                                <i data-lucide="mail" class="setting-icon"></i>
                                <div class="setting-text">
                                    <h3 class="setting-label" data-i18n="supportTitle">Contactar soporte</h3>
                                    <p class="setting-description" data-i18n="supportDescription">Envíanos tus consultas o reporta problemas</p>
                                </div>
                            </div>
                            <button type="button" class="btn-action">
                                <i data-lucide="chevron-right" class="arrow-icon"></i>
                            </button>
                        </div>

                        <!-- DESPLEGABLE SUPPORT -->
                        <div id="supportBox" class="ajuste-content support-accordion-content">
                            <!-- Contact Form -->
                            <form id="supportForm" class="support-form">
                                <div class="form-group">
                                    <input 
                                        type="text" 
                                        id="supportSubject" 
                                        class="form-input" 
                                        data-i18n="supportSubject"
                                        placeholder="Asunto" 
                                        required
                                    >
                                </div>
                                <div class="form-group">
                                    <textarea 
                                        id="supportMessage" 
                                        class="form-textarea" 
                                        data-i18n="supportMessage"
                                        placeholder="Mensaje" 
                                        rows="4" 
                                        required
                                    ></textarea>
                                </div>
                                <button type="submit" class="btn-submit" id="supportSubmitBtn">
                                    <span data-i18n="supportSend">Enviar</span>
                                </button>
                                <p id="supportMsg" class="support-message"></p>
                            </form>
                            
                            <!-- Quick Actions -->
                            <div class="quick-actions">
                                <h4 class="quick-actions-title" data-i18n="supportQuickActions">Acciones rápidas</h4>
                                <div class="quick-actions-buttons">
                                    <a href="mailto:soporte@zpot.com" class="btn-quick-action">
                                        <i data-lucide="mail"></i>
                                        <span data-i18n="supportContactEmail">Contactar por Email</span>
                                    </a>
                                    <a href="mailto:soporte@zpot.com?subject=Reporte de Error" class="btn-quick-action">
                                        <i data-lucide="bug"></i>
                                        <span data-i18n="supportReportBug">Reportar un error</span>
                                    </a>
                                </div>
                            </div>
                        </div>
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

    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h3 data-i18n="deleteAccountQuestion">¿Eliminar cuenta?</h3>
            <p data-i18n="deleteAccountWarning">Esta acción es permanente.</p>

            <input type="password" id="deletePass" data-i18n="enterPassword" placeholder="Introduce tu contraseña">

            <div class="modal-actions">
                <button id="confirmDelete" class="danger" data-i18n="deletePermanently">Eliminar definitivamente</button>
                <button id="cancelDelete" data-i18n="cancel">Cancelar</button>
            </div>

            <p id="deleteMsg"></p>
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

        // ── Notificaciones push ──────────────────────────────
        var pushToggle = document.getElementById('pushNotifications');
        var pushDesc   = pushToggle ? pushToggle.closest('.setting-item').querySelector('.setting-description') : null;

        function actualizarDescripcion(activo, permiso) {
            if (!pushDesc) return;
            if (permiso === 'denied') {
                pushDesc.textContent = 'Bloqueadas en el navegador. Actívalas en Ajustes del navegador.';
                pushDesc.style.color = '#c0392b';
            } else if (activo) {
                pushDesc.textContent = 'Recibirás alertas sobre tus reservas.';
                pushDesc.style.color = '#065f46';
            } else {
                pushDesc.textContent = 'Activa para recibir alertas sobre tus reservas.';
                pushDesc.style.color = '';
            }
        }

        if (pushToggle) {
            var permiso = ('Notification' in window) ? Notification.permission : 'unsupported';
            var guardado = localStorage.getItem('zpot_push_enabled');

            if (permiso === 'denied') {
                pushToggle.checked = false;
                pushToggle.disabled = true;
                actualizarDescripcion(false, 'denied');
            } else if (permiso === 'granted' && guardado !== 'false') {
                pushToggle.checked = true;
                actualizarDescripcion(true, 'granted');
            } else {
                pushToggle.checked = false;
                actualizarDescripcion(false, permiso);
            }

            pushToggle.addEventListener('change', function() {
                var activar = this.checked;
                if (activar) {
                    if (!('Notification' in window)) {
                        this.checked = false;
                        if (pushDesc) { pushDesc.textContent = 'Tu navegador no soporta notificaciones.'; }
                        return;
                    }
                    Notification.requestPermission().then(function(resultado) {
                        if (resultado === 'granted') {
                            localStorage.setItem('zpot_push_enabled', 'true');
                            actualizarDescripcion(true, 'granted');
                            new Notification('¡Notificaciones activadas!', {
                                body: 'Recibirás alertas sobre tus reservas en Zpot.',
                                icon: '../frontend/assets/images/Icono.png'
                            });
                        } else if (resultado === 'denied') {
                            pushToggle.checked = false;
                            pushToggle.disabled = true;
                            localStorage.setItem('zpot_push_enabled', 'false');
                            actualizarDescripcion(false, 'denied');
                        } else {
                            pushToggle.checked = false;
                            actualizarDescripcion(false, 'default');
                        }
                    });
                } else {
                    localStorage.setItem('zpot_push_enabled', 'false');
                    actualizarDescripcion(false, ('Notification' in window) ? Notification.permission : 'unsupported');
                }
            });
        }

        document.getElementById('darkMode')?.addEventListener('change', function() {
            console.log('Dark mode:', this.checked);
        });

        document.getElementById('languageSelect')?.addEventListener('change', function() {
            changeLanguage(this.value);
        });

        formPassword.onsubmit = async (e) => {
            e.preventDefault();

            const current = currentPass.value;
            const pass = newPass.value;
            const confirm = confirmPass.value;

            if (pass.length < 8) return passMsg.textContent = "Mínimo 8 caracteres";
            if (!/\d/.test(pass)) return passMsg.textContent = "Debe tener un número";
            if (pass !== confirm) return passMsg.textContent = "No coinciden";

            try {
                const res = await fetch('cambiar_password.php', {
                    method: 'POST',
                    body: JSON.stringify({ current, pass })
                });

                const text = await res.text();  
                const data = JSON.parse(text);   

                if (data.success) {
                    passMsg.textContent = "Contraseña actualizada";

                    currentPass.value = "";
                    newPass.value = "";
                    confirmPass.value = "";
                } else {
                    passMsg.textContent = data.error;
                }

            } catch (err) {
                console.error(err);
                passMsg.textContent = "Error del servidor";
            }

            if (data.success) {
                passMsg.textContent = "Contraseña actualizada";
                
                // limpiar campos
                currentPass.value = "";
                newPass.value = "";
                confirmPass.value = "";
            } else {
                passMsg.textContent = data.error;
            }
        };

        const deleteModal = document.getElementById('deleteModal');
        const openDeleteModal = document.getElementById('openDeleteModal');
        const cancelDelete = document.getElementById('cancelDelete');
        const confirmDelete = document.getElementById('confirmDelete');
        const deleteMsg = document.getElementById('deleteMsg');

        openDeleteModal.onclick = () => {
            deleteModal.classList.add('open');
        };

        cancelDelete.onclick = () => {
            deleteModal.classList.remove('open');
            deleteMsg.textContent = "";
        };

        deleteModal.onclick = (e) => {
            if (e.target === deleteModal) {
                deleteModal.classList.remove('open');
            }
        };

        confirmDelete.onclick = async () => {
            const password = deletePass.value;

            const res = await fetch('eliminar_cuenta.php', {
                method: 'POST',
                body: JSON.stringify({ password })
            });

            const data = await res.json();

            if (data.success) {
                window.location.href = '../sesion/logout.php';
            } else {
                deleteMsg.textContent = data.error;
            }
        };

        function toggleAjuste(id, el) {
            const box = document.getElementById(id);
            const isOpen = box.classList.contains('open');

            document.querySelectorAll('.ajuste-content').forEach(e => e.classList.remove('open'));
            document.querySelectorAll('.setting-item').forEach(e => e.classList.remove('open'));

            if (!isOpen) {
                box.classList.add('open');
                el.classList.add('open');
            }
        }

        // FAQ Accordion functionality
        function toggleFAQ(button) {
            const faqItem = button.parentElement;
            const answer = faqItem.querySelector('.faq-answer');
            const isOpen = answer.classList.contains('open');

            // Close all other FAQ items
            document.querySelectorAll('.faq-answer').forEach(ans => {
                ans.classList.remove('open');
            });
            document.querySelectorAll('.faq-question').forEach(q => {
                q.classList.remove('active');
            });

            // Toggle current item
            if (!isOpen) {
                answer.classList.add('open');
                button.classList.add('active');
            }

            // Re-initialize Lucide icons for the newly opened content
            lucide.createIcons();
        }

        // ── Support Form Functionality ──────────────────────────────
        const supportForm = document.getElementById('supportForm');
        const supportMsg = document.getElementById('supportMsg');
        const supportSubmitBtn = document.getElementById('supportSubmitBtn');

        if (supportForm) {
            supportForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const subject = document.getElementById('supportSubject').value;
                const message = document.getElementById('supportMessage').value;

                // Get current language for translated messages
                const currentLang = getCurrentLanguage();

                // Show sending message
                supportMsg.textContent = t('supportSending', currentLang);
                supportMsg.className = 'support-message sending';
                supportSubmitBtn.disabled = true;

                // Simulate sending (2 seconds delay)
                setTimeout(() => {
                    // Show success message
                    supportMsg.textContent = t('supportSuccess', currentLang);
                    supportMsg.className = 'support-message success';
                    
                    // Clear form
                    supportForm.reset();
                    
                    // Re-enable button
                    supportSubmitBtn.disabled = false;
                    
                    // Clear success message after 5 seconds
                    setTimeout(() => {
                        supportMsg.textContent = '';
                        supportMsg.className = 'support-message';
                    }, 5000);
                }, 2000);
            });
        }

        // Re-initialize Lucide icons after page load
        setTimeout(() => {
            lucide.createIcons();
        }, 100);

    </script>
    
</body>
</html>