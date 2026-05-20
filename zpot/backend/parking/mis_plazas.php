<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: sesion/login.php');
    exit;
}

require_once '../sesion/conexion.php';

$dni = $_SESSION['dni'];

// Endpoint JSON para polling (propietario ve mensajes en tiempo real)
if (isset($_GET['msgonly'])) {
    header('Content-Type: application/json; charset=utf-8');
    $id_p  = (int)($_GET['id_plaza'] ?? 0);
    $inq   = trim($_GET['u'] ?? '');
    $after = (int)($_GET['after'] ?? 0);
    if ($id_p > 0 && !empty($inq)) {
        $stmtV = $_conexion->prepare("SELECT 1 FROM PLAZA WHERE ID_plaza=? AND DNI=?");
        $stmtV->bind_param('is', $id_p, $dni); $stmtV->execute();
        if ($stmtV->get_result()->num_rows > 0) {
            $stmtV->close();
            $stmtJ = $_conexion->prepare(
                "SELECT ID_mensaje, DNI_emisor, Contenido, Fecha FROM MENSAJE
                 WHERE ID_plaza=? AND DNI_inquilino=? AND ID_mensaje>? ORDER BY Fecha ASC"
            );
            $stmtJ->bind_param('isi', $id_p, $inq, $after);
            $stmtJ->execute();
            echo json_encode(['ok'=>true,'msgs'=>$stmtJ->get_result()->fetch_all(MYSQLI_ASSOC)],JSON_UNESCAPED_UNICODE);
            $stmtJ->close();
        } else { $stmtV->close(); echo json_encode(['ok'=>false]); }
    } else { echo json_encode(['ok'=>false]); }
    exit;
}

// ── POST: propietario responde en vista de conversación ──────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_owner_reply'])) {
    $id_pl_r  = (int)($_POST['_id_plaza'] ?? 0);
    $dni_inq  = trim($_POST['_dni_inq'] ?? '');
    $contReply = trim($_POST['_owner_reply'] ?? '');
    if ($contReply !== '' && mb_strlen($contReply) <= 1000 && $id_pl_r > 0 && !empty($dni_inq)) {
        $stmtR = $_conexion->prepare(
            "INSERT INTO MENSAJE (ID_plaza, DNI_inquilino, DNI_emisor, Contenido) VALUES (?,?,?,?)"
        );
        $stmtR->bind_param('isss', $id_pl_r, $dni_inq, $dni, $contReply);
        $stmtR->execute();
        $stmtR->close();
        $helperPath = __DIR__ . '/../notificaciones/notificaciones_helper.php';
        if (file_exists($helperPath)) {
            require_once $helperPath;
            $stmtDir = $_conexion->prepare("SELECT Direccion FROM PLAZA WHERE ID_plaza = ?");
            $stmtDir->bind_param('i', $id_pl_r);
            $stmtDir->execute();
            $dirRow = $stmtDir->get_result()->fetch_assoc();
            $stmtDir->close();
            crearNotificacion($_conexion, $dni_inq, 'nueva_consulta',
                'Respuesta del propietario',
                'El propietario respondió sobre la plaza en ' . ($dirRow['Direccion'] ?? 'tu plaza') . '.',
                $id_pl_r);
        }
    }
    header('Location: mis_plazas.php?tab=chats&id_plaza=' . $id_pl_r . '&u=' . urlencode($dni_inq));
    exit;
}

// ── Datos para tab=chats ─────────────────────────────────────────────────────
$tab           = $_GET['tab'] ?? '';
$tab_conv_plaza = (int)($_GET['id_plaza'] ?? 0);
$tab_conv_inq   = trim($_GET['u'] ?? '');
$tab_conv       = ($tab === 'chats' && $tab_conv_plaza > 0 && !empty($tab_conv_inq));
$consultas_list = []; $mis_consultas = []; $tab_mensajes = []; $tab_nombre_inq = ''; $tab_dir = '';

if ($tab === 'chats') {
    if ($tab_conv) {
        // Verify plaza belongs to owner
        $stmtA = $_conexion->prepare("SELECT Direccion FROM PLAZA WHERE ID_plaza = ? AND DNI = ?");
        $stmtA->bind_param('is', $tab_conv_plaza, $dni);
        $stmtA->execute();
        $aRow = $stmtA->get_result()->fetch_assoc();
        $stmtA->close();
        if (!$aRow) { header('Location: mis_plazas.php?tab=chats'); exit; }
        $tab_dir = $aRow['Direccion'];
        // Tenant name
        $stmtU = $_conexion->prepare("SELECT Nombre, Apellidos FROM USUARIO WHERE DNI = ?");
        $stmtU->bind_param('s', $tab_conv_inq);
        $stmtU->execute();
        $uRow = $stmtU->get_result()->fetch_assoc();
        $stmtU->close();
        if ($uRow) $tab_nombre_inq = $uRow['Nombre'] . ' ' . mb_substr($uRow['Apellidos'], 0, 1) . '.';
        // Messages
        $stmtM = $_conexion->prepare(
            "SELECT ID_mensaje, DNI_emisor, Contenido, Fecha FROM MENSAJE
             WHERE ID_plaza = ? AND DNI_inquilino = ? ORDER BY Fecha ASC"
        );
        $stmtM->bind_param('is', $tab_conv_plaza, $tab_conv_inq);
        $stmtM->execute();
        $tab_mensajes = $stmtM->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmtM->close();
        // Mark as read
        $stmtLR = $_conexion->prepare("UPDATE MENSAJE SET Leido=1 WHERE ID_plaza=? AND DNI_inquilino=? AND DNI_emisor!=? AND Leido=0");
        $stmtLR->bind_param('iss', $tab_conv_plaza, $tab_conv_inq, $dni);
        $stmtLR->execute(); $stmtLR->close();
    } else {
        // Conversation list
        $sqlCL = "SELECT m.ID_plaza, m.DNI_inquilino, p.Direccion,
                         u.Nombre AS nombre_inq, u.Apellidos AS apellidos_inq,
                         MAX(m.Fecha) AS ultima_fecha,
                         COUNT(CASE WHEN m.DNI_emisor != ? AND m.Leido = 0 THEN 1 END) AS no_leidos,
                         (SELECT Contenido FROM MENSAJE WHERE ID_plaza=m.ID_plaza AND DNI_inquilino=m.DNI_inquilino ORDER BY Fecha DESC LIMIT 1) AS ultimo_msg
                  FROM MENSAJE m JOIN PLAZA p ON m.ID_plaza=p.ID_plaza JOIN USUARIO u ON m.DNI_inquilino=u.DNI
                  WHERE p.DNI = ? AND m.ID_plaza IS NOT NULL
                  GROUP BY m.ID_plaza, m.DNI_inquilino ORDER BY ultima_fecha DESC";
        $stmtCL = $_conexion->prepare($sqlCL);
        $stmtCL->bind_param('ss', $dni, $dni);
        $stmtCL->execute();
        $consultas_list = $stmtCL->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmtCL->close();
        // Conversaciones donde el usuario actual es el inquilino
        $sqlMC = "SELECT m.ID_plaza, p.Direccion, u.Nombre AS nombre_prop, u.Apellidos AS apellidos_prop,
                         MAX(m.Fecha) AS ultima_fecha,
                         COUNT(CASE WHEN m.DNI_emisor != ? AND m.Leido=0 THEN 1 END) AS no_leidos,
                         (SELECT Contenido FROM MENSAJE WHERE ID_plaza=m.ID_plaza AND DNI_inquilino=? ORDER BY Fecha DESC LIMIT 1) AS ultimo_msg
                  FROM MENSAJE m JOIN PLAZA p ON m.ID_plaza=p.ID_plaza JOIN USUARIO u ON p.DNI=u.DNI
                  WHERE m.DNI_inquilino=? AND m.ID_plaza IS NOT NULL
                  GROUP BY m.ID_plaza, p.Direccion, u.Nombre, u.Apellidos ORDER BY ultima_fecha DESC";
        $stmtMC = $_conexion->prepare($sqlMC);
        $stmtMC->bind_param('sss', $dni, $dni, $dni);
        $stmtMC->execute();
        $mis_consultas = $stmtMC->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmtMC->close();
    }
}

// Mensajes no leídos en reservas de las plazas del propietario
$stmtUnread = $_conexion->prepare(
    "SELECT COUNT(*) AS total FROM MENSAJE m
     JOIN RESERVA r ON m.ID_reserva = r.ID_reserva
     JOIN PLAZA p ON r.ID_plaza = p.ID_plaza
     WHERE p.DNI = ? AND m.DNI_emisor != ? AND m.Leido = 0"
);
$stmtUnread->bind_param('ss', $dni, $dni);
$stmtUnread->execute();
$totalNoLeidos = (int)$stmtUnread->get_result()->fetch_assoc()['total'];
$stmtUnread->close();

// Consultas no leídas sobre las plazas (sin reserva)
$stmtConsultas = $_conexion->prepare(
    "SELECT COUNT(*) AS total FROM MENSAJE m
     JOIN PLAZA p ON m.ID_plaza = p.ID_plaza
     WHERE p.DNI = ? AND m.DNI_emisor != ? AND m.Leido = 0 AND m.ID_plaza IS NOT NULL"
);
$stmtConsultas->bind_param('ss', $dni, $dni);
$stmtConsultas->execute();
$totalConsultas = (int)$stmtConsultas->get_result()->fetch_assoc()['total'];
$stmtConsultas->close();

$sql = "SELECT * FROM PLAZA WHERE DNI = ?";

$stmt = $_conexion->prepare($sql);

if (!$stmt) {
    die("Error SQL: " . $_conexion->error);
}

$stmt->bind_param("s", $dni);
$stmt->execute();

$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis plazas</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>


    <link rel="stylesheet" href="../app.css">
    <link rel="stylesheet" href="../styles/mis_plazas.css">
</head>
<body class="my-plazas-page">

<div class="layout">
    <div class="layout-container">
        
        <header class="page-header">
            <a href="../index.php" class="back-link"><i data-lucide="arrow-left"></i> Volver al mapa</a>
            <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
                <div>
                    <h1 class="headline">Mis plazas publicadas</h1>
                    <p class="support">Gestiona los anuncios de tus plazas de aparcamiento o añade nuevas.</p>
                </div>
                <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                    <a href="reservas_propietario.php" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.6rem 1.1rem;background:var(--brand-dark);color:var(--brand-yellow);border-radius:999px;text-decoration:none;font-size:0.85rem;font-weight:700;white-space:nowrap;position:relative;flex-shrink:0;">
                        <i data-lucide="message-circle" width="16" height="16"></i>
                        Reservas recibidas
                        <?php if ($totalNoLeidos > 0): ?>
                            <span style="position:absolute;top:-7px;right:-7px;background:#ef4444;color:#fff;border-radius:999px;font-size:0.65rem;font-weight:700;min-width:19px;height:19px;display:flex;align-items:center;justify-content:center;padding:0 4px;">
                                <?php echo $totalNoLeidos; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <a href="mis_plazas.php?tab=chats" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.6rem 1.1rem;background:var(--brand-dark);color:var(--brand-yellow);border-radius:999px;text-decoration:none;font-size:0.85rem;font-weight:700;white-space:nowrap;position:relative;flex-shrink:0;">
                        <i data-lucide="message-circle" width="16" height="16"></i>
                        Consultas
                        <?php if ($totalConsultas > 0): ?>
                            <span style="position:absolute;top:-7px;right:-7px;background:#ef4444;color:#fff;border-radius:999px;font-size:0.65rem;font-weight:700;min-width:19px;height:19px;display:flex;align-items:center;justify-content:center;padding:0 4px;">
                                <?php echo $totalConsultas; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </header>

        <?php if ($tab === 'chats'): ?>
            <?php if ($tab_conv): ?>
                <!-- ── Conversación individual ── -->
                <div style="margin-bottom:1rem;">
                    <a href="mis_plazas.php?tab=chats" style="display:inline-flex;align-items:center;gap:.4rem;font-size:.85rem;color:var(--brand-dark);text-decoration:none;font-weight:600;">
                        <i data-lucide="arrow-left" width="15" height="15"></i> Volver a consultas
                    </a>
                </div>
                <div style="background:#fff;border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,.06);display:flex;flex-direction:column;height:70dvh;max-height:600px;overflow:hidden;">
                    <div style="display:flex;align-items:center;gap:.75rem;padding:.875rem 1rem;border-bottom:1px solid #e5e7eb;flex-shrink:0;background:#fff;">
                        <div style="width:38px;height:38px;border-radius:50%;background:var(--brand-yellow);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.9rem;color:var(--brand-dark);flex-shrink:0;"><?php echo mb_strtoupper(mb_substr($tab_nombre_inq, 0, 1)); ?></div>
                        <div style="flex:1;min-width:0;">
                            <p style="margin:0;font-size:.9rem;font-weight:700;color:var(--brand-dark);"><?php echo htmlspecialchars($tab_nombre_inq); ?></p>
                            <p style="margin:0;font-size:.72rem;color:#888;">Interesado en tu plaza</p>
                        </div>
                    </div>
                    <div style="background:#fafaf5;border-bottom:1px solid #e5e7eb;padding:.4rem 1rem;font-size:.72rem;color:#888;display:flex;align-items:center;gap:.4rem;flex-shrink:0;">
                        <i data-lucide="map-pin" width="11" height="11"></i> <?php echo htmlspecialchars($tab_dir); ?>
                    </div>
                    <div id="tabMsgs" style="flex:1;overflow-y:auto;padding:.75rem;display:flex;flex-direction:column;gap:.4rem;">
                        <?php if (empty($tab_mensajes)): ?>
                        <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#aaa;padding:2rem;text-align:center;">
                            <p style="font-size:.875rem;margin:0;">Aún no hay mensajes.</p>
                        </div>
                        <?php else: foreach ($tab_mensajes as $tm):
                            $mio = $tm['DNI_emisor'] === $dni;
                            $hm  = date('H:i', strtotime($tm['Fecha']));
                        ?>
                        <div style="display:flex;flex-direction:column;max-width:78%;<?php echo $mio?'align-self:flex-end;align-items:flex-end;':'align-self:flex-start;align-items:flex-start;'; ?>">
                            <div style="padding:.5rem .875rem;border-radius:18px;font-size:.875rem;line-height:1.45;word-break:break-word;<?php echo $mio?'background:#1a1915;color:#fff;border-bottom-right-radius:4px;':'background:#f4f4f0;color:#1a1915;border-bottom-left-radius:4px;border:1px solid #e5e7eb;'; ?>"><?php echo htmlspecialchars($tm['Contenido']); ?></div>
                            <div style="font-size:.65rem;color:#aaa;margin-top:.2rem;"><?php echo $hm; ?></div>
                        </div>
                        <?php endforeach; endif; ?>
                    </div>
                    <form method="POST" action="mis_plazas.php?tab=chats&id_plaza=<?php echo $tab_conv_plaza; ?>&u=<?php echo urlencode($tab_conv_inq); ?>"
                          style="display:flex;gap:.5rem;padding:.65rem .75rem calc(.65rem + env(safe-area-inset-bottom));border-top:1px solid #e5e7eb;flex-shrink:0;align-items:flex-end;">
                        <input type="hidden" name="_id_plaza" value="<?php echo $tab_conv_plaza; ?>">
                        <input type="hidden" name="_dni_inq"  value="<?php echo htmlspecialchars($tab_conv_inq); ?>">
                        <textarea name="_owner_reply" id="ownerInput" rows="1" maxlength="1000" placeholder="Escribe un mensaje…"
                            style="flex:1;border:1.5px solid #e5e7eb;border-radius:22px;padding:.5rem .875rem;font-family:inherit;font-size:.875rem;resize:none;min-height:38px;max-height:100px;overflow-y:auto;line-height:1.4;outline:none;"></textarea>
                        <button type="submit" style="width:38px;height:38px;border-radius:50%;background:#1a1915;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#f4dd49;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        </button>
                    </form>
                </div>
                <script>
                (function(){
                    var msgsEl = document.getElementById('tabMsgs');
                    msgsEl.scrollTop = 99999;
                    var lastId  = <?php echo !empty($tab_mensajes) ? (int)end($tab_mensajes)['ID_mensaje'] : 0; ?>;
                    var dniYo  = <?php echo json_encode($dni); ?>;
                    function esc(s){ return s?String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'):''; }
                    function fmtH(s){ return new Date(s.replace(' ','T')).toLocaleTimeString('es-ES',{hour:'2-digit',minute:'2-digit'}); }
                    function appendMsg(m){
                        var mio=(m.DNI_emisor===dniYo);
                        var w=document.createElement('div');
                        w.style.cssText='display:flex;flex-direction:column;max-width:78%;'+(mio?'align-self:flex-end;align-items:flex-end;':'align-self:flex-start;align-items:flex-start;');
                        w.innerHTML='<div style="padding:.5rem .875rem;border-radius:18px;font-size:.875rem;line-height:1.45;word-break:break-word;'+(mio?'background:#1a1915;color:#fff;border-bottom-right-radius:4px;':'background:#f4f4f0;color:#1a1915;border-bottom-left-radius:4px;border:1px solid #e5e7eb;')+'">'+esc(m.Contenido)+'</div><div style="font-size:.65rem;color:#aaa;margin-top:.2rem;">'+fmtH(m.Fecha)+'</div>';
                        msgsEl.appendChild(w); lastId=Math.max(lastId,parseInt(m.ID_mensaje)); msgsEl.scrollTop=msgsEl.scrollHeight;
                    }
                    setInterval(function(){
                        fetch('mis_plazas.php?tab=chats&id_plaza=<?php echo $tab_conv_plaza; ?>&u=<?php echo urlencode($tab_conv_inq); ?>&msgonly=1&after='+lastId,{credentials:'same-origin'})
                            .then(function(r){return r.json();}).then(function(d){if(d.ok&&d.msgs&&d.msgs.length)d.msgs.forEach(appendMsg);}).catch(function(){});
                    }, 5000);
                    var oi=document.getElementById('ownerInput');
                    oi.addEventListener('input',function(){ this.style.height='auto'; this.style.height=Math.min(this.scrollHeight,100)+'px'; });
                    oi.addEventListener('keydown',function(e){ if(e.key==='Enter'&&!e.shiftKey){ e.preventDefault(); if(this.value.trim()) this.closest('form').submit(); } });
                })();
                </script>
            <?php else: ?>
                <!-- ── Lista de consultas ── -->
                <?php if (!empty($mis_consultas)): ?>
                <h3 style="font-size:.85rem;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:.05em;margin:0 0 .6rem;">Mis consultas como interesado</h3>
                <div style="display:flex;flex-direction:column;gap:.5rem;margin-bottom:1.25rem;">
                    <?php foreach ($mis_consultas as $c):
                        $ini = mb_strtoupper(mb_substr($c['nombre_prop'], 0, 1));
                        $nom = htmlspecialchars($c['nombre_prop'] . ' ' . mb_substr($c['apellidos_prop'], 0, 1) . '.');
                    ?>
                    <a href="reserva.php?id_plaza=<?php echo (int)$c['ID_plaza']; ?>&contactar=1"
                       style="background:#fff;border-radius:14px;padding:.875rem 1rem;box-shadow:0 2px 10px rgba(0,0,0,.06);display:flex;align-items:center;gap:.75rem;text-decoration:none;color:inherit;">
                        <div style="width:40px;height:40px;border-radius:50%;background:#e8e8e4;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.95rem;color:var(--brand-dark);flex-shrink:0;"><?php echo $ini; ?></div>
                        <div style="flex:1;min-width:0;">
                            <p style="margin:0 0 .15rem;font-weight:700;font-size:.875rem;color:var(--brand-dark);"><?php echo $nom; ?></p>
                            <p style="margin:0 0 .2rem;font-size:.72rem;color:#888;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><i data-lucide="map-pin" width="10" height="10"></i> <?php echo htmlspecialchars($c['Direccion']); ?></p>
                            <p style="margin:0;font-size:.78rem;color:#555;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars(mb_substr($c['ultimo_msg'] ?? '', 0, 80)); ?></p>
                        </div>
                        <?php if ($c['no_leidos'] > 0): ?>
                        <div style="background:#ef4444;color:#fff;border-radius:999px;font-size:.65rem;font-weight:700;min-width:20px;height:20px;display:flex;align-items:center;justify-content:center;padding:0 5px;flex-shrink:0;"><?php echo (int)$c['no_leidos']; ?></div>
                        <?php endif; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($consultas_list)): ?>
                <h3 style="font-size:.85rem;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:.05em;margin:0 0 .6rem;">Consultas sobre mis plazas</h3>
                <div style="display:flex;flex-direction:column;gap:.5rem;">
                    <?php foreach ($consultas_list as $c):
                        $ini = mb_strtoupper(mb_substr($c['nombre_inq'], 0, 1));
                        $nom = htmlspecialchars($c['nombre_inq'] . ' ' . mb_substr($c['apellidos_inq'], 0, 1) . '.');
                    ?>
                    <a href="mis_plazas.php?tab=chats&id_plaza=<?php echo (int)$c['ID_plaza']; ?>&u=<?php echo urlencode($c['DNI_inquilino']); ?>"
                       style="background:#fff;border-radius:14px;padding:.875rem 1rem;box-shadow:0 2px 10px rgba(0,0,0,.06);display:flex;align-items:center;gap:.75rem;text-decoration:none;color:inherit;">
                        <div style="width:40px;height:40px;border-radius:50%;background:var(--brand-yellow);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.95rem;color:var(--brand-dark);flex-shrink:0;"><?php echo $ini; ?></div>
                        <div style="flex:1;min-width:0;">
                            <p style="margin:0 0 .15rem;font-weight:700;font-size:.875rem;color:var(--brand-dark);"><?php echo $nom; ?></p>
                            <p style="margin:0 0 .2rem;font-size:.72rem;color:#888;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><i data-lucide="map-pin" width="10" height="10"></i> <?php echo htmlspecialchars($c['Direccion']); ?></p>
                            <p style="margin:0;font-size:.78rem;color:#555;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars(mb_substr($c['ultimo_msg'] ?? '', 0, 80)); ?></p>
                        </div>
                        <?php if ($c['no_leidos'] > 0): ?>
                        <div style="background:#ef4444;color:#fff;border-radius:999px;font-size:.65rem;font-weight:700;min-width:20px;height:20px;display:flex;align-items:center;justify-content:center;padding:0 5px;flex-shrink:0;"><?php echo (int)$c['no_leidos']; ?></div>
                        <?php endif; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if (empty($mis_consultas) && empty($consultas_list)): ?>
                <div class="empty-state"><i data-lucide="message-circle"></i><p>Aún no tienes mensajes.</p></div>
                <?php endif; ?>
            <?php endif; ?>

        <?php else: ?>

        <?php if (isset($_GET['updated'])): ?>
            <div style="background:#f4dd49;padding:0.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-weight:500;">Plaza actualizada correctamente.</div>
        <?php endif; ?>

        <?php if ($result->num_rows === 0): ?>
            <div class="empty-state">
                <i data-lucide="parking-circle"></i>
                <p>Aún no has publicado ninguna plaza.</p>
                <a href="../parking/alta_plaza.php" class="btn-primary-link">Publicar mi primera plaza</a>
            </div>
        <?php else: ?>

        <div class="plazas-grid">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="card plaza-card">
                    <div class="plaza-image-wrap">
                        <?php if (!empty($row['Foto'])): ?>
                            <img src="<?php echo htmlspecialchars($row['Foto']); ?>" alt="Foto de la plaza">
                        <?php else: ?>
                            <div class="no-image"><i data-lucide="image"></i></div>
                        <?php endif; ?>
                    </div>

                    <div class="plaza-content">
                        <h3>Plaza #<?php echo $row['ID_plaza']; ?></h3>
                        
                        <div class="info-row">
                            <i data-lucide="map-pin"></i>
                            <div>
                                <span>Dirección</span>
                                <strong><?php echo htmlspecialchars($row['Direccion']); ?></strong>
                            </div>
                        </div>

                        <div class="info-row">
                            <i data-lucide="banknote"></i>
                            <div>
                                <span>Precio</span>
                                <strong class="price-text"><?php echo number_format($row['Precio'], 2); ?> € /h</strong>
                            </div>
                        </div>

                        <div class="info-row">
                            <i data-lucide="maximize"></i>
                            <div>
                                <span>Medidas</span>
                                <strong>
                                    <?php if ($row['Ancho'] && $row['Largo']): ?>
                                        <?php echo $row['Ancho']; ?>m &times; <?php echo $row['Largo']; ?>m
                                    <?php else: ?>
                                        No especificado
                                    <?php endif; ?>
                                </strong>
                            </div>
                        </div>

                        <?php if(!empty($row['Descripcion'])): ?>
                        <div class="plaza-description">
                            <?php echo htmlspecialchars($row['Descripcion']); ?>
                        </div>
                        <?php endif; ?>

                        <div class="plaza-footer">
                            <a href="editar_plaza.php?id_plaza=<?php echo $row['ID_plaza']; ?>" class="btn-edit">
                                <i data-lucide="pencil"></i> Editar
                            </a>
                            <form method="POST" action="eliminar_plaza.php"
                                  onsubmit="return confirm('¿Estás seguro de que deseas eliminar este anuncio?');">
                                <input type="hidden" name="id_plaza" value="<?php echo $row['ID_plaza']; ?>">
                                <button type="submit" class="btn-delete">
                                    <i data-lucide="trash-2"></i> Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <?php endif; ?>

        <?php endif; // end tab !== chats ?>
    </div>
</div>

<script>
    lucide.createIcons();
</script>

</body>
</html>