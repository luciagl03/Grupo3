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

$sql = "SELECT * FROM RESERVA WHERE DNI = ? ORDER BY Fecha DESC, Hora_entrada DESC";

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
    <title>Mis reservas</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>

    <link rel="stylesheet" href="../app.css">
    <link rel="stylesheet" href="../styles/mis_reservas.css">
    
</head>
<body class="my-reservations-page">

<div class="layout">
    <div class="layout-container">
        
        <header class="page-header">
            <a href="../index.php" class="back-link"><i data-lucide="arrow-left"></i> Volver al mapa</a>
            <h1 class="headline">Mis reservas</h1>
            <p class="support">Gestiona tus próximas estancias y revisa tu historial.</p>
        </header>

        <?php if ($result->num_rows === 0): ?>
            <div class="empty-state">
                <i data-lucide="calendar-off"></i>
                <p>Aún no has realizado ninguna reserva.</p>
                <a href="../app.html" class="btn-primary-link">Buscar una plaza</a>
            </div>
        <?php else: ?>

        <div class="reservas-grid">
            <?php while ($row = $result->fetch_assoc()): ?>
                
                <div class="card reserva-card">
                    <div class="reserva-header">
                        <div class="plaza-tag">Plaza #<?php echo $row['ID_plaza']; ?></div>
                        <div class="price-tag"><?php echo number_format($row['Precio'], 2); ?> €</div>
                    </div>

                    <div class="reserva-body">
                        <div class="info-row">
                            <i data-lucide="calendar"></i>
                            <div>
                                <span>Fecha</span>
                                <strong><?php echo date("d/m/Y", strtotime($row['Fecha'])); ?></strong>
                            </div>
                        </div>

                        <div class="info-row">
                            <i data-lucide="clock"></i>
                            <div>
                                <span>Horario</span>
                                <strong><?php echo substr($row['Hora_entrada'], 0, 5); ?> - <?php echo substr($row['Hora_salida'], 0, 5); ?></strong>
                            </div>
                        </div>

                        <div class="info-row">
                            <i data-lucide="timer"></i>
                            <div>
                                <span>Duración</span>
                                <strong><?php echo $row['Duracion']; ?> horas</strong>
                            </div>
                        </div>
                    </div>

                    <div class="reserva-footer">
                        <form method="POST" action="eliminar_reserva.php" 
                              onsubmit="return confirm('¿Seguro que quieres cancelar esta reserva?');">
                            <input type="hidden" name="id_reserva" value="<?php echo $row['ID_reserva']; ?>">
                            <button type="submit" class="btn-cancel">
                                <i data-lucide="trash-2"></i> Cancelar reserva
                            </button>
                        </form>
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