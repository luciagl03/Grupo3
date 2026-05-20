<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../sesion/login.php');
    exit;
}

require_once '../sesion/conexion.php';

$dni = $_SESSION['dni'];

// Consultas agrupadas por (plaza, inquilino), con último mensaje y no leídos
$sql = "SELECT m.ID_plaza, m.DNI_inquilino,
               p.Direccion,
               u.Nombre AS nombre_inquilino, u.Apellidos AS apellidos_inquilino,
               MAX(m.Fecha) AS ultima_fecha,
               COUNT(CASE WHEN m.DNI_emisor != ? AND m.Leido = 0 THEN 1 END) AS no_leidos,
               (SELECT Contenido FROM MENSAJE
                WHERE ID_plaza = m.ID_plaza AND DNI_inquilino = m.DNI_inquilino
                ORDER BY Fecha DESC LIMIT 1) AS ultimo_mensaje
        FROM MENSAJE m
        JOIN PLAZA p ON m.ID_plaza = p.ID_plaza
        JOIN USUARIO u ON m.DNI_inquilino = u.DNI
        WHERE p.DNI = ? AND m.ID_plaza IS NOT NULL
        GROUP BY m.ID_plaza, m.DNI_inquilino
        ORDER BY ultima_fecha DESC";

$stmt = $_conexion->prepare($sql);
$stmt->bind_param('ss', $dni, $dni);
$stmt->execute();
$consultas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultas — Zpot</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="../app.css">
    <link rel="stylesheet" href="../styles/mis_reservas.css">
    <style>
        .consulta-card {
            background:#fff; border-radius:16px; padding:1rem 1.25rem;
            box-shadow:0 2px 12px rgba(0,0,0,.06); display:flex;
            align-items:center; gap:1rem; text-decoration:none;
            color:inherit; transition:box-shadow .15s;
        }
        .consulta-card:hover { box-shadow:0 4px 20px rgba(0,0,0,.10); }
        .consulta-avatar {
            width:42px; height:42px; border-radius:50%;
            background:var(--brand-yellow); display:flex; align-items:center;
            justify-content:center; font-weight:700; font-size:1rem;
            color:var(--brand-dark); flex-shrink:0;
        }
        .consulta-info { flex:1; min-width:0; }
        .consulta-nombre { font-weight:700; font-size:0.9rem; color:var(--brand-dark); margin:0 0 .15rem; }
        .consulta-plaza  { font-size:0.75rem; color:var(--text-muted); margin:0 0 .25rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .consulta-preview { font-size:0.8rem; color:#555; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin:0; }
        .badge-unread {
            background:#ef4444; color:#fff; border-radius:999px;
            font-size:0.65rem; font-weight:700; min-width:20px; height:20px;
            display:flex; align-items:center; justify-content:center; padding:0 5px; flex-shrink:0;
        }
    </style>
</head>
<body class="my-reservations-page">

<div class="layout">
    <div class="layout-container">
        <header class="page-header">
            <a href="mis_plazas.php" class="back-link"><i data-lucide="arrow-left"></i> Volver a mis plazas</a>
            <h1 class="headline">Consultas sobre mis plazas</h1>
            <p class="support">Mensajes de usuarios interesados en tus plazas.</p>
        </header>

        <?php if (empty($consultas)): ?>
            <div class="empty-state">
                <i data-lucide="message-circle"></i>
                <p>Aún no hay consultas sobre tus plazas.</p>
            </div>
        <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:0.75rem;">
            <?php foreach ($consultas as $c):
                $inicial = mb_strtoupper(mb_substr($c['nombre_inquilino'], 0, 1));
                $nombre  = htmlspecialchars($c['nombre_inquilino'] . ' ' . mb_substr($c['apellidos_inquilino'], 0, 1) . '.');
            ?>
            <a href="contactar.php?id_plaza=<?php echo (int)$c['ID_plaza']; ?>&dni_inquilino=<?php echo urlencode($c['DNI_inquilino']); ?>"
               class="consulta-card">
                <div class="consulta-avatar"><?php echo $inicial; ?></div>
                <div class="consulta-info">
                    <p class="consulta-nombre"><?php echo $nombre; ?></p>
                    <p class="consulta-plaza"><i data-lucide="map-pin" width="11" height="11"></i> <?php echo htmlspecialchars($c['Direccion']); ?></p>
                    <p class="consulta-preview"><?php echo htmlspecialchars(mb_substr($c['ultimo_mensaje'] ?? '', 0, 80)); ?></p>
                </div>
                <?php if ($c['no_leidos'] > 0): ?>
                    <div class="badge-unread"><?php echo (int)$c['no_leidos']; ?></div>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
