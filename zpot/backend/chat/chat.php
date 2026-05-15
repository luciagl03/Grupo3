<?php
session_start();
require_once '../sesion/conexion.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: ../sesion/login.php');
    exit;
}

$id_reserva = isset($_GET['id_reserva']) ? (int)$_GET['id_reserva'] : 0;
$dni = $_SESSION['dni'] ?? '';

if ($id_reserva <= 0) {
    header('Location: mis_reservas.php');
    exit;
}

// Verificar acceso y obtener info de la reserva
$stmt = $_conexion->prepare(
    "SELECT r.ID_reserva, r.DNI AS dni_inquilino, r.Fecha, r.Hora_entrada, r.Hora_salida,
            p.DNI AS dni_propietario, p.Direccion,
            u_i.Nombre AS nombre_inquilino, u_i.Apellidos AS apellidos_inquilino,
            u_p.Nombre AS nombre_propietario, u_p.Apellidos AS apellidos_propietario
     FROM RESERVA r
     JOIN PLAZA p ON r.ID_plaza = p.ID_plaza
     JOIN USUARIO u_i ON r.DNI = u_i.DNI
     JOIN USUARIO u_p ON p.DNI = u_p.DNI
     WHERE r.ID_reserva = ? AND (r.DNI = ? OR p.DNI = ?)"
);
$stmt->bind_param('iss', $id_reserva, $dni, $dni);
$stmt->execute();
$reserva = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$reserva) {
    header('Location: mis_reservas.php');
    exit;
}

$yoSoyInquilino = $dni === $reserva['dni_inquilino'];
$nombreOtro = $yoSoyInquilino
    ? $reserva['nombre_propietario'] . ' ' . mb_substr($reserva['apellidos_propietario'], 0, 1) . '.'
    : $reserva['nombre_inquilino']   . ' ' . mb_substr($reserva['apellidos_inquilino'],   0, 1) . '.';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Chat — Zpot</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="../app.css">
    <style>
        * { box-sizing: border-box; }
        body { background: var(--brand-bg); margin:0; font-family:'DM Sans',sans-serif; }

        /* ── Layout chat ── */
        .chat-layout {
            display:flex; flex-direction:column;
            height: 100dvh; max-width: 640px; margin:0 auto;
            background:#fff; box-shadow: var(--shadow-lg);
        }

        /* ── Header ── */
        .chat-header {
            display:flex; align-items:center; gap:0.75rem;
            padding:0.875rem 1rem; border-bottom:1px solid var(--border);
            background:#fff; flex-shrink:0;
            position:sticky; top:0; z-index:10;
        }
        .chat-back { color:var(--brand-dark); text-decoration:none; display:flex; align-items:center; }
        .chat-avatar {
            width:38px; height:38px; border-radius:50%;
            background:var(--brand-yellow); display:flex; align-items:center;
            justify-content:center; font-weight:700; font-size:0.9rem;
            color:var(--brand-dark); flex-shrink:0;
        }
        .chat-header-info { flex:1; min-width:0; }
        .chat-header-name { font-size:0.95rem; font-weight:700; color:var(--brand-dark); margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .chat-header-sub  { font-size:0.72rem; color:var(--text-muted); margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

        /* ── Mensajes ── */
        .chat-messages {
            flex:1; overflow-y:auto; padding:1rem;
            display:flex; flex-direction:column; gap:0.5rem;
            scroll-behavior:smooth;
        }
        .chat-messages::-webkit-scrollbar { width:3px; }
        .chat-messages::-webkit-scrollbar-thumb { background:var(--border); border-radius:4px; }

        .msg-wrap { display:flex; flex-direction:column; max-width:78%; }
        .msg-wrap.mio  { align-self:flex-end; align-items:flex-end; }
        .msg-wrap.otro { align-self:flex-start; align-items:flex-start; }

        .msg-bubble {
            padding:0.55rem 0.875rem; border-radius:18px;
            font-size:0.875rem; line-height:1.45; word-break:break-word;
        }
        .msg-wrap.mio  .msg-bubble { background:var(--brand-dark); color:#fff; border-bottom-right-radius:4px; }
        .msg-wrap.otro .msg-bubble { background:var(--brand-bg); color:var(--brand-dark); border-bottom-left-radius:4px; border:1px solid var(--border); }

        .msg-meta { font-size:0.65rem; color:var(--text-muted); margin-top:0.2rem; display:flex; align-items:center; gap:0.3rem; }

        .msg-fecha-sep {
            text-align:center; font-size:0.7rem; color:var(--text-muted);
            padding:0.5rem; background:var(--brand-bg); border-radius:999px;
            align-self:center; padding:0.3rem 0.75rem; margin:0.25rem 0;
        }

        .chat-empty {
            flex:1; display:flex; flex-direction:column; align-items:center;
            justify-content:center; color:var(--text-muted); gap:0.5rem;
            padding:2rem;
        }
        .chat-empty p { font-size:0.875rem; text-align:center; margin:0; }

        /* ── Input ── */
        .chat-input-wrap {
            display:flex; align-items:flex-end; gap:0.5rem;
            padding:0.75rem 1rem calc(0.75rem + env(safe-area-inset-bottom));
            border-top:1px solid var(--border); background:#fff; flex-shrink:0;
        }
        .chat-textarea {
            flex:1; border:1.5px solid var(--border); border-radius:22px;
            padding:0.6rem 1rem; font-family:inherit; font-size:0.875rem;
            color:var(--brand-dark); resize:none; min-height:40px; max-height:120px;
            overflow-y:auto; line-height:1.4; transition:border-color 0.15s;
        }
        .chat-textarea:focus { outline:none; border-color:var(--brand-dark); }
        .chat-send {
            width:40px; height:40px; border-radius:50%; background:var(--brand-dark);
            border:none; cursor:pointer; display:flex; align-items:center; justify-content:center;
            flex-shrink:0; transition:background 0.15s, transform 0.1s; color:var(--brand-yellow);
        }
        .chat-send:hover  { background:#1a1915; }
        .chat-send:active { transform:scale(0.92); }
        .chat-send:disabled { opacity:0.4; cursor:not-allowed; }

        /* Info banner reserva */
        .reserva-banner {
            background:var(--brand-bg); border-bottom:1px solid var(--border);
            padding:0.6rem 1rem; font-size:0.75rem; color:var(--text-muted);
            display:flex; align-items:center; gap:0.4rem; flex-shrink:0;
        }

        @media (min-width:640px) {
            .chat-layout { height:100vh; border-radius:0; }
        }
    </style>
</head>
<body>
<div class="chat-layout">

    <!-- Header -->
    <div class="chat-header">
        <a href="mis_reservas.php" class="chat-back">
            <i data-lucide="arrow-left" width="20" height="20"></i>
        </a>
        <div class="chat-avatar"><?php echo mb_strtoupper(mb_substr($nombreOtro, 0, 1)); ?></div>
        <div class="chat-header-info">
            <p class="chat-header-name"><?php echo htmlspecialchars($nombreOtro); ?></p>
            <p class="chat-header-sub"><?php echo $yoSoyInquilino ? 'Propietario de la plaza' : 'Inquilino'; ?></p>
        </div>
    </div>

    <!-- Info reserva -->
    <div class="reserva-banner">
        <i data-lucide="map-pin" width="12" height="12"></i>
        <?php echo htmlspecialchars($reserva['Direccion']); ?> &middot;
        <?php echo date('d/m/Y', strtotime($reserva['Fecha'])); ?> &middot;
        <?php echo substr($reserva['Hora_entrada'], 0, 5); ?>–<?php echo substr($reserva['Hora_salida'], 0, 5); ?>
    </div>

    <!-- Mensajes -->
    <div class="chat-messages" id="chatMessages">
        <div class="chat-empty" id="chatEmpty">
            <i data-lucide="message-circle" width="32" height="32"></i>
            <p>Aún no hay mensajes.<br>Sé el primero en escribir.</p>
        </div>
    </div>

    <!-- Input -->
    <div class="chat-input-wrap">
        <textarea class="chat-textarea" id="msgInput" placeholder="Escribe un mensaje…" rows="1"
                  maxlength="1000"></textarea>
        <button class="chat-send" id="sendBtn" disabled>
            <i data-lucide="send" width="16" height="16"></i>
        </button>
    </div>

</div>

<script>
lucide.createIcons();

var ID_RESERVA = <?php echo $id_reserva; ?>;
var API        = '../chat/chat_api.php';
var msgInput   = document.getElementById('msgInput');
var sendBtn    = document.getElementById('sendBtn');
var chatEl     = document.getElementById('chatMessages');
var emptyEl    = document.getElementById('chatEmpty');
var lastMsgId  = 0;
var ultimaFecha = '';

function formatHora(str) {
    var d = new Date(str);
    return d.toLocaleTimeString('es-ES', { hour:'2-digit', minute:'2-digit' });
}
function formatFechaSep(str) {
    var d = new Date(str);
    var hoy = new Date();
    if (d.toDateString() === hoy.toDateString()) return 'Hoy';
    var ayer = new Date(); ayer.setDate(ayer.getDate()-1);
    if (d.toDateString() === ayer.toDateString()) return 'Ayer';
    return d.toLocaleDateString('es-ES', {day:'2-digit', month:'long', year:'numeric'});
}
function esc(s) {
    return s ? String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;') : '';
}

// ── Cargar mensajes ──────────────────────────
function cargarMensajes(scroll) {
    fetch(API + '?id_reserva=' + ID_RESERVA, { credentials:'same-origin' })
        .then(function(r){ return r.json(); })
        .then(function(data) {
            if (!data.success) return;
            if (data.mensajes.length === 0) {
                emptyEl.style.display = 'flex';
                return;
            }
            emptyEl.style.display = 'none';

            // Solo añadir mensajes nuevos
            var nuevos = data.mensajes.filter(function(m){ return m.id > lastMsgId; });
            if (nuevos.length === 0) return;

            nuevos.forEach(function(m) {
                var fechaStr = formatFechaSep(m.fecha);
                if (fechaStr !== ultimaFecha) {
                    var sep = document.createElement('div');
                    sep.className = 'msg-fecha-sep';
                    sep.textContent = fechaStr;
                    chatEl.appendChild(sep);
                    ultimaFecha = fechaStr;
                }

                var wrap = document.createElement('div');
                wrap.className = 'msg-wrap ' + (m.es_mio ? 'mio' : 'otro');
                wrap.innerHTML =
                    '<div class="msg-bubble">' + esc(m.contenido) + '</div>' +
                    '<div class="msg-meta">' +
                        (!m.es_mio ? '<span>' + esc(m.emisor) + '</span>' : '') +
                        '<span>' + formatHora(m.fecha) + '</span>' +
                        (m.es_mio ? (m.leido ? ' <i data-lucide="check-check" width="11" height="11"></i>' : ' <i data-lucide="check" width="11" height="11"></i>') : '') +
                    '</div>';
                chatEl.appendChild(wrap);
                lastMsgId = Math.max(lastMsgId, m.id);
            });

            lucide.createIcons();

            if (scroll || nuevos.length > 0) {
                chatEl.scrollTop = chatEl.scrollHeight;
            }
        })
        .catch(function(){});
}

// ── Enviar mensaje ───────────────────────────
function enviarMensaje() {
    var texto = msgInput.value.trim();
    if (!texto) return;

    sendBtn.disabled = true;

    fetch(API, {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        credentials:'same-origin',
        body: JSON.stringify({ id_reserva: ID_RESERVA, mensaje: texto })
    })
    .then(function(r){ return r.json(); })
    .then(function(data) {
        if (data.success) {
            msgInput.value = '';
            msgInput.style.height = 'auto';
            sendBtn.disabled = true;
            cargarMensajes(true);
        }
    })
    .catch(function(){})
    .finally(function(){ sendBtn.disabled = false; });
}

// ── Eventos ──────────────────────────────────
msgInput.addEventListener('input', function() {
    sendBtn.disabled = this.value.trim() === '';
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
});

msgInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        if (!sendBtn.disabled) enviarMensaje();
    }
});

sendBtn.addEventListener('click', enviarMensaje);

// ── Arranque ─────────────────────────────────
cargarMensajes(true);
setInterval(function(){ cargarMensajes(false); }, 3000);
</script>
</body>
</html>
