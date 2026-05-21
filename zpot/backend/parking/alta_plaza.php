<?php

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
    
    <title data-i18n="addMySpotTitle">Añadir mi plaza</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&display=swap" rel="stylesheet">

    <!-- Iconos -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <link rel="stylesheet" href="../styles/alta_plaza.css">
    <script src="../translations.js"></script>
</head>
<body class="auth-page">
    <div class="layout">
        <main class="card">
            <div class="logo"><a href="../index.php"><img src="../../frontend/assets/images/logo.png" alt="Zpot"></a></div>
            <h1 class="headline" data-i18n="addMySpotTitle">Añadir mi plaza</h1>
            <p class="support" data-i18n="addMySpotSubtitle">Publica tu plaza de aparcamiento o garaje. Los campos con (*) son obligatorios.</p>

            <div id="globalError" class="global-error" role="alert" hidden></div>

            <form id="plazaForm" novalidate>
                <div class="form-group">
                    <label for="direccion"><i data-lucide="map-pin"></i> <span data-i18n="address">Dirección</span> <span aria-hidden="true">*</span></label>
                    <input type="text" id="direccion" name="direccion" autocomplete="street-address" data-i18n="addressPlaceholder" placeholder="Ej: Calle Larios 2, Málaga" required>
                    <span class="field-hint" data-i18n="addressHint">Incluye siempre la ciudad para que aparezca en el mapa.</span>
                    <span id="direccionError" class="field-error" aria-live="polite"></span>
                </div>

                <div class="form-group">
                    <label for="foto"><i data-lucide="image"></i> <span data-i18n="spotPhoto">Foto de la plaza</span></label>
                    <input type="file" id="foto" name="foto" accept="image/*" class="file-input">
                    <label for="foto" class="file-label">
                        <i data-lucide="upload"></i>
                        <span id="fileLabel" data-i18n="selectImage">Seleccionar imagen</span>
                    </label>
                    <span id="fotoError" class="field-error" aria-live="polite"></span>
                    <div id="imagePreview" class="image-preview" hidden>
                        <img id="previewImg" src="" alt="Vista previa">
                        <button type="button" class="remove-image" id="removeImage" data-i18n="removeImage" aria-label="Eliminar imagen">
                            <i data-lucide="x"></i>
                        </button>
                    </div>
                </div>

                <div class="row-two">
                    <div class="form-group">
                        <label for="ancho"><i data-lucide="maximize-2"></i><span data-i18n="width">Ancho (m)</span></label>
                        <input type="number" id="ancho" name="ancho" min="0" step="0.01" data-i18n="widthPlaceholder" placeholder="2.5" inputmode="decimal">
                        <span id="anchoError" class="field-error" aria-live="polite"></span>
                    </div>
                    <div class="form-group">
                        <label for="largo"><i data-lucide="maximize-2"></i><span data-i18n="length">Largo (m)</span></label>
                        <input type="number" id="largo" name="largo" min="0" step="0.01" data-i18n="lengthPlaceholder" placeholder="5" inputmode="decimal">
                        <span id="largoError" class="field-error" aria-live="polite"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label><i data-lucide="home"></i><span data-i18n="location">Ubicación</span></label>
                    <div class="radio-group">
                        <label class="radio-option"><input type="radio" name="ubicacion" value="cubierto"> <span data-i18n="covered">Cubierto</span></label>
                        <label class="radio-option"><input type="radio" name="ubicacion" value="garaje"> <span data-i18n="garage">Garaje</span></label>
                        <label class="radio-option"><input type="radio" name="ubicacion" value="exterior"> <span data-i18n="outdoor">Exterior / Al aire libre</span></label>
                    </div>
                </div>

                <div class="form-group">
                    <label><i data-lucide="star"></i><span data-i18n="extras">Extras</span></label>
                    <div class="radio-group">
                        <label class="radio-option"><input type="checkbox" name="extras" value="ev"> <span data-i18n="electricCharging">Carga eléctrica</span></label>
                        <label class="radio-option"><input type="checkbox" name="extras" value="vigilado"> <span data-i18n="guarded">Vigilado</span></label>
                        <label class="radio-option"><input type="checkbox" name="extras" value="24h"> <span data-i18n="access24h">Acceso 24h</span></label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="descripcion"><i data-lucide="align-left"></i><span data-i18n="description">Descripción</span></label>
                    <textarea id="descripcion" name="descripcion" data-i18n="descriptionPlaceholder" placeholder="Detalles del aparcamiento, acceso, seguridad..."></textarea>
                    <span id="descripcionError" class="field-error" aria-live="polite"></span>
                </div>

                <div class="form-group">
                    <label for="precio"><i data-lucide="banknote"></i><span data-i18n="pricePerHour">Precio (€/h)</span> <span aria-hidden="true">*</span></label>
                    <div class="input-with-symbol">
                        <input type="number" id="precio" name="precio" min="0.01" step="0.01" data-i18n="pricePlaceholder" placeholder="4.50" inputmode="decimal" required>
                    </div>
                    <span id="precioError" class="field-error" aria-live="polite"></span>
                </div>

                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i data-lucide="plus-circle"></i> <span data-i18n="publishSpot">Publicar plaza</span>
                </button>
            </form>

            <div class="back-link-container">
                <a href="../app.html" class="back-link"><i data-lucide="arrow-left"></i> <span data-i18n="backToMap">Volver al mapa</span></a>
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();

        (function () {
            var form = document.getElementById('plazaForm');
            var submitBtn = document.getElementById('submitBtn');
            var globalError = document.getElementById('globalError');
            var fotoInput = document.getElementById('foto');
            var fileLabel = document.getElementById('fileLabel');
            var imagePreview = document.getElementById('imagePreview');
            var previewImg = document.getElementById('previewImg');
            var removeImageBtn = document.getElementById('removeImage');
            var selectedFile = null;

            var fields = {
                direccion:  { el: document.getElementById('direccion'),  err: document.getElementById('direccionError') },
                foto:       { el: fotoInput,       err: document.getElementById('fotoError') },
                ancho:      { el: document.getElementById('ancho'),     err: document.getElementById('anchoError') },
                largo:      { el: document.getElementById('largo'),      err: document.getElementById('largoError') },
                descripcion:{ el: document.getElementById('descripcion'), err: document.getElementById('descripcionError') },
                precio:     { el: document.getElementById('precio'),    err: document.getElementById('precioError') }
            };

            fotoInput.addEventListener('change', function(e) {
                var file = e.target.files[0];
                if (!file) return;

                if (!file.type.startsWith('image/')) {
                    setError('foto', t('invalidImageFile'));
                    fotoInput.value = '';
                    return;
                }

                if (file.size > 5 * 1024 * 1024) {
                    setError('foto', t('imageTooLarge'));
                    fotoInput.value = '';
                    return;
                }

                setError('foto', '');
                selectedFile = file;
                fileLabel.textContent = file.name;

                var reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    imagePreview.hidden = false;
                    lucide.createIcons();
                };
                reader.readAsDataURL(file);
            });

            removeImageBtn.addEventListener('click', function() {
                fotoInput.value = '';
                selectedFile = null;
                fileLabel.textContent = t('selectImage');
                imagePreview.hidden = true;
                previewImg.src = '';
                setError('foto', '');
                lucide.createIcons();
            });

            function getUbicacion() {
                var checked = document.querySelector('input[name="ubicacion"]:checked');
                return checked ? checked.value : null;
            }
            function getExtras() {
                return Array.from(document.querySelectorAll('input[name="extras"]:checked')).map(function (cb) { return cb.value; });
            }

            function setError(field, message) {
                var f = fields[field];
                if (!f || !f.err) return;
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
                var anchoVal = fields.ancho.el.value;
                var largoVal = fields.largo.el.value;
                var precioVal = fields.precio.el.value;

                if (!direccionVal) {
                    setError('direccion', t('addressRequired'));
                    valid = false;
                } else if (direccionVal.indexOf(',') === -1) {
                    setError('direccion', t('addressCityRequired'));
                    valid = false;
                } else {
                    setError('direccion', '');
                }

                if (anchoVal !== '') {
                    var anchoNum = parseFloat(anchoVal);
                    if (isNaN(anchoNum) || anchoNum < 0) {
                        setError('ancho', t('invalidNumber'));
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
                        setError('largo', t('invalidNumber'));
                        valid = false;
                    } else {
                        setError('largo', '');
                    }
                } else {
                    setError('largo', '');
                }

                if (precioVal === '' || precioVal === null) {
                    setError('precio', t('priceRequired'));
                    valid = false;
                } else {
                    var precioNum = parseFloat(precioVal);
                    if (isNaN(precioNum) || precioNum <= 0) {
                        setError('precio', t('pricePositive'));
                        valid = false;
                    } else {
                        setError('precio', '');
                    }
                }

                return valid;
            }

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                clearErrors();

                if (!validate()) return;

                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i data-lucide="loader"></i> ' + t('publishing');
                lucide.createIcons();

                function processForm(fotoBase64) {
                    var payload = {
                        direccion: (fields.direccion.el.value || '').trim(),
                        foto: fotoBase64 || null,
                        ancho: fields.ancho.el.value === '' ? null : fields.ancho.el.value,
                        largo: fields.largo.el.value === '' ? null : fields.largo.el.value,
                        descripcion: (fields.descripcion.el.value || '').trim() || null,
                        precio: fields.precio.el.value === '' ? null : fields.precio.el.value,
                        ubicacion: getUbicacion(),
                        extras: getExtras()
                    };
                    if (!payload.foto) delete payload.foto;
                    if (payload.ancho === null) delete payload.ancho;
                    if (payload.largo === null) delete payload.largo;
                    if (payload.descripcion === null) delete payload.descripcion;
                    if (payload.precio === null) delete payload.precio;
                    if (!payload.ubicacion) delete payload.ubicacion;
                    if (!payload.extras || payload.extras.length === 0) delete payload.extras;

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
                                var dest = data.geocoded ? '../app.html?plaza_created=1' : '../app.html?plaza_created=1&sin_ubicacion=1';
                                window.location.href = dest;
                                return;
                            }

                            submitBtn.disabled = false;
                            submitBtn.innerHTML = '<i data-lucide="plus-circle"></i> ' + t('publishSpot');
                            lucide.createIcons();

                            if (data.errors && typeof data.errors === 'object') {
                                Object.keys(data.errors).forEach(function (key) {
                                    if (fields[key]) setError(key, data.errors[key]);
                                });
                            }
                            if (data.error && (!data.errors || Object.keys(data.errors || {}).length === 0)) {
                                showGlobalError(data.error);
                            } else if (status >= 500) {
                                showGlobalError(t('saveError'));
                            }
                        })
                        .catch(function () {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = '<i data-lucide="plus-circle"></i> ' + t('publishSpot');
                            lucide.createIcons();
                            showGlobalError(t('connectionError'));
                        });
                }

                if (selectedFile) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        processForm(e.target.result);
                    };
                    reader.onerror = function() {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i data-lucide="plus-circle"></i> ' + t('publishSpot');
                        lucide.createIcons();
                        showGlobalError(t('imageProcessError'));
                    };
                    reader.readAsDataURL(selectedFile);
                } else {
                    processForm(null);
                }
            });
        })();
    </script>
</body>
</html>
