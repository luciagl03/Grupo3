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

// Plaza data
$precio_hora = 0; $direccion = ''; $dni_propietario = '';
if ($id_plaza > 0) {
    $stmtPl = $_conexion->prepare(
        "SELECT p.Precio, p.Direccion, p.DNI AS dni_propietario, u.Nombre AS nombre_propietario
         FROM PLAZA p JOIN USUARIO u ON p.DNI = u.DNI WHERE p.ID_plaza = ?"
    );
    $stmtPl->bind_param('i', $id_plaza);
    $stmtPl->execute();
    if ($rowPl = $stmtPl->get_result()->fetch_assoc()) {
        $precio_hora        = $rowPl['Precio'];
        $direccion          = $rowPl['Direccion'] ?? '';
        $dni_propietario    = $rowPl['dni_propietario'] ?? '';
        $nombre_propietario = $rowPl['nombre_propietario'] ?? '';
    }
    $stmtPl->close();
}

// Owners manage their chats from mis_plazas.php
if (!empty($dni) && $dni === $dni_propietario) {
    header('Location: mis_plazas.php?tab=chats&id_plaza=' . $id_plaza);
    exit;
}

// Cargar reservas confirmadas de esta plaza para bloquear el calendario
$reservas_ocupadas = [];
if ($id_plaza > 0) {
    $stmtOcup = $_conexion->prepare(
        "SELECT Fecha, Hora_entrada, Hora_salida
         FROM RESERVA
         WHERE ID_plaza = ? AND Estado = 'confirmada' AND Fecha >= CURDATE()
         ORDER BY Fecha ASC, Hora_entrada ASC"
    );
    $stmtOcup->bind_param('i', $id_plaza);
    $stmtOcup->execute();
    $rowsOcup = $stmtOcup->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtOcup->close();
    foreach ($rowsOcup as $r) {
        $reservas_ocupadas[] = [
            'fecha'        => $r['Fecha'],
            'hora_entrada' => substr($r['Hora_entrada'], 0, 5),
            'hora_salida'  => substr($r['Hora_salida'],  0, 5),
        ];
    }
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
<style>
    /* Punto rojo en días con reservas existentes */
    .flatpickr-day.has-reserva { position: relative; }
    .flatpickr-day.has-reserva::after {
        content: '';
        position: absolute;
        bottom: 3px;
        left: 50%;
        transform: translateX(-50%);
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: #f59e0b;
    }
    .flatpickr-day.has-reserva.selected::after,
    .flatpickr-day.has-reserva.inRange::after { background: #fff; }
</style>
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

        // Reservas ya confirmadas para esta plaza (para bloqueo de calendario)
        var reservasOcupadas = <?php echo json_encode($reservas_ocupadas); ?>;

        // Índice de días con al menos una reserva (para el punto visual en Flatpickr)
        var diasConReserva = {};
        reservasOcupadas.forEach(function(r) { diasConReserva[r.fecha] = true; });

        var errorBox = document.createElement('div');
        errorBox.style.cssText = 'display:none;margin:1rem 0 0;padding:.75rem 1rem;border-radius:12px;background:#fef2f2;border:1.5px solid #fca5a5;color:#991b1b;font-size:.85rem;font-weight:500;line-height:1.4;align-items:center;gap:.5rem;';
        form.querySelector('.form-actions').before(errorBox);

        function mostrarError(msg) {
            errorBox.style.display = 'flex';
            errorBox.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg><span>' + msg + '</span>';
            errorBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        // Formatea un Date a "YYYY-MM-DD HH:MM" en hora LOCAL (evita el bug UTC de toISOString)
        function toLocalDT(d) {
            var p = function(n) { return String(n).padStart(2, '0'); };
            return d.getFullYear() + '-' + p(d.getMonth()+1) + '-' + p(d.getDate())
                 + ' ' + p(d.getHours()) + ':' + p(d.getMinutes());
        }

        // Formatea un Date a "YYYY-MM-DD" en hora local
        function toLocalDate(d) {
            var p = function(n) { return String(n).padStart(2, '0'); };
            return d.getFullYear() + '-' + p(d.getMonth()+1) + '-' + p(d.getDate());
        }

        // Detecta si el horario elegido choca con alguna reserva confirmada
        function hayConflicto(entrada, salida) {
            var fStr  = toLocalDate(entrada);
            var hIn   = String(entrada.getHours()).padStart(2,'0') + ':' + String(entrada.getMinutes()).padStart(2,'0');
            var hOut  = String(salida.getHours()).padStart(2,'0')  + ':' + String(salida.getMinutes()).padStart(2,'0');

            return reservasOcupadas.some(function(r) {
                return r.fecha === fStr && hIn < r.hora_salida && hOut > r.hora_entrada;
            });
        }

        var fechaEntrada = null;
        var fechaSalida = null;

        // Inicializar Flatpickr con puntos en días ocupados
        var fp = flatpickr("#dateRange", {
            mode: "range",
            locale: "es",
            minDate: "today",
            maxDate: new Date().fp_incr(365),
            dateFormat: "d/m/Y",
            onDayCreate: function(dObj, dStr, fpInst, dayElem) {
                var d = dayElem.dateObj;
                if (!d) return;
                var p = function(n) { return String(n).padStart(2,'0'); };
                var fStr = d.getFullYear() + '-' + p(d.getMonth()+1) + '-' + p(d.getDate());
                if (diasConReserva[fStr]) {
                    dayElem.classList.add('has-reserva');
                    dayElem.title = 'Este día tiene reservas (puede haber huecos libres)';
                }
            },
            onChange: function(selectedDates) {
                if (selectedDates.length === 2) {
                    fechaEntrada = selectedDates[0];
                    fechaSalida = selectedDates[1];
                } else if (selectedDates.length === 1) {
                    fechaEntrada = selectedDates[0];
                    fechaSalida = selectedDates[0];
                }
                actualizarPrecio();
                comprobarConflicto();
            }
        });

        // Actualizar precio cuando cambian las horas
        [horaEntrada, minutoEntrada, horaSalida, minutoSalida].forEach(function(el) {
            el.addEventListener('change', function() {
                actualizarPrecio();
                comprobarConflicto();
            });
        });

        function actualizarPrecio() {
            if (!fechaEntrada || !fechaSalida) {
                duracionEl.textContent = '—';
                precioTotalEl.textContent = '—';
                return;
            }

            var entrada = new Date(fechaEntrada);
            entrada.setHours(parseInt(horaEntrada.value), parseInt(minutoEntrada.value), 0);

            var salida = new Date(fechaSalida);
            salida.setHours(parseInt(horaSalida.value), parseInt(minutoSalida.value), 0);

            // Campos ocultos con hora LOCAL (no UTC)
            hiddenEntrada.value = toLocalDT(entrada);
            hiddenSalida.value  = toLocalDT(salida);

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
                if (horasRestantes > 0) duracionTexto += ' y ' + horasRestantes + 'h';
            } else {
                duracionTexto = horas + ' hora' + (horas > 1 ? 's' : '');
            }

            duracionEl.textContent = duracionTexto;
            precioTotalEl.textContent = (horas * precioPorHora).toFixed(2) + ' €';
        }

        // Comprueba conflictos y muestra/oculta el aviso
        function comprobarConflicto() {
            if (!fechaEntrada) return false;

            var entrada = new Date(fechaEntrada);
            entrada.setHours(parseInt(horaEntrada.value), parseInt(minutoEntrada.value), 0);

            var salida = new Date(fechaSalida || fechaEntrada);
            salida.setHours(parseInt(horaSalida.value), parseInt(minutoSalida.value), 0);

            if (salida <= entrada) return false;

            if (hayConflicto(entrada, salida)) {
                mostrarError('Este horario ya está reservado. Por favor, elige otro horario o consulta los puntos naranjas en el calendario.');
                return true;
            }

            errorBox.style.display = 'none';
            return false;
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

            var ahoraConMargen = new Date(Date.now() - 5 * 60 * 1000);
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

            if (hayConflicto(entrada, salida)) {
                e.preventDefault();
                mostrarError('Este horario ya está reservado. Por favor, elige otro horario.');
                return;
            }
        });
    })();
</script>
</body>
</html>