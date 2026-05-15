<?php
/**
 * Booking / reservation page.
 * Integration point: connect to existing RESERVA table and flow when ready.
 * For now shows a placeholder and the selected plaza id.
 */
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../sesion/login.php');
    exit;
}

require_once '../sesion/conexion.php';

$id_plaza = isset($_GET['id_plaza']) ? (int) $_GET['id_plaza'] : 0;
$dni = $_SESSION['dni'] ?? '';

$sql = "SELECT Precio, Direccion FROM PLAZA WHERE ID_plaza = ?";
$stmt = $_conexion->prepare($sql);

if (!$stmt) {
    die($_conexion->error);
}

$stmt->bind_param("i", $id_plaza);
$stmt->execute();
$result = $stmt->get_result();

$precio_hora = 0;
$direccion = '';

if ($row = $result->fetch_assoc()) {
    $precio_hora = $row['Precio'];
    $direccion = $row['Direccion'] ?? '';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Reservar plaza — Zpot</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Iconos -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Flatpickr para calendario elegante -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">

    <link rel="stylesheet" href="../app.css">
    <link rel="stylesheet" href="../styles/reservar.css">
</head>
<body class="booking-page">

<div class="layout">
    <div class="layout-container">
        <header class="booking-header">
            <a href="../index.php" class="back-link"><i data-lucide="arrow-left"></i> Volver al mapa</a>
            <h1 class="headline">Finalizar reserva</h1>
            <p class="support">Confirma los detalles de tu estancia en la plaza seleccionada.</p>
        </header>

        <main class="card booking-card">
            <!-- Resumen rápido de la plaza -->
            <div class="booking-summary">
                <div class="summary-icon">
                    <i data-lucide="map-pin"></i>
                </div>
                <div class="summary-text">
                    <span class="summary-label">Plaza seleccionada</span>
                    <strong class="summary-address"><?php echo !empty($direccion) ? htmlspecialchars($direccion) : 'Plaza #' . $id_plaza; ?></strong>
                </div>
            </div>

            <form method="POST" action="pago.php" class="booking-form">
                <input type="hidden" name="id_plaza" value="<?php echo $id_plaza; ?>">
                <input type="hidden" name="dni" value="<?php echo htmlspecialchars($dni); ?>">

                <!-- Selector de fechas -->
                <div class="date-time-section">
                    <div class="section-header">
                        <i data-lucide="calendar"></i>
                        <h3>Selecciona las fechas</h3>
                    </div>
                    
                    <div class="date-picker-wrapper">
                        <input type="text" id="dateRange" placeholder="Selecciona entrada y salida" readonly>
                        <input type="hidden" name="hora_entrada" id="hiddenEntrada" required>
                        <input type="hidden" name="hora_salida" id="hiddenSalida" required>
                    </div>
                </div>

                <!-- Selector de horas -->
                <div class="time-section">
                    <div class="section-header">
                        <i data-lucide="clock"></i>
                        <h3>Selecciona las horas</h3>
                    </div>
                    
                    <div class="time-grid">
                        <div class="time-picker-group">
                            <label class="time-label">Hora de entrada</label>
                            <div class="time-picker-custom">
                                <select id="horaEntrada" class="time-select">
                                    <?php for($h = 0; $h < 24; $h++): ?>
                                        <option value="<?php echo sprintf('%02d', $h); ?>"><?php echo sprintf('%02d', $h); ?></option>
                                    <?php endfor; ?>
                                </select>
                                <span class="time-separator">:</span>
                                <select id="minutoEntrada" class="time-select">
                                    <option value="00">00</option>
                                    <option value="15">15</option>
                                    <option value="30">30</option>
                                    <option value="45">45</option>
                                </select>
                            </div>
                        </div>

                        <div class="time-picker-group">
                            <label class="time-label">Hora de salida</label>
                            <div class="time-picker-custom">
                                <select id="horaSalida" class="time-select">
                                    <?php for($h = 0; $h < 24; $h++): ?>
                                        <option value="<?php echo sprintf('%02d', $h); ?>"><?php echo sprintf('%02d', $h); ?></option>
                                    <?php endfor; ?>
                                </select>
                                <span class="time-separator">:</span>
                                <select id="minutoSalida" class="time-select">
                                    <option value="00">00</option>
                                    <option value="15">15</option>
                                    <option value="30">30</option>
                                    <option value="45">45</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resumen de precio -->
                <div class="price-summary">
                    <div class="price-details">
                        <div class="price-row">
                            <span class="price-label">Precio por hora</span>
                            <span class="price-value"><?php echo number_format($precio_hora, 2); ?> €</span>
                        </div>
                        <div class="price-row">
                            <span class="price-label">Duración estimada</span>
                            <span class="price-value" id="duracionEstimada">—</span>
                        </div>
                        <div class="price-row price-total">
                            <span class="price-label">Total estimado</span>
                            <span class="price-value" id="precioTotal">—</span>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        Confirmar y pagar <i data-lucide="credit-card"></i>
                    </button>
                </div>
            </form>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<script>
    lucide.createIcons();

    (function () {
        var form = document.querySelector('.booking-form');
        var hiddenEntrada = document.getElementById('hiddenEntrada');
        var hiddenSalida = document.getElementById('hiddenSalida');
        var horaEntrada = document.getElementById('horaEntrada');
        var minutoEntrada = document.getElementById('minutoEntrada');
        var horaSalida = document.getElementById('horaSalida');
        var minutoSalida = document.getElementById('minutoSalida');
        var duracionEl = document.getElementById('duracionEstimada');
        var precioTotalEl = document.getElementById('precioTotal');
        var precioPorHora = <?php echo (float) $precio_hora; ?>;
        
        var errorBox = document.createElement('div');
        errorBox.style.cssText = 'display:none;margin:1rem 0 0;padding:.75rem 1rem;border-radius:12px;background:#fef2f2;border:1.5px solid #fca5a5;color:#991b1b;font-size:.85rem;font-weight:500;line-height:1.4;align-items:center;gap:.5rem;';
        form.querySelector('.form-actions').before(errorBox);

        function mostrarError(msg) {
            errorBox.style.display = 'flex';
            errorBox.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg><span>' + msg + '</span>';
            errorBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        var fechaEntrada = null;
        var fechaSalida = null;

        // Inicializar Flatpickr con rango de fechas
        var fp = flatpickr("#dateRange", {
            mode: "range",
            locale: "es",
            minDate: "today",
            maxDate: new Date().fp_incr(365),
            dateFormat: "d/m/Y",
            onChange: function(selectedDates) {
                if (selectedDates.length === 2) {
                    fechaEntrada = selectedDates[0];
                    fechaSalida = selectedDates[1];
                    actualizarPrecio();
                }
            }
        });

        // Actualizar precio cuando cambian las horas
        [horaEntrada, minutoEntrada, horaSalida, minutoSalida].forEach(function(el) {
            el.addEventListener('change', actualizarPrecio);
        });

        function actualizarPrecio() {
            if (!fechaEntrada || !fechaSalida) {
                duracionEl.textContent = '—';
                precioTotalEl.textContent = '—';
                return;
            }

            // Crear fechas completas con horas
            var entrada = new Date(fechaEntrada);
            entrada.setHours(parseInt(horaEntrada.value), parseInt(minutoEntrada.value), 0);
            
            var salida = new Date(fechaSalida);
            salida.setHours(parseInt(horaSalida.value), parseInt(minutoSalida.value), 0);

            // Actualizar campos ocultos
            hiddenEntrada.value = entrada.toISOString().slice(0, 16).replace('T', ' ');
            hiddenSalida.value = salida.toISOString().slice(0, 16).replace('T', ' ');

            // Calcular duración
            var diff = salida - entrada;
            if (diff <= 0) {
                duracionEl.textContent = '—';
                precioTotalEl.textContent = '—';
                return;
            }

            var horas = Math.ceil(diff / 3600000);
            var dias = Math.floor(horas / 24);
            var horasRestantes = horas % 24;
            
            var duracionTexto = '';
            if (dias > 0) {
                duracionTexto = dias + ' día' + (dias > 1 ? 's' : '');
                if (horasRestantes > 0) {
                    duracionTexto += ' y ' + horasRestantes + 'h';
                }
            } else {
                duracionTexto = horas + ' hora' + (horas > 1 ? 's' : '');
            }
            
            duracionEl.textContent = duracionTexto;
            
            var precioTotal = horas * precioPorHora;
            precioTotalEl.textContent = precioTotal.toFixed(2) + ' €';
        }

        form.addEventListener('submit', function (e) {
            errorBox.style.display = 'none';

            if (!fechaEntrada || !fechaSalida) {
                e.preventDefault();
                mostrarError('Selecciona las fechas de entrada y salida.');
                return;
            }

            var entrada = new Date(fechaEntrada);
            entrada.setHours(parseInt(horaEntrada.value), parseInt(minutoEntrada.value), 0);
            
            var salida = new Date(fechaSalida);
            salida.setHours(parseInt(horaSalida.value), parseInt(minutoSalida.value), 0);

            var ahora = new Date();
            // Margen de 5 minutos para evitar falsos positivos por segundos/timezone
            var ahoraConMargen = new Date(ahora.getTime() - 5 * 60 * 1000);
            if (entrada < ahoraConMargen) {
                e.preventDefault();
                mostrarError('La hora de entrada ya ha pasado. Selecciona una hora futura.');
                return;
            }

            if (salida <= entrada) {
                e.preventDefault();
                mostrarError('La hora de salida debe ser posterior a la de entrada.');
                return;
            }
        });
    })();
</script>
</body>
</html>