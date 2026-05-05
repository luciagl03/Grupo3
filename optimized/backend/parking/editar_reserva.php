<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../sesion/login.php');
    exit;
}

require_once '../sesion/conexion.php';

$id_reserva = isset($_GET['id_reserva']) ? (int) $_GET['id_reserva'] : 0;
$dni = $_SESSION['dni'] ?? '';

if ($id_reserva <= 0) {
    header('Location: mis_reservas.php');
    exit;
}

// Verify ownership and get reservation data
$stmt = $_conexion->prepare(
    "SELECT r.*, p.Direccion AS PlazaDireccion, p.Precio AS PrecioHora
     FROM RESERVA r
     LEFT JOIN PLAZA p ON r.ID_plaza = p.ID_plaza
     WHERE r.ID_reserva = ? AND r.DNI = ?"
);
$stmt->bind_param("is", $id_reserva, $dni);
$stmt->execute();
$reserva = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$reserva) {
    header('Location: mis_reservas.php');
    exit;
}

// Build datetime strings for the picker
$entradaDT = $reserva['Fecha'] . 'T' . substr($reserva['Hora_entrada'], 0, 5);
$salidaDT  = $reserva['Fecha'] . 'T' . substr($reserva['Hora_salida'],  0, 5);
$ahora     = date('Y-m-d\TH:i');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Editar reserva — Zpot</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="../app.css">
    <link rel="stylesheet" href="../styles/reservar.css">
    <link rel="stylesheet" href="../styles/zpot-datetime-picker.css">
</head>
<body class="booking-page">

<div class="layout">
    <div class="layout-container">

        <header class="booking-header">
            <a href="mis_reservas.php" class="back-link"><i data-lucide="arrow-left"></i> Volver a mis reservas</a>
            <h1 class="headline">Editar reserva</h1>
            <p class="support">Modifica las fechas y horas de tu estancia.</p>
        </header>

        <div class="booking-summary">
            <div class="summary-icon"><i data-lucide="map-pin"></i></div>
            <div class="summary-text">
                <span>Plaza seleccionada</span>
                <strong><?php echo htmlspecialchars($reserva['PlazaDireccion'] ?? 'Plaza #' . $reserva['ID_plaza']); ?></strong>
            </div>
        </div>

        <main class="card booking-card">

            <div id="globalError" style="display:none;background:#d32f2f;color:#fff;padding:0.8rem 1rem;border-radius:12px;margin-bottom:1.5rem;font-size:0.9rem;"></div>

            <form id="editForm">
                <input type="hidden" id="id_reserva" value="<?php echo $id_reserva; ?>">
                <input type="hidden" id="id_plaza"   value="<?php echo $reserva['ID_plaza']; ?>">
                <input type="hidden" id="precio_hora" value="<?php echo (float) $reserva['PrecioHora']; ?>">

                <div class="form-grid">
                    <div class="form-group">
                        <label>
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/><path d="M16 2v4M8 2v4M3 10h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            Hora de entrada
                        </label>
                        <input type="hidden" id="hora_entrada" name="hora_entrada" value="<?php echo $entradaDT; ?>">
                        <span id="entradaError" class="field-error" style="display:none;color:#d32f2f;font-size:0.8rem;font-weight:600;margin-top:4px;"></span>
                    </div>

                    <div class="form-group">
                        <label>
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/><path d="M16 2v4M8 2v4M3 10h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            Hora de salida
                        </label>
                        <input type="hidden" id="hora_salida" name="hora_salida" value="<?php echo $salidaDT; ?>">
                        <span id="salidaError" class="field-error" style="display:none;color:#d32f2f;font-size:0.8rem;font-weight:600;margin-top:4px;"></span>
                    </div>

                    <div class="form-group full-width">
                        <label>
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm.5 15H11v-6h1.5v6zm0-8H11V7h1.5v2z" fill="currentColor" opacity=".3"/><rect x="2" y="2" width="20" height="20" rx="10" stroke="currentColor" stroke-width="2" fill="none"/><path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            Precio estimado
                        </label>
                        <input type="text" id="precioEstimado" disabled
                               style="width:100%;padding:0.9rem 1rem;font-family:inherit;font-size:1rem;border:1.5px solid var(--brand-grey);border-radius:var(--radius);background:#f5f5f3;color:var(--brand-dark);">
                        <span class="helper-text">Se actualiza al seleccionar las horas.</span>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary" id="submitBtn">
                        Guardar cambios <i data-lucide="save"></i>
                    </button>
                </div>
            </form>
        </main>
    </div>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script src="../scripts/zpot-datetime-picker.js"></script>
<script>
lucide.createIcons();

(function () {
    var inputEntrada  = document.getElementById('hora_entrada');
    var inputSalida   = document.getElementById('hora_salida');
    var precioHora    = parseFloat(document.getElementById('precio_hora').value) || 0;
    var estimadoEl    = document.getElementById('precioEstimado');
    var globalError   = document.getElementById('globalError');
    var submitBtn     = document.getElementById('submitBtn');
    var ahora         = new Date();

    // Init pickers
    var pickerEntrada = new ZpotDatePicker(inputEntrada, {
        min: ahora.toISOString().slice(0,16),
        onChange: function () { actualizarPrecio(); sincronizarMinSalida(); }
    });
    var pickerSalida = new ZpotDatePicker(inputSalida, {
        min: ahora.toISOString().slice(0,16),
        onChange: function () { actualizarPrecio(); }
    });

    function sincronizarMinSalida() {
        if (!inputEntrada.value) return;
        var minSalida = new Date(inputEntrada.value);
        minSalida.setHours(minSalida.getHours() + 1);
        pickerSalida.minDate = minSalida;
    }

    function actualizarPrecio() {
        if (!inputEntrada.value || !inputSalida.value) return;
        var entrada = new Date(inputEntrada.value);
        var salida  = new Date(inputSalida.value);
        if (salida <= entrada) { estimadoEl.value = '—'; return; }
        var horas = Math.max(1, Math.ceil((salida - entrada) / 3600000));
        estimadoEl.value = (horas * precioHora).toFixed(2) + ' € (' + horas + 'h × ' + precioHora.toFixed(2) + ' €/h)';
    }

    // Init display
    actualizarPrecio();

    document.getElementById('editForm').addEventListener('submit', function (e) {
        e.preventDefault();
        globalError.style.display = 'none';

        var entrada = inputEntrada.value;
        var salida  = inputSalida.value;

        if (!entrada || !salida) {
            globalError.textContent = 'Debes seleccionar fecha de entrada y salida.';
            globalError.style.display = 'block';
            return;
        }
        if (new Date(entrada) < new Date()) {
            globalError.textContent = 'La fecha de entrada no puede ser en el pasado.';
            globalError.style.display = 'block';
            return;
        }
        if (new Date(salida) <= new Date(entrada)) {
            globalError.textContent = 'La fecha de salida debe ser posterior a la de entrada.';
            globalError.style.display = 'block';
            return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = 'Guardando…';

        fetch('editar_reserva_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({
                id_reserva:   parseInt(document.getElementById('id_reserva').value),
                id_plaza:     parseInt(document.getElementById('id_plaza').value),
                hora_entrada: entrada,
                hora_salida:  salida
            })
        })
        .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
        .then(function (ref) {
            if (ref.ok && ref.data.success) {
                window.location.href = 'mis_reservas.php?updated=1';
                return;
            }
            submitBtn.disabled = false;
            submitBtn.textContent = 'Guardar cambios';
            globalError.textContent = ref.data.error || 'Error al guardar. Inténtalo de nuevo.';
            globalError.style.display = 'block';
        })
        .catch(function () {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Guardar cambios';
            globalError.textContent = 'Error de conexión. Inténtalo de nuevo.';
            globalError.style.display = 'block';
        });
    });
})();
</script>
</body>
</html>
