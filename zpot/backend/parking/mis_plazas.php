<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: sesion/login.php');
    exit;
}

require_once '../sesion/conexion.php';

$dni = $_SESSION['dni'];

// Mensajes no leídos en reservas CONFIRMADAS de las plazas del propietario
$stmtUnread = $_conexion->prepare(
    "SELECT COUNT(*) AS total FROM MENSAJE m
     JOIN RESERVA r ON m.ID_reserva = r.ID_reserva
     JOIN PLAZA p ON r.ID_plaza = p.ID_plaza
     WHERE p.DNI = ? AND m.DNI_emisor != ? AND m.Leido = 0 AND r.Estado = 'confirmada'"
);
$stmtUnread->bind_param('ss', $dni, $dni);
$stmtUnread->execute();
$totalNoLeidos = (int)$stmtUnread->get_result()->fetch_assoc()['total'];
$stmtUnread->close();

// Notificaciones no leídas de nuevas reservas recibidas
$stmtNuevasReservas = $_conexion->prepare(
    "SELECT COUNT(*) AS total FROM NOTIFICACION
     WHERE DNI = ? AND Tipo = 'nueva_reserva_propietario' AND Leida = 0"
);
$stmtNuevasReservas->bind_param('s', $dni);
$stmtNuevasReservas->execute();
$nuevasReservasNoLeidas = (int)$stmtNuevasReservas->get_result()->fetch_assoc()['total'];
$stmtNuevasReservas->close();

// Total de alertas (mensajes + nuevas reservas)
$totalAlertas = $totalNoLeidos + $nuevasReservasNoLeidas;

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
    <script src="../dark-mode.js"></script>
    <script src="../translations.js"></script>
</head>
<body class="my-plazas-page">

<div class="layout">
    <div class="layout-container">
        
        <header class="page-header">
            <a href="../index.php" class="back-link"><i data-lucide="arrow-left"></i> <span data-i18n="backToMap">Volver al mapa</span></a>
            <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
                <div>
                    <h1 class="headline" data-i18n="mySpotsTitle">Mis plazas publicadas</h1>
                    <p class="support" data-i18n="mySpotsSubtitle">Gestiona los anuncios de tus plazas de aparcamiento o añade nuevas.</p>
                </div>
                <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                    <a href="reservas_propietario.php" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.6rem 1.1rem;background:var(--brand-yellow);color:#1a1915;border-radius:999px;text-decoration:none;font-size:0.85rem;font-weight:700;white-space:nowrap;position:relative;flex-shrink:0;">
                        <i data-lucide="calendar-check" width="16" height="16"></i>
                        <span data-i18n="receivedReservations">Reservas recibidas</span>
                        <?php if ($totalAlertas > 0): ?>
                            <span style="position:absolute;top:-7px;right:-7px;background:#ef4444;color:#fff;border-radius:999px;font-size:0.65rem;font-weight:700;min-width:19px;height:19px;display:flex;align-items:center;justify-content:center;padding:0 4px;">
                                <?php echo $totalAlertas; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </header>

        <?php if (isset($_GET['updated'])): ?>
            <div style="background:#f4dd49;padding:0.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-weight:500;" data-i18n="spotUpdatedSuccess">Plaza actualizada correctamente.</div>
        <?php endif; ?>

        <?php if ($result->num_rows === 0): ?>
            <div class="empty-state">
                <i data-lucide="parking-circle"></i>
                <p data-i18n="noSpotsYet">Aún no has publicado ninguna plaza.</p>
                <a href="../parking/alta_plaza.php" class="btn-primary-link" data-i18n="publishFirstSpot">Publicar mi primera plaza</a>
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
                                <span data-i18n="addressLabel">Dirección</span>
                                <strong><?php echo htmlspecialchars($row['Direccion']); ?></strong>
                            </div>
                        </div>

                        <div class="info-row">
                            <i data-lucide="banknote"></i>
                            <div>
                                <span data-i18n="priceLabel">Precio</span>
                                <strong class="price-text"><?php echo number_format($row['Precio'], 2); ?> € /h</strong>
                            </div>
                        </div>

                        <div class="info-row">
                            <i data-lucide="maximize"></i>
                            <div>
                                <span data-i18n="measurementsLabel">Medidas</span>
                                <strong>
                                    <?php if ($row['Ancho'] && $row['Largo']): ?>
                                        <?php echo $row['Ancho']; ?>m &times; <?php echo $row['Largo']; ?>m
                                    <?php else: ?>
                                        <span data-i18n="notSpecified">No especificado</span>
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
                                <i data-lucide="pencil"></i> <span data-i18n="editButton">Editar</span>
                            </a>
                            <form method="POST" action="eliminar_plaza.php"
                                  onsubmit="return confirmDeleteSpot();">
                                <input type="hidden" name="id_plaza" value="<?php echo $row['ID_plaza']; ?>">
                                <button type="submit" class="btn-delete">
                                    <i data-lucide="trash-2"></i> <span data-i18n="deleteButton">Eliminar</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <?php endif; ?>
    </div>
</div>

<script>
    lucide.createIcons();
    
    // Función para confirmar eliminación con traducción
    function confirmDeleteSpot() {
        const currentLang = getCurrentLanguage();
        const message = t('confirmDeleteSpot', currentLang);
        return confirm(message);
    }
</script>

</body>
</html>
