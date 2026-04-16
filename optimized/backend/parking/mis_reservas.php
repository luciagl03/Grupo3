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
    <link rel="stylesheet" href="app.css">
</head>
<body>

<a href="index.php" class="back-link"><i data-lucide="arrow-left"></i> Volver al mapa</a>
<h1>Mis reservas</h1>

<?php if ($result->num_rows === 0): ?>
    <p>No tienes reservas todavía.</p>
<?php else: ?>

    <div class="reservas-container">
        <?php while ($row = $result->fetch_assoc()): ?>
            
            <div class="reserva-card">
                <h3>Plaza #<?php echo $row['ID_plaza']; ?></h3>
                
                <p><strong>Fecha:</strong> <?php echo $row['Fecha']; ?></p>
                <p><strong>Entrada:</strong> <?php echo $row['Hora_entrada']; ?></p>
                <p><strong>Salida:</strong> <?php echo $row['Hora_salida']; ?></p>
                <p><strong>Duración:</strong> <?php echo $row['Duracion']; ?> h</p>
                <p><strong>Total:</strong> <?php echo number_format($row['Precio'], 2); ?> €</p>
                <form method="POST" action="eliminar_reserva.php" onsubmit="return confirm('¿Seguro que quieres eliminar esta reserva?');">
                    <input type="hidden" name="id_reserva" value="<?php echo $row['ID_reserva']; ?>">
                    <button type="submit">Eliminar</button>
                </form>

            </div>

        <?php endwhile; ?>
    </div>

<?php endif; ?>

</body>
</html>