<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

function respondJson($status, $payload) {
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respondJson(405, ['success' => false, 'error' => 'Method not allowed']);
}

if (!isset($_SESSION['usuario'])) {
    respondJson(401, ['success' => false, 'error' => 'Not authenticated']);
}

require_once __DIR__ . '/../sesion/conexion.php';

$dni  = $_SESSION['dni'] ?? '';
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) respondJson(400, ['success' => false, 'error' => 'Invalid JSON']);

$id_reserva = isset($data['id_reserva']) ? (int) $data['id_reserva'] : 0;
$id_plaza   = isset($data['id_plaza'])   ? (int) $data['id_plaza']   : 0;
$rawEntrada = $data['hora_entrada'] ?? '';
$rawSalida  = $data['hora_salida']  ?? '';

if ($id_reserva <= 0) respondJson(400, ['success' => false, 'error' => 'ID de reserva inválido']);
if (!$rawEntrada || !$rawSalida) respondJson(422, ['success' => false, 'error' => 'Fechas requeridas']);

// Parse datetimes
$entrada = new DateTime(str_replace('T', ' ', $rawEntrada));
$salida  = new DateTime(str_replace('T', ' ', $rawSalida));
$ahora   = new DateTime();

if ($entrada < $ahora) {
    respondJson(422, ['success' => false, 'error' => 'La fecha de entrada no puede ser en el pasado']);
}
if ($salida <= $entrada) {
    respondJson(422, ['success' => false, 'error' => 'La fecha de salida debe ser posterior a la de entrada']);
}

$fecha      = $entrada->format('Y-m-d');
$horaEnt    = $entrada->format('H:i:s');
$horaSal    = $salida->format('H:i:s');
$intervalo  = $entrada->diff($salida);
$horas      = max(1, ($intervalo->days * 24) + $intervalo->h);

// Verify ownership
$stmt = $_conexion->prepare('SELECT ID_reserva FROM RESERVA WHERE ID_reserva = ? AND DNI = ?');
$stmt->bind_param('is', $id_reserva, $dni);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    $stmt->close();
    respondJson(403, ['success' => false, 'error' => 'No tienes permiso para editar esta reserva']);
}
$stmt->close();

// Check availability (exclude current reservation)
$stmt = $_conexion->prepare(
    'SELECT ID_reserva FROM RESERVA
     WHERE ID_plaza = ? AND Fecha = ? AND ID_reserva != ?
     AND (Hora_entrada < ? AND Hora_salida > ?)'
);
$stmt->bind_param('isiss', $id_plaza, $fecha, $id_reserva, $horaSal, $horaEnt);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $stmt->close();
    respondJson(409, ['success' => false, 'error' => 'Esta plaza ya está reservada en ese horario']);
}
$stmt->close();

// Get price per hour
$stmt = $_conexion->prepare('SELECT Precio FROM PLAZA WHERE ID_plaza = ?');
$stmt->bind_param('i', $id_plaza);
$stmt->execute();
$plazaRow = $stmt->get_result()->fetch_assoc();
$stmt->close();
$precioHora = $plazaRow ? (float) $plazaRow['Precio'] : 0;
$total = $horas * $precioHora;

// Update reservation
try {
    $stmt = $_conexion->prepare(
        'UPDATE RESERVA SET Fecha = ?, Hora_entrada = ?, Hora_salida = ?, Duracion = ?, Precio = ?, Estado = "pendiente"
         WHERE ID_reserva = ? AND DNI = ?'
    );
    $stmt->bind_param('sssidis', $fecha, $horaEnt, $horaSal, $horas, $total, $id_reserva, $dni);
    $stmt->execute();
    $stmt->close();
    respondJson(200, ['success' => true]);
} catch (mysqli_sql_exception $e) {
    respondJson(500, ['success' => false, 'error' => 'Error al guardar. Inténtalo de nuevo.']);
}
