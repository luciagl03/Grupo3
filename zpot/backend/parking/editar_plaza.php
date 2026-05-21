<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../sesion/login.php');
    exit;
}

require_once '../sesion/conexion.php';

$id_plaza = isset($_GET['id_plaza']) ? (int) $_GET['id_plaza'] : 0;
$dni = $_SESSION['dni'] ?? '';

if ($id_plaza <= 0) {
    header('Location: mis_plazas.php');
    exit;
}

$stmt = $_conexion->prepare('SELECT * FROM PLAZA WHERE ID_plaza = ? AND DNI = ?');
$stmt->bind_param('is', $id_plaza, $dni);
$stmt->execute();
$plaza = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$plaza) {
    header('Location: mis_plazas.php');
    exit;
}

$extras = !empty($plaza['Extras']) ? explode(',', $plaza['Extras']) : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Editar plaza — Zpot</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="../styles/alta_plaza.css">
    <script src="../dark-mode.js"></script>
</head>
<body class="auth-page">
    <div class="layout">
        <main class="card">
            <div class="logo"><a href="../index.php"><img src="../../frontend/assets/images/logo.png" alt="Zpot"></a></div>
            <h1 class="headline">Editar plaza</h1>
            <p class="support">Modifica los datos de tu plaza. Los campos con (*) son obligatorios.</p>

            <div id="globalError" class="global-error" role="alert" hidden></div>

            <form id="plazaForm" novalidate>
                <input type="hidden" id="id_plaza" value="<?php echo $id_plaza; ?>">

                <div class="form-group">
                    <label for="direccion"><i data-lucide="map-pin"></i> Dirección <span aria-hidden="true">*</span></label>
                    <input type="text" id="direccion" name="direccion" autocomplete="street-address"
                           placeholder="Ej: Calle Larios 2, Málaga" required
                           value="<?php echo htmlspecialchars($plaza['Direccion']); ?>">
                    <span class="field-hint">Incluye siempre la ciudad para que aparezca en el mapa.</span>
                    <span id="direccionError" class="field-error" aria-live="polite"></span>
                </div>

                <div class="form-group">
                    <label for="foto"><i data-lucide="image"></i> Foto de la plaza</label>
                    <input type="file" id="foto" name="foto" accept="image/*" class="file-input">
                    <label for="foto" class="file-label">
                        <i data-lucide="upload"></i>
                        <span id="fileLabel">Seleccionar imagen</span>
                    </label>
                    <span id="fotoError" class="field-error" aria-live="polite"></span>
                    <div id="imagePreview" class="image-preview" <?php echo !empty($plaza['Foto']) ? '' : 'hidden'; ?>>
                        <img id="previewImg" src="<?php echo htmlspecialchars($plaza['Foto'] ?? ''); ?>" alt="Vista previa">
                        <button type="button" class="remove-image" id="removeImage" aria-label="Eliminar imagen">
                            <i data-lucide="x"></i>
                        </button>
                    </div>
                </div>

                <div class="row-two">
                    <div class="form-group">
                        <label for="ancho"><i data-lucide="maximize-2"></i>Ancho (m)</label>
                        <input type="number" id="ancho" name="ancho" min="0" step="0.01" placeholder="2.5" inputmode="decimal"
                               value="<?php echo $plaza['Ancho'] ?? ''; ?>">
                        <span id="anchoError" class="field-error" aria-live="polite"></span>
                    </div>
                    <div class="form-group">
                        <label for="largo"><i data-lucide="maximize-2"></i>Largo (m)</label>
                        <input type="number" id="largo" name="largo" min="0" step="0.01" placeholder="5" inputmode="decimal"
                               value="<?php echo $plaza['Largo'] ?? ''; ?>">
                        <span id="largoError" class="field-error" aria-live="polite"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label><i data-lucide="home"></i>Ubicación</label>
                    <div class="radio-group">
                        <?php foreach (['cubierto' => 'Cubierto', 'garaje' => 'Garaje', 'exterior' => 'Exterior / Al aire libre'] as $val => $label): ?>
                            <label class="radio-option">
                                <input type="radio" name="ubicacion" value="<?php echo $val; ?>"
                                    <?php echo ($plaza['Ubicacion'] === $val) ? 'checked' : ''; ?>>
                                <?php echo $label; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label><i data-lucide="star"></i>Extras</label>
                    <div class="radio-group">
                        <?php foreach (['ev' => 'Carga eléctrica', 'vigilado' => 'Vigilado', '24h' => 'Acceso 24h'] as $val => $label): ?>
                            <label class="radio-option">
                                <input type="checkbox" name="extras" value="<?php echo $val; ?>"
                                    <?php echo in_array($val, $extras) ? 'checked' : ''; ?>>
                                <?php echo $label; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="descripcion"><i data-lucide="align-left"></i>Descripción</label>
                    <textarea id="descripcion" name="descripcion" placeholder="Detalles del aparcamiento, acceso, seguridad..."><?php echo htmlspecialchars($plaza['Descripcion'] ?? ''); ?></textarea>
                    <span id="descripcionError" class="field-error" aria-live="polite"></span>
                </div>

                <div class="form-group">
                    <label for="precio"><i data-lucide="banknote"></i>Precio (€/h) <span aria-hidden="true">*</span></label>
                    <div class="input-with-symbol">
                        <input type="number" id="precio" name="precio" min="0.01" step="0.01" placeholder="4.50" inputmode="decimal" required
                               value="<?php echo $plaza['Precio'] ?? ''; ?>">
                    </div>
                    <span id="precioError" class="field-error" aria-live="polite"></span>
                </div>

                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i data-lucide="save"></i> Guardar cambios
                </button>
            </form>

            <div class="back-link-container">
                <a href="mis_plazas.php" class="back-link"><i data-lucide="arrow-left"></i> Volver a mis plazas</a>
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
            var keepExistingPhoto = <?php echo !empty($plaza['Foto']) ? 'true' : 'false'; ?>;
            var existingPhotoUrl = <?php echo !empty($plaza['Foto']) ? json_encode($plaza['Foto']) : 'null'; ?>;

            var fields = {
                direccion:   { el: document.getElementById('direccion'),   err: document.getElementById('direccionError') },
                foto:        { el: fotoInput,        err: document.getElementById('fotoError') },
                ancho:       { el: document.getElementById('ancho'),       err: document.getElementById('anchoError') },
                largo:       { el: document.getElementById('largo'),       err: document.getElementById('largoError') },
                descripcion: { el: document.getElementById('descripcion'), err: document.getElementById('descripcionError') },
                precio:      { el: document.getElementById('precio'),      err: document.getElementById('precioError') }
            };

            // Handle file selection and preview
            fotoInput.addEventListener('change', function(e) {
                var file = e.target.files[0];
                if (!file) return;

                // Validate file type
                if (!file.type.startsWith('image/')) {
                    setError('foto', 'El archivo debe ser una imagen');
                    fotoInput.value = '';
                    return;
                }

                // Validate file size (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    setError('foto', 'La imagen es demasiado grande (máx. 5MB)');
                    fotoInput.value = '';
                    return;
                }

                setError('foto', '');
                selectedFile = file;
                keepExistingPhoto = false;
                fileLabel.textContent = file.name;

                // Show preview
                var reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    imagePreview.hidden = false;
                    lucide.createIcons();
                };
                reader.readAsDataURL(file);
            });

            // Remove image
            removeImageBtn.addEventListener('click', function() {
                fotoInput.value = '';
                selectedFile = null;
                keepExistingPhoto = false;
                fileLabel.textContent = 'Seleccionar imagen';
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
                Object.keys(fields).forEach(function (k) { setError(k, ''); });
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
                var precioVal = fields.precio.el.value;

                if (!direccionVal) {
                    setError('direccion', 'La dirección es obligatoria');
                    valid = false;
                } else if (direccionVal.indexOf(',') === -1) {
                    setError('direccion', 'Incluye la ciudad separada por coma (ej: Calle Larios 2, Málaga)');
                    valid = false;
                } else {
                    setError('direccion', '');
                }

                if (precioVal === '' || precioVal === null) {
                    setError('precio', 'El precio es obligatorio');
                    valid = false;
                } else if (isNaN(parseFloat(precioVal)) || parseFloat(precioVal) <= 0) {
                    setError('precio', 'El precio debe ser mayor que 0');
                    valid = false;
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
                submitBtn.innerHTML = '<i data-lucide="loader"></i> Guardando…';
                lucide.createIcons();

                // Convert image to Base64 if selected
                function processForm(fotoBase64) {
                    var payload = {
                        id_plaza: parseInt(document.getElementById('id_plaza').value),
                        direccion: (fields.direccion.el.value || '').trim(),
                        foto: fotoBase64 !== undefined ? fotoBase64 : (keepExistingPhoto ? existingPhotoUrl : null),
                        ancho: fields.ancho.el.value === '' ? null : fields.ancho.el.value,
                        largo: fields.largo.el.value === '' ? null : fields.largo.el.value,
                        descripcion: (fields.descripcion.el.value || '').trim() || null,
                        precio: fields.precio.el.value,
                        ubicacion: getUbicacion(),
                        extras: getExtras()
                    };

                    fetch('editar_plaza_api.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        credentials: 'same-origin',
                        body: JSON.stringify(payload)
                    })
                    .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
                    .then(function (ref) {
                        if (ref.ok && ref.data.success) {
                            window.location.href = 'mis_plazas.php?updated=1';
                            return;
                        }
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i data-lucide="save"></i> Guardar cambios';
                        lucide.createIcons();
                        if (ref.data.errors) {
                            Object.keys(ref.data.errors).forEach(function (k) { setError(k, ref.data.errors[k]); });
                        }
                        if (ref.data.error) showGlobalError(ref.data.error);
                    })
                    .catch(function () {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i data-lucide="save"></i> Guardar cambios';
                        lucide.createIcons();
                        showGlobalError('Error de conexión. Inténtalo de nuevo.');
                    });
                }

                // If there's a new file, convert to Base64
                if (selectedFile) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        processForm(e.target.result);
                    };
                    reader.onerror = function() {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i data-lucide="save"></i> Guardar cambios';
                        lucide.createIcons();
                        showGlobalError('Error al procesar la imagen');
                    };
                    reader.readAsDataURL(selectedFile);
                } else {
                    processForm(undefined);
                }
            });
        })();
    </script>
</body>
</html>
