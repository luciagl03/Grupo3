<?php
/**
 * Add parking spot page.
 * Form to create a PLAZA; ownership (DNI) is set on the server from session.
 */
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../sesion/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    
    <title>Añadir mi plaza — Zpot</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&display=swap" rel="stylesheet">

    <!-- Iconos -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <link rel="stylesheet" href="../styles/alta_plaza.css">
</head>
<body class="auth-page">
    <div class="layout">
        <main class="card">
            <div class="logo"><a href="../index.php"><img src="../../frontend/assets/images/logo.png" alt="Zpot"></a></div>
            <h1 class="headline">Añadir mi plaza</h1>
            <p class="support">Publica tu plaza de aparcamiento o garaje. Los campos con (*) son obligatorios.</p>

            <div id="globalError" class="global-error" role="alert" hidden></div>

            <form id="plazaForm" novalidate>
                <div class="form-group">
                    <label for="direccion"><i data-lucide="map-pin"></i> Dirección <span aria-hidden="true">*</span></label>
                    <input type="text" id="direccion" name="direccion" autocomplete="street-address" placeholder="Calle, número, ciudad" required>
                    <span id="direccionError" class="field-error" aria-live="polite"></span>
                </div>

                <div class="form-group">
                    <label for="foto"><i data-lucide="image"></i>URL de la foto</label>
                    <input type="url" id="foto" name="foto" autocomplete="off" placeholder="https://...">
                    <span id="fotoError" class="field-error" aria-live="polite"></span>
                </div>

                <div class="row-two">
                    <div class="form-group">
                        <label for="ancho"><i data-lucide="maximize-2"></i>Ancho (m)</label>
                        <input type="number" id="ancho" name="ancho" min="0" step="0.01" placeholder="2.5" inputmode="decimal">
                        <span id="anchoError" class="field-error" aria-live="polite"></span>
                    </div>
                    <div class="form-group">
                        <label for="largo"><i data-lucide="maximize-2"></i>Largo (m)</label>
                        <input type="number" id="largo" name="largo" min="0" step="0.01" placeholder="5" inputmode="decimal">
                        <span id="largoError" class="field-error" aria-live="polite"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="descripcion"><i data-lucide="align-left"></i>Descripción</label>
                    <textarea id="descripcion" name="descripcion" placeholder="Detalles del aparcamiento, acceso, seguridad..."></textarea>
                    <span id="descripcionError" class="field-error" aria-live="polite"></span>
                </div>

                <div class="form-group">
                    <label for="precio"><i data-lucide="banknote"></i>Precio (€/h)</label>
                    <div class="input-with-symbol">
                        <input type="number" id="precio" name="precio" min="0" step="0.01" placeholder="4.50" inputmode="decimal">
                    </div>
                    <span id="precioError" class="field-error" aria-live="polite"></span>
                </div>

                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i data-lucide="plus-circle"></i> Publicar plaza
                </button>
            </form>

            <div class="back-link-container">
                <a href="../app.html" class="back-link"><i data-lucide="arrow-left"></i> Volver al mapa</a>
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();

        (function () {
            var form = document.getElementById('plazaForm');
            var submitBtn = document.getElementById('submitBtn');
            var globalError = document.getElementById('globalError');

            var fields = {
                direccion:  { el: document.getElementById('direccion'),  err: document.getElementById('direccionError') },
                foto:       { el: document.getElementById('foto'),       err: document.getElementById('fotoError') },
                ancho:      { el: document.getElementById('ancho'),     err: document.getElementById('anchoError') },
                largo:      { el: document.getElementById('largo'),      err: document.getElementById('largoError') },
                descripcion:{ el: document.getElementById('descripcion'), err: document.getElementById('descripcionError') },
                precio:     { el: document.getElementById('precio'),    err: document.getElementById('precioError') }
            };

            function setError(field, message) {
                var f = fields[field];
                if (!f) return;
                f.err.textContent = message || '';
                f.el.classList.toggle('error', !!message);
            }

            function clearErrors() {
                Object.keys(fields).forEach(function (k) {
                    setError(k, '');
                });
                globalError.hidden = true;
                globalError.textContent = '';
            }

            function showGlobalError(msg) {
                globalError.textContent = msg;
                globalError.hidden = false;
            }

            function validate() {
                var valid = true;
                var direccionVal = (fields.direccion.el.value || '').trim();
                var fotoVal = (fields.foto.el.value || '').trim();
                var anchoVal = fields.ancho.el.value;
                var largoVal = fields.largo.el.value;
                var precioVal = fields.precio.el.value;

                if (!direccionVal) {
                    setError('direccion', 'La dirección es obligatoria');
                    valid = false;
                } else {
                    setError('direccion', '');
                }

                if (fotoVal && !/^https?:\/\/.+/.test(fotoVal)) {
                    setError('foto', 'Introduce una URL válida');
                    valid = false;
                } else {
                    setError('foto', '');
                }

                if (anchoVal !== '') {
                    var anchoNum = parseFloat(anchoVal);
                    if (isNaN(anchoNum) || anchoNum < 0) {
                        setError('ancho', 'Debe ser un número ≥ 0');
                        valid = false;
                    } else {
                        setError('ancho', '');
                    }
                } else {
                    setError('ancho', '');
                }

                if (largoVal !== '') {
                    var largoNum = parseFloat(largoVal);
                    if (isNaN(largoNum) || largoNum < 0) {
                        setError('largo', 'Debe ser un número ≥ 0');
                        valid = false;
                    } else {
                        setError('largo', '');
                    }
                } else {
                    setError('largo', '');
                }

                if (precioVal !== '') {
                    var precioNum = parseFloat(precioVal);
                    if (isNaN(precioNum) || precioNum < 0) {
                        setError('precio', 'Debe ser un número ≥ 0');
                        valid = false;
                    } else {
                        setError('precio', '');
                    }
                } else {
                    setError('precio', '');
                }

                return valid;
            }

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                clearErrors();

                if (!validate()) return;

                submitBtn.disabled = true;
                submitBtn.textContent = 'Publicando…';

                var payload = {
                    direccion: (fields.direccion.el.value || '').trim(),
                    foto: (fields.foto.el.value || '').trim() || null,
                    ancho: fields.ancho.el.value === '' ? null : fields.ancho.el.value,
                    largo: fields.largo.el.value === '' ? null : fields.largo.el.value,
                    descripcion: (fields.descripcion.el.value || '').trim() || null,
                    precio: fields.precio.el.value === '' ? null : fields.precio.el.value
                };
                if (!payload.foto) delete payload.foto;
                if (payload.ancho === null) delete payload.ancho;
                if (payload.largo === null) delete payload.largo;
                if (payload.descripcion === null) delete payload.descripcion;
                if (payload.precio === null) delete payload.precio;

                fetch('alta_plaza_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload)
                })
                    .then(function (res) {
                        return res.json().then(function (data) {
                            return { ok: res.ok, status: res.status, data: data };
                        });
                    })
                    .then(function (ref) {
                        var ok = ref.ok, status = ref.status, data = ref.data;

                        if (ok && data.success) {
                            window.location.href = '../app.html?plaza_created=1';
                            return;
                        }

                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Publicar plaza';

                        if (data.errors && typeof data.errors === 'object') {
                            Object.keys(data.errors).forEach(function (key) {
                                if (fields[key]) setError(key, data.errors[key]);
                            });
                        }
                        if (data.error && (!data.errors || Object.keys(data.errors || {}).length === 0)) {
                            showGlobalError(data.error);
                        } else if (status >= 500) {
                            showGlobalError('Error al guardar. Inténtalo de nuevo.');
                        }
                    })
                    .catch(function () {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Publicar plaza';
                        showGlobalError('Error de conexión. Inténtalo de nuevo.');
                    });
            });
        })();
    </script>
</body>
</html>
