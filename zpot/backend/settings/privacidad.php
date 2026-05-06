<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Política de Privacidad - TFG</title>
    
    <!-- Fuentes y Iconos -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>

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
            <h1 class="headline">Política de <em>Privacidad</em></h1>
            <p class="support">Cómo gestionamos y protegemos tus datos en este proyecto.</p>
        </header>

        <main class="card-legal">
            
            <section class="legal-section">
                <div class="section-header">
                    <div class="feature-dot"></div>
                    <h2>Responsable del Tratamiento</h2>
                </div>
                <div class="legal-content">
                    <p>Los responsables del tratamiento de los datos recolectados en este prototipo son las integrantes del <strong>Grupo 3</strong>, alumnas del centro <strong> Davante Medac Nova</strong>.</p>
                    <p>Este proyecto ha sido desarrollado de forma colaborativa por:</p>
                    <ul style="list-style: none; padding-left: 0; margin-top: 0.5rem;">
                        <li><i data-lucide="user" style="width:14px; vertical-align:middle; margin-right:8px;"></i>Maria González-Carrascosa </li>
                        <li><i data-lucide="user" style="width:14px; vertical-align:middle; margin-right:8px;"></i>Lucia Gallar </li>
                        <li><i data-lucide="user" style="width:14px; vertical-align:middle; margin-right:8px;"></i>Susana Villena </li>
                        <!-- Añade o quita según seáis -->
                    </ul>
                    <p style="margin-top: 1rem;">El tratamiento de la información se realiza con fines estrictamente académicos y de evaluación dentro del marco del Trabajo de Fin de Grado.</p>
                </div>
            </section>

            <section class="legal-section">
                <div class="section-header">
                    <div class="feature-dot"></div>
                    <h2>Datos Recopilados</h2>
                </div>
                <div class="legal-content">
                    <p>Para el correcto funcionamiento de la plataforma de parking, almacenamos:</p>
                    <ul>
                        <li><strong>Identificación:</strong> Nombre, Apellidos y DNI.</li>
                        <li><strong>Contacto:</strong> Correo electrónico.</li>
                        <li><strong>Multimedia:</strong> Fotografías de las plazas de aparcamiento subidas por el usuario.</li>
                        <li><strong>Ubicación:</strong> Direcciones de las plazas para su geolocalización en el mapa.</li>
                    </ul>
                </div>
            </section>

            <section class="legal-section">
                <div class="section-header">
                    <div class="feature-dot"></div>
                    <h2>Seguridad Técnica</h2>
                </div>
                <div class="legal-content">
                    <p>La seguridad es una prioridad en este TFG. Se han implementado las siguientes medidas:</p>
                    <ul>
                        <li><strong>Hash de Contraseñas:</strong> Las claves nunca se guardan en texto plano, utilizando algoritmos de cifrado seguros (bcrypt/password_hash).</li>
                        <li><strong>Sentencias Preparadas:</strong> Protección contra ataques de Inyección SQL en todas las consultas a la base de datos.</li>
                        <li><strong>Sesiones Seguras:</strong> Uso de `session_start()` con identificadores únicos para prevenir el secuestro de sesiones.</li>
                    </ul>
                </div>
            </section>

            <section class="legal-section">
                <div class="section-header">
                    <div class="feature-dot"></div>
                    <h2>Tus Derechos</h2>
                </div>
                <div class="legal-content">
                    <p>Aunque se trata de un entorno de pruebas, el usuario puede ejercer sus derechos de Acceso, Rectificación, Cancelación y Oposición simplemente eliminando su perfil desde el panel de ajustes.</p>
                </div>
            </section>

            <footer class="legal-footer">
                <div class="university-tag">Protección de Datos · Entorno Académico</div>
                <p style="margin-top: 1rem; font-size: 0.9rem; color: #8a8880;">
                    Este documento cumple con una función didáctica basada en el RGPD.
                </p>
            </footer>
        </main>

    </div>
</div>

<script>
    lucide.createIcons();
</script>

</body>
</html>