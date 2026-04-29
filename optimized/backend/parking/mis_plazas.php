<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: sesion/login.php');
    exit;
}

require_once '../sesion/conexion.php';

$dni = $_SESSION['dni'];

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
    <title>Mis plazas</title>
    <link rel="stylesheet" href="../app.css">

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
            <h1 class="headline">Mis plazas publicadas</h1>
            <p class="support">Gestiona los anuncios de tus plazas de aparcamiento o añade nuevas.</p>
        </header>

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
                                <strong><?php echo $row['Ancho']; ?>m x <?php echo $row['Largo']; ?>m</strong>
                            </div>
                        </div>

                        <?php if(!empty($row['Descripcion'])): ?>
                        <div class="plaza-description">
                            <?php echo htmlspecialchars($row['Descripcion']); ?>
                        </div>
                        <?php endif; ?>

                        <div class="plaza-footer">
                            <form method="POST" action="/zpot/optimized/backend/parking/eliminar_plaza.php" 
                                  onsubmit="return confirm('¿Estás seguro de que deseas eliminar este anuncio?');">
                                <input type="hidden" name="id_plaza" value="<?php echo $row['ID_plaza']; ?>">
                                <button type="submit" class="btn-delete">
                                    <i data-lucide="trash-2"></i> Eliminar anuncio
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
</script>

</body>
</html>