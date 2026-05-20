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

// Endpoint JSON para polling de mensajes (evita chat_api.php bloqueado por WAF)
if (isset($_GET['msgonly'])) {
    header('Content-Type: application/json; charset=utf-8');
    $id_p  = (int)($_GET['id_plaza'] ?? 0);
    $after = (int)($_GET['after'] ?? 0);
    $d     = $_SESSION['dni'] ?? '';
    if ($id_p > 0 && !empty($d)) {
        $stmtJ = $_conexion->prepare(
            "SELECT ID_mensaje, DNI_emisor, Contenido, Fecha FROM MENSAJE
             WHERE ID_plaza = ? AND DNI_inquilino = ? AND ID_mensaje > ? ORDER BY Fecha ASC"
        );
        $stmtJ->bind_param('isi', $id_p, $d, $after);
        $stmtJ->execute();
        echo json_encode(['ok' => true, 'msgs' => $stmtJ->get_result()->fetch_all(MYSQLI_ASSOC)], JSON_UNESCAPED_UNICODE);
        $stmtJ->close();
    } else {
        echo json_encode(['ok' => false]);
    }
    exit;
}

$id_plaza = isset($_GET['id_plaza']) ? (int) $_GET['id_plaza'] : 0;
$dni = $_SESSION['dni'] ?? '';

// 1. Plaza data first (needed by POST handler and owner redirect)
$precio_hora = 0; $direccion = ''; $dni_propietario = ''; $nombre_propietario = '';
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

// 2. POST: tenant sends contact message (form submit, no AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_chat_msg'])) {
    $contenido = trim($_POST['_chat_msg'] ?? '');
    if ($contenido !== '' && mb_strlen($contenido) <= 1000 && $id_plaza > 0 && !empty($dni)) {
        $stmtIns = $_conexion->prepare(
            "INSERT INTO MENSAJE (ID_plaza, DNI_inquilino, DNI_emisor, Contenido) VALUES (?,?,?,?)"
        );
        $stmtIns->bind_param('isss', $id_plaza, $dni, $dni, $contenido);
        $stmtIns->execute();
        $stmtIns->close();
        // Notify owner
        $helperPath = __DIR__ . '/../notificaciones/notificaciones_helper.php';
        if (!empty($dni_propietario) && file_exists($helperPath)) {
            require_once $helperPath;
            crearNotificacion($_conexion, $dni_propietario, 'nueva_consulta',
                'Nueva consulta', 'Nuevo mensaje sobre tu plaza en ' . $direccion . '.', $id_plaza);
        }
    }
    header('Location: reserva.php?id_plaza=' . $id_plaza . '&contactar=1');
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

// Cargar mensajes de contacto de esta plaza (para el panel de chat sin AJAX)
$mensajes_contacto = [];
if ($id_plaza > 0 && !empty($dni) && !empty($dni_propietario) && $dni !== $dni_propietario) {
    $stmtM = $_conexion->prepare(
        "SELECT ID_mensaje, DNI_emisor, Contenido, Fecha
         FROM MENSAJE
         WHERE ID_plaza = ? AND DNI_inquilino = ?
         ORDER BY Fecha ASC"
    );
    $stmtM->bind_param('is', $id_plaza, $dni);
    $stmtM->execute();
    $mensajes_contacto = $stmtM->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtM->close();
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

            <?php if ($id_plaza > 0 && !empty($dni_propietario) && $dni !== $dni_propietario): ?>
            <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid var(--border);text-align:center;">
                <button id="btnContactar" type="button"
                   style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.65rem 1.25rem;border:1.5px solid var(--brand-dark);color:var(--brand-dark);background:transparent;border-radius:999px;cursor:pointer;font-weight:600;font-size:0.875rem;font-family:inherit;">
                    <i data-lucide="message-circle" width="16" height="16"></i>
                    Contactar propietario
                </button>
            </div>

            <div id="chatOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:999;"></div>
            <div id="chatPanel" style="display:none;position:fixed;bottom:0;left:0;right:0;max-width:640px;margin:0 auto;background:#fff;border-radius:20px 20px 0 0;box-shadow:0 -4px 32px rgba(0,0,0,.18);z-index:1000;height:75dvh;flex-direction:column;">
                <div style="display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem 0.75rem;border-bottom:1px solid #e5e7eb;flex-shrink:0;">
                    <div>
                        <strong style="font-size:0.95rem;color:#1a1915;"><?php echo htmlspecialchars($nombre_propietario); ?></strong>
                        <p style="margin:0.1rem 0 0;font-size:0.72rem;color:#888;">Propietario · <?php echo htmlspecialchars($direccion); ?></p>
                    </div>
                    <button id="btnCerrarChat" style="background:none;border:none;cursor:pointer;font-size:1.5rem;color:#888;line-height:1;padding:0.25rem;">×</button>
                </div>
                <div id="chatMsgs" style="flex:1;overflow-y:auto;padding:1rem;display:flex;flex-direction:column;gap:0.5rem;scroll-behavior:smooth;min-height:0;">
                    <?php if (empty($mensajes_contacto)): ?>
                    <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#aaa;padding:2rem;text-align:center;">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        <p style="margin:0.5rem 0 0;font-size:0.875rem;">Escribe tu primera pregunta al propietario.</p>
                    </div>
                    <?php else: foreach ($mensajes_contacto as $msg):
                        $es_mio = $msg['DNI_emisor'] === $dni;
                        $hora_msg = date('H:i', strtotime($msg['Fecha']));
                    ?>
                    <div style="display:flex;flex-direction:column;max-width:78%;<?php echo $es_mio ? 'align-self:flex-end;align-items:flex-end;' : 'align-self:flex-start;align-items:flex-start;'; ?>">
                        <div style="padding:.5rem .875rem;border-radius:18px;font-size:.875rem;line-height:1.45;word-break:break-word;<?php echo $es_mio ? 'background:#1a1915;color:#fff;border-bottom-right-radius:4px;' : 'background:#f4f4f0;color:#1a1915;border-bottom-left-radius:4px;border:1px solid #e5e7eb;'; ?>">
                            <?php echo htmlspecialchars($msg['Contenido']); ?>
                        </div>
                        <div style="font-size:.65rem;color:#aaa;margin-top:.2rem;"><?php echo $hora_msg; ?></div>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
                <form method="POST" action="reserva.php?id_plaza=<?php echo $id_plaza; ?>&contactar=1"
                      style="display:flex;gap:0.5rem;padding:0.75rem 1rem calc(0.75rem + env(safe-area-inset-bottom));border-top:1px solid #e5e7eb;flex-shrink:0;align-items:flex-end;">
                    <textarea name="_chat_msg" id="chatInput" rows="1" maxlength="1000" placeholder="Escribe un mensaje…"
                        style="flex:1;border:1.5px solid #e5e7eb;border-radius:22px;padding:0.55rem 1rem;font-family:inherit;font-size:0.875rem;resize:none;min-height:40px;max-height:100px;overflow-y:auto;line-height:1.4;outline:none;"></textarea>
                    <button type="submit" id="chatSendBtn"
                        style="width:40px;height:40px;border-radius:50%;background:#1a1915;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#f4dd49;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </button>
                </form>
            </div>

            <script>
            (function() {
                var overlay   = document.getElementById('chatOverlay');
                var panel     = document.getElementById('chatPanel');
                var msgsEl    = document.getElementById('chatMsgs');
                var lastId    = <?php echo !empty($mensajes_contacto) ? (int)end($mensajes_contacto)['ID_mensaje'] : 0; ?>;
                var dniYo     = <?php echo json_encode($dni); ?>;
                var pollTimer = null;

                function esc(s){ return s?String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'):''; }
                function fmtH(s){ return new Date(s.replace(' ','T')).toLocaleTimeString('es-ES',{hour:'2-digit',minute:'2-digit'}); }

                function appendMsg(m) {
                    var mio = (m.DNI_emisor === dniYo);
                    var w = document.createElement('div');
                    w.style.cssText = 'display:flex;flex-direction:column;max-width:78%;' + (mio?'align-self:flex-end;align-items:flex-end;':'align-self:flex-start;align-items:flex-start;');
                    w.innerHTML = '<div style="padding:.5rem .875rem;border-radius:18px;font-size:.875rem;line-height:1.45;word-break:break-word;' +
                        (mio?'background:#1a1915;color:#fff;border-bottom-right-radius:4px;':'background:#f4f4f0;color:#1a1915;border-bottom-left-radius:4px;border:1px solid #e5e7eb;') +
                        '">' + esc(m.Contenido) + '</div><div style="font-size:.65rem;color:#aaa;margin-top:.2rem;">' + fmtH(m.Fecha) + '</div>';
                    msgsEl.appendChild(w);
                    lastId = Math.max(lastId, parseInt(m.ID_mensaje));
                    msgsEl.scrollTop = msgsEl.scrollHeight;
                }

                function poll() {
                    fetch('reserva.php?id_plaza=<?php echo $id_plaza; ?>&msgonly=1&after=' + lastId, {credentials:'same-origin'})
                        .then(function(r){ return r.json(); })
                        .then(function(d){ if(d.ok && d.msgs && d.msgs.length) d.msgs.forEach(appendMsg); })
                        .catch(function(){});
                }

                function abrirChat() {
                    overlay.style.display = 'block';
                    panel.style.display   = 'flex';
                    msgsEl.scrollTop = msgsEl.scrollHeight;
                    clearInterval(pollTimer);
                    pollTimer = setInterval(poll, 5000);
                }
                function cerrarChat() {
                    overlay.style.display = 'none';
                    panel.style.display   = 'none';
                    clearInterval(pollTimer);
                }
                document.getElementById('btnContactar').addEventListener('click', abrirChat);
                document.getElementById('btnCerrarChat').addEventListener('click', cerrarChat);
                overlay.addEventListener('click', cerrarChat);

                var inputEl = document.getElementById('chatInput');
                inputEl.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = Math.min(this.scrollHeight, 100) + 'px';
                });
                inputEl.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        if (this.value.trim()) this.closest('form').submit();
                    }
                });

                <?php if (isset($_GET['contactar'])): ?>abrirChat();<?php endif; ?>
            })();
            </script>
            <?php endif; ?>
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