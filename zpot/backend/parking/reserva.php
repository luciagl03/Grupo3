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

$id_plaza = isset($_GET['id_plaza']) ? (int) $_GET['id_plaza'] : 0;
$dni = $_SESSION['dni'] ?? '';

$sql = "SELECT Precio, Direccion FROM PLAZA WHERE ID_plaza = ?";
$stmt = $_conexion->prepare($sql);

if (!$stmt) {
    die($_conexion->error);
}

$stmt->bind_param("i", $id_plaza);
$stmt->execute();
$result = $stmt->get_result();

$precio_hora = 0;
$direccion = '';

if ($row = $result->fetch_assoc()) {
    $precio_hora = $row['Precio'];
    $direccion = $row['Direccion'] ?? '';
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
                    <span class="summary-label">Plaza seleccionada</span>
                    <strong class="summary-address"><?php echo !empty($direccion) ? htmlspecialchars($direccion) : 'Plaza #' . $id_plaza; ?></strong>
                </div>
            </div>

            <form method="POST" action="pago.php" class="booking-form">
                <input type="hidden" name="id_plaza" value="<?php echo $id_plaza; ?>">
                <input type="hidden" name="dni" value="<?php echo htmlspecialchars($dni); ?>">

                <div class="form-grid">
                    <?php
                        $ahora = date('Y-m-d\TH:i');
                        $maxFecha = date('Y-m-d\TH:i', strtotime('+1 year'));
                    ?>
                    <div class="form-group">
                        <label><i data-lucide="calendar-input"></i> Hora de entrada</label>
                        <input type="datetime-local" name="hora_entrada" required min="<?php echo $ahora; ?>" max="<?php echo $maxFecha; ?>">
                    </div>

                    <div class="form-group">
                        <label><i data-lucide="calendar-output"></i> Hora de salida</label>
                        <input type="datetime-local" name="hora_salida" required min="<?php echo $ahora; ?>" max="<?php echo $maxFecha; ?>">
                    </div>

                    <div class="form-group full-width">
                        <label><i data-lucide="banknote"></i> Precio estimado</label>
                        <input type="text" id="precioEstimado" value="<?php echo number_format($precio_hora, 2); ?> €/h" disabled>
                        <span class="helper-text">Se actualiza al seleccionar las horas.</span>
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

    (function () {
        var form = document.querySelector('.booking-form');
        var inputEntrada = document.querySelector('[name="hora_entrada"]');
        var inputSalida  = document.querySelector('[name="hora_salida"]');
        var errorBox = document.createElement('p');
        errorBox.style.cssText = 'color:#c0392b;font-size:0.875rem;margin:0.5rem 0;display:none;';
        form.querySelector('.form-actions').before(errorBox);

        form.addEventListener('submit', function (e) {
            var ahora = new Date();
            var entrada = new Date(inputEntrada.value);
            var salida  = new Date(inputSalida.value);
            errorBox.style.display = 'none';

            if (!inputEntrada.value || !inputSalida.value) {
                e.preventDefault();
                errorBox.textContent = 'Debes indicar fecha de entrada y salida.';
                errorBox.style.display = 'block';
                return;
            }
            if (entrada < ahora) {
                e.preventDefault();
                errorBox.textContent = 'La fecha de entrada no puede ser en el pasado.';
                errorBox.style.display = 'block';
                return;
            }
            if (salida <= entrada) {
                e.preventDefault();
                errorBox.textContent = 'La fecha de salida debe ser posterior a la de entrada.';
                errorBox.style.display = 'block';
                return;
            }
        });

        var precioPorHora = <?php echo (float) $precio_hora; ?>;
        var estimadoEl = document.getElementById('precioEstimado');

        function actualizarPrecio() {
            if (!inputEntrada.value || !inputSalida.value) return;
            var entrada = new Date(inputEntrada.value);
            var salida  = new Date(inputSalida.value);
            if (salida <= entrada) return;
            var horas = Math.max(1, Math.ceil((salida - entrada) / 3600000));
            estimadoEl.value = (horas * precioPorHora).toFixed(2) + ' € (' + horas + 'h × ' + precioPorHora.toFixed(2) + ' €/h)';
        }

        inputEntrada.addEventListener('change', function () {
            if (inputEntrada.value && (!inputSalida.value || new Date(inputSalida.value) <= new Date(inputEntrada.value))) {
                var min = new Date(inputEntrada.value);
                min.setHours(min.getHours() + 1);
                inputSalida.min = min.toISOString().slice(0, 16);
            }
            actualizarPrecio();
        });
        inputSalida.addEventListener('change', actualizarPrecio);
    })();
</script>
</body>
</html>
