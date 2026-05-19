 <?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../sesion/login.php');
    exit;
}

require_once '../sesion/conexion.php';

// ============================================================================
// DETECTAR SI ES RETOMAR PAGO O FLUJO NORMAL
// ============================================================================
$es_retomar_pago = isset($_GET['id_reserva']) && !empty($_GET['id_reserva']);

// Función para mostrar errores con estilo
function mostrarErrorReserva($titulo, $mensaje, $backUrl = '../index.php') {
    $t = htmlspecialchars($titulo);
    $m = htmlspecialchars($mensaje);
    $u = htmlspecialchars($backUrl);
    echo <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Error — Zpot</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../app.css">
    <style>
        body{display:flex;align-items:center;justify-content:center;min-height:100dvh;background:var(--brand-bg,#f4f4f0);font-family:"DM Sans",sans-serif;padding:1.5rem;margin:0;}
        .err-card{background:#fff;border-radius:20px;padding:2rem 1.75rem;max-width:400px;width:100%;box-shadow:0 4px 24px rgba(0,0,0,.08);text-align:center;}
        .err-icon{width:52px;height:52px;border-radius:50%;background:#fef2f2;display:flex;align-items:center;justify-content:center;margin:0 auto 1.1rem;}
        .err-title{font-size:1.1rem;font-weight:700;color:#1a1915;margin:0 0 .5rem;}
        .err-msg{font-size:.875rem;color:#666;line-height:1.5;margin:0 0 1.5rem;}
        .err-btn{display:inline-flex;align-items:center;gap:.4rem;padding:.65rem 1.25rem;background:#1a1915;color:#fff;border-radius:999px;text-decoration:none;font-size:.875rem;font-weight:600;transition:background .15s;}
        .err-btn:hover{background:#2a2920;}
    </style>
</head>
<body>
<div class="err-card">
    <div class="err-icon">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    </div>
    <p class="err-title">$t</p>
    <p class="err-msg">$m</p>
    <a href="$u" class="err-btn">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
        Volver
    </a>
</div>
</body>
</html>
HTML;
    exit;
}

$dni = $_SESSION['dni'] ?? '';

// ============================================================================
// FLUJO 1: RETOMAR PAGO (desde Mis Reservas)
// ============================================================================
if ($es_retomar_pago) {
    $id_reserva = (int) $_GET['id_reserva'];
    
    // Cargar reserva existente de la BD
    $sql = "SELECT r.*, p.Precio as PrecioPlaza, p.Direccion 
            FROM RESERVA r 
            JOIN PLAZA p ON r.ID_plaza = p.ID_plaza
            WHERE r.ID_reserva = ? AND r.DNI = ? AND r.Estado = 'pendiente'";
    
    $stmt = $_conexion->prepare($sql);
    $stmt->bind_param("is", $id_reserva, $dni);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        mostrarErrorReserva(
            'Reserva no encontrada',
            'No se encontró la reserva o ya ha sido pagada/cancelada.',
            'mis_reservas.php'
        );
    }
    
    $reserva = $result->fetch_assoc();
    
    // Extraer datos de la reserva existente
    $id_plaza = $reserva['ID_plaza'];
    $fecha = $reserva['Fecha'];
    $hora_entrada = $reserva['Hora_entrada'];
    $hora_salida = $reserva['Hora_salida'];
    $duracion = $reserva['Duracion'];
    $total = $reserva['Precio'];
    $precio_hora = $reserva['PrecioPlaza'];
    $direccion = $reserva['Direccion'];
    
    // Calcular comisión (para mostrar en el desglose)
    $precio_con_comision = round($precio_hora * 1.20, 2);
    $comision = round($precio_hora * 0.20, 2);
    
    // RE-VALIDAR DISPONIBILIDAD (por si otro usuario reservó mientras tanto)
    $sql_disponibilidad = "SELECT ID_reserva FROM RESERVA 
                           WHERE ID_plaza = ? 
                           AND Fecha = ?
                           AND Estado = 'confirmada'
                           AND ID_reserva != ?
                           AND (Hora_entrada < ? AND Hora_salida > ?)
                           LIMIT 1";
    
    $stmt_disp = $_conexion->prepare($sql_disponibilidad);
    $stmt_disp->bind_param("isiss", $id_plaza, $fecha, $id_reserva, $hora_salida, $hora_entrada);
    $stmt_disp->execute();
    $result_disp = $stmt_disp->get_result();
    
    if ($result_disp->num_rows > 0) {
        // Conflicto: otro usuario reservó en el mismo horario
        // Eliminar la reserva pendiente obsoleta
        $stmt_delete = $_conexion->prepare("DELETE FROM RESERVA WHERE ID_reserva = ? AND DNI = ?");
        $stmt_delete->bind_param("is", $id_reserva, $dni);
        $stmt_delete->execute();
        $stmt_delete->close();
        
        mostrarErrorReserva(
            'Plaza ya no disponible',
            'Lo sentimos, esta plaza ya fue reservada por otro usuario en ese horario. Tu reserva pendiente ha sido cancelada automáticamente.',
            'mis_reservas.php'
        );
    }
    
    // Actualizar sesión
    $_SESSION['id_reserva_actual'] = $id_reserva;
    
    // NO crear notificación (ya existe de cuando se creó la reserva)

// ============================================================================
// FLUJO 2: FLUJO NORMAL (desde formulario de reserva.php)
// ============================================================================
} else {
    // DATOS
    $id_plaza = (int) $_POST['id_plaza'];
    $fecha_inicio = $_POST['hora_entrada'];
    $fecha_fin = $_POST['hora_salida'];

    // VALIDAR FECHAS
    if (empty($fecha_inicio) || empty($fecha_fin)) {
        mostrarErrorReserva('Faltan datos', 'Debes indicar la fecha de entrada y salida para continuar.');
    }
    // Margen de 5 minutos (igual que la validación JS)
    if (strtotime($fecha_inicio) < (time() - 5 * 60)) {
        mostrarErrorReserva('Hora en el pasado', 'La hora de entrada seleccionada ya ha pasado. Por favor, vuelve y elige una hora futura.', 'javascript:history.back()');
    }
    if (strtotime($fecha_fin) <= strtotime($fecha_inicio)) {
        mostrarErrorReserva('Horas incorrectas', 'La hora de salida debe ser posterior a la de entrada.', 'javascript:history.back()');
    }

    $fecha_inicio = str_replace('T', ' ', $_POST['hora_entrada']);
    $fecha_fin = str_replace('T', ' ', $_POST['hora_salida']);

    $inicio = new DateTime($fecha_inicio);
    $fin = new DateTime($fecha_fin);

    $fecha = $inicio->format('Y-m-d');
    $hora_entrada = $inicio->format('H:i:s');
    $hora_salida = $fin->format('H:i:s');

    // DURACIÓN
    $intervalo = $inicio->diff($fin);
    $horas = ($intervalo->days * 24) + $intervalo->h;

    if ($horas <= 0) {
        $horas = 1;
    }

    $duracion = $horas;

    // PRECIO
    $sql = "SELECT Precio, Direccion FROM plaza WHERE ID_plaza = ?";
    $stmt = $_conexion->prepare($sql);
    $stmt->bind_param("i", $id_plaza);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        mostrarErrorReserva('Plaza no encontrada', 'No se encontró la plaza solicitada.', '../index.php');
    }

    $plaza = $result->fetch_assoc();
    $precio_hora = $plaza['Precio'];
    $direccion = $plaza['Direccion'] ?? '';

    // Aplicar comisión del 20% (precio base × 1.20)
    $precio_con_comision = round($precio_hora * 1.20, 2);
    $comision = round($precio_hora * 0.20, 2);
    $total = $horas * $precio_con_comision;

    // COMPROBAR DISPONIBILIDAD (solo reservas confirmadas bloquean la plaza)
    $sql = "SELECT * FROM reserva 
            WHERE ID_plaza = ? 
            AND Fecha = ?
            AND Estado = 'confirmada'
            AND (Hora_entrada < ? AND Hora_salida > ?)";

    $stmt = $_conexion->prepare($sql);
    $stmt->bind_param("isss", $id_plaza, $fecha, $hora_salida, $hora_entrada);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        mostrarErrorReserva('Plaza no disponible', 'Esta plaza ya está reservada en ese horario. Por favor, elige otro horario.', 'javascript:history.back()');
    }

    // EVITAR DUPLICADOS: Comprobar si ya existe una reserva pendiente idéntica
    $sql = "SELECT ID_reserva FROM reserva 
            WHERE DNI = ? 
            AND ID_plaza = ? 
            AND Fecha = ?
            AND Hora_entrada = ?
            AND Hora_salida = ?
            AND Estado = 'pendiente'
            LIMIT 1";

    $stmt = $_conexion->prepare($sql);
    $stmt->bind_param("sisss", $dni, $id_plaza, $fecha, $hora_entrada, $hora_salida);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Ya existe una reserva pendiente idéntica, reutilizarla
        $row = $result->fetch_assoc();
        $id_reserva = $row['ID_reserva'];
        $_SESSION['id_reserva_actual'] = $id_reserva;
    } else {
        // No existe, crear nueva reserva pendiente
        $sql = "INSERT INTO reserva
                (DNI, ID_plaza, Precio, Duracion, Hora_entrada, Hora_salida, Fecha, Estado)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'pendiente')";

        $stmt = $_conexion->prepare($sql);

        $stmt->bind_param(
            "sidisss",
            $dni,
            $id_plaza,
            $total,
            $duracion,
            $hora_entrada,
            $hora_salida,
            $fecha
        );

        $stmt->execute();

        $id_reserva = $_conexion->insert_id;
        $_SESSION['id_reserva_actual'] = $id_reserva;
        // Marcar que es una reserva nueva para mostrar notificación en el frontend
        $_SESSION['reserva_recien_creada'] = $direccion ?? '';

        // Crear notificación persistente en BD
        require_once __DIR__ . '/../notificaciones/notificaciones_helper.php';
        crearNotificacion($_conexion, $dni, 'reserva_pendiente', 'Reserva pendiente de pago', 'Tu reserva en ' . ($direccion ?? 'tu plaza') . ' está pendiente de pago. Completa el pago para confirmarla.', $id_reserva);
    }
}

// ============================================================================
// PÁGINA DE PAGO (COMÚN PARA AMBOS FLUJOS)
// ============================================================================
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Pagar reserva — Zpot</title>

     <!-- Fuentes e Iconos -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <link rel="stylesheet" href="../app.css">
    <link rel="stylesheet" href="../styles/pago.css">
    
</head>
<body class="payment-page">

<div class="layout">
    <div class="payment-card">
        <img src="../../frontend/assets/images/logo.png" alt="Zpot" class="logo-img">
        <h2>Resumen del pago</h2>
        
        <div class="price-tag">
            <?php echo number_format($total, 2); ?><span>€</span>
        </div>

        <div class="details-box">
            <div class="detail-item"><span>Precio plaza/hora:</span> <span><?php echo number_format($precio_hora, 2); ?> €</span></div>
            <div class="detail-item"><span>Comisión de servicio (20%):</span> <span><?php echo number_format($comision, 2); ?> €/hora</span></div>
            <div class="detail-item"><span>Precio final/hora:</span> <span><?php echo number_format($precio_con_comision, 2); ?> €</span></div>
            <div class="detail-item"><span>Duración:</span> <span><?php echo $duracion; ?> horas</span></div>
            <div class="detail-item"><span>Fecha:</span> <span><?php echo $fecha; ?></span></div>
            <div class="detail-item" style="font-weight:700;border-top:1px solid #e0e0e0;padding-top:0.5rem;margin-top:0.5rem;"><span>Total:</span> <span><?php echo number_format($total, 2); ?> €</span></div>
        </div>

        <!-- PASAMOS DATOS AL JS AQUÍ -->
        <div id="paypal-button-container"
             data-total="<?php echo number_format($total, 2, '.', ''); ?>"
             data-id-reserva="<?php echo $id_reserva; ?>">
        </div>
        
        <a href="../index.php" class="back-link"><i data-lucide="arrow-left"></i> Cancelar y volver</a>
    </div>
</div>

<!-- SDK DE PAYPAL-->
<script src="https://www.paypal.com/sdk/js?client-id=AY_hkQ1T9mIhXUfY2Eu7TXdORPVLmI-SF6UaaorGnCYcgYsZ6Zt40_KL-fPTzCzE812wHUxd3JCzYWEP&currency=EUR&intent=capture"></script>

<!-- TU JS SEPARADO -->
<script src="../../scripts/pago.js"></script>
<script>
// Banner de "pendiente de pago" visible en la propia página de pago
(function() {
    var banner = document.createElement('div');
    banner.style.cssText = 'background:#fffbeb;border:1.5px solid #f59e0b;border-radius:12px;padding:0.75rem 1rem;margin:0 0 1rem;display:flex;align-items:center;gap:0.5rem;font-size:0.82rem;color:#92400e;';
    banner.innerHTML =
        '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>' +
        '<span><strong>Reserva pendiente de pago.</strong> Completa el pago para confirmar tu plaza.</span>';
    var card = document.querySelector('.payment-card');
    var h2   = card && card.querySelector('h2');
    if (h2) { card.insertBefore(banner, h2.nextSibling); }
})();
</script>
</body>
</html>