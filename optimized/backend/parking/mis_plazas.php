<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: sesion/login.php');
    exit;
}

require_once 'sesion/conexion.php';

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
    <link rel="stylesheet" href="app.css">
</head>
<body>

<a href="index.php" class="back-link">← Volver</a>

<h1>Mis plazas</h1>

<?php if ($result->num_rows === 0): ?>
    <p>No tienes plazas publicadas.</p>
<?php else: ?>

<div class="reservas-container">

    <?php while ($row = $result->fetch_assoc()): ?>

        <div class="reserva-card">

            <h3>Plaza #<?php echo $row['ID_plaza']; ?></h3>

            <p><strong>Dirección:</strong> <?php echo $row['Direccion']; ?></p>
            <p><strong>Precio:</strong> <?php echo number_format($row['Precio'], 2); ?> € /h</p>
            <p><strong>Descripción:</strong> <?php echo $row['Descripcion']; ?></p>
            <p><strong>Medidas:</strong> <?php echo $row['Ancho']; ?> x <?php echo $row['Largo']; ?></p>

            <?php if (!empty($row['Foto'])): ?>
                <img src="<?php echo $row['Foto']; ?>" style="width:100%; border-radius:10px;">
            <?php endif; ?>

            <!-- ELIMINAR -->
            <form method="POST" action="eliminar_plaza.php"
                  onsubmit="return confirm('¿Seguro que quieres eliminar esta plaza?');">

                <input type="hidden" name="id_plaza" value="<?php echo $row['ID_plaza']; ?>">

                <button type="submit">Eliminar plaza</button>

            </form>

        </div>

    <?php endwhile; ?>

</div>

<?php endif; ?>

</body>
</html>