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

require_once '../sesion//conexion.php';

$id_plaza = isset($_GET['id_plaza']) ? (int) $_GET['id_plaza'] : 0;
$dni = $_SESSION['usuario']['dni'] ?? '';

$sql = "SELECT Precio FROM PLAZA WHERE ID_plaza = ?";
$stmt = $_conexion->prepare($sql);

if (!$stmt) {
    die($_conexion->error);
}

$stmt->bind_param("i", $id_plaza);
$stmt->execute();
$result = $stmt->get_result();

$precio_hora = 0;

if ($row = $result->fetch_assoc()) {
    $precio_hora = $row['Precio'];
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
                    <span>Plaza seleccionada</span>
                    <strong>Identificador: #<?php echo $id_plaza; ?></strong>
                </div>
            </div>

            <form method="POST" action="pago.php" class="booking-form">
                <input type="hidden" name="id_plaza" value="<?php echo $id_plaza; ?>">
                <input type="hidden" name="dni" value="<?php echo htmlspecialchars($dni); ?>">

                <div class="form-grid">
                    <div class="form-group">
                        <label><i data-lucide="calendar-input"></i> Hora de entrada</label>
                        <input type="datetime-local" name="hora_entrada" required>
                    </div>

                    <div class="form-group">
                        <label><i data-lucide="calendar-output"></i> Hora de salida</label>
                        <input type="datetime-local" name="hora_salida" required>
                    </div>

                    <div class="form-group full-width">
                        <label><i data-lucide="banknote"></i> Precio total estimado (€)</label>
                        <div class="form-group full-width">
                            <label><i data-lucide="banknote"></i> Precio por hora (€)</label>
                            <input type="text" value="<?php echo number_format($precio_hora, 2); ?> €/h" disabled>
                        </div>
                        <span class="helper-text">El precio final se calculará al confirmar el pago.</span>
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

<script>
    lucide.createIcons();
</script>
</body>
</html>
