<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

function respondJson($status, $payload) {
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

function geocodeAddress($address) {
    $url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' . urlencode($address);
    $ctx = stream_context_create(['http' => ['header' => "User-Agent: Zpot/1.0\r\n", 'timeout' => 5]]);
    $json = @file_get_contents($url, false, $ctx);
    if (!$json) return null;
    $results = json_decode($json, true);
    if (empty($results)) return null;
    return [(float) $results[0]['lat'], (float) $results[0]['lon']];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respondJson(405, ['success' => false, 'error' => 'Method not allowed']);
}

if (!isset($_SESSION['usuario'])) {
    respondJson(401, ['success' => false, 'error' => 'Not authenticated']);
}

require_once __DIR__ . '/../sesion/conexion.php';

$dni = $_SESSION['dni'] ?? '';
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) respondJson(400, ['success' => false, 'error' => 'Invalid JSON']);

$id_plaza = isset($data['id_plaza']) ? (int) $data['id_plaza'] : 0;
if ($id_plaza <= 0) respondJson(400, ['success' => false, 'error' => 'ID de plaza inválido']);

// Verify ownership
$stmt = $_conexion->prepare('SELECT Direccion FROM PLAZA WHERE ID_plaza = ? AND DNI = ?');
$stmt->bind_param('is', $id_plaza, $dni);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$existing) respondJson(403, ['success' => false, 'error' => 'No tienes permiso para editar esta plaza']);

$errors = [];

$direccionRaw = trim((string)($data['direccion'] ?? ''));
if ($direccionRaw === '') {
    $errors['direccion'] = 'La dirección es obligatoria';
} elseif (strpos($direccionRaw, ',') === false) {
    $errors['direccion'] = 'Incluye la ciudad separada por coma (ej: Calle Larios 2, Málaga)';
}
$direccion = htmlspecialchars($direccionRaw, ENT_QUOTES, 'UTF-8');

$foto = trim((string)($data['foto'] ?? ''));
$foto = $foto !== '' ? htmlspecialchars($foto, ENT_QUOTES, 'UTF-8') : null;
if ($foto && !filter_var($foto, FILTER_VALIDATE_URL)) $errors['foto'] = 'URL de foto no válida';

$descripcion = trim((string)($data['descripcion'] ?? ''));
$descripcion = $descripcion !== '' ? htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8') : null;

$ancho = ($data['ancho'] !== null && $data['ancho'] !== '') ? (float)$data['ancho'] : null;
$largo = ($data['largo'] !== null && $data['largo'] !== '') ? (float)$data['largo'] : null;

$precioRaw = $data['precio'] ?? null;
if ($precioRaw === null || $precioRaw === '') {
    $errors['precio'] = 'El precio es obligatorio';
    $precio = null;
} else {
    $precio = (float)$precioRaw;
    if ($precio <= 0) $errors['precio'] = 'El precio debe ser mayor que 0';
}

$ubicacionesValidas = ['cubierto', 'garaje', 'exterior'];
$ubicacion = isset($data['ubicacion']) && in_array($data['ubicacion'], $ubicacionesValidas, true) ? $data['ubicacion'] : null;

$extrasValidos = ['ev', 'vigilado', '24h'];
$extrasRaw = isset($data['extras']) && is_array($data['extras']) ? $data['extras'] : [];
$extrasRaw = array_filter($extrasRaw, fn($e) => in_array($e, $extrasValidos, true));
$extras = count($extrasRaw) > 0 ? implode(',', array_values($extrasRaw)) : null;

if (!empty($errors)) respondJson(422, ['success' => false, 'errors' => $errors]);

try {
    $stmt = $_conexion->prepare(
        'UPDATE PLAZA SET Direccion=?, Foto=?, Ancho=?, Largo=?, Descripcion=?, Precio=?, Ubicacion=?, Extras=? WHERE ID_plaza=? AND DNI=?'
    );
    $stmt->bind_param('ssddsdssis', $direccion, $foto, $ancho, $largo, $descripcion, $precio, $ubicacion, $extras, $id_plaza, $dni);
    $stmt->execute();
    $stmt->close();

    // Re-geocode if address changed
    if ($direccionRaw !== $existing['Direccion']) {
        $coords = geocodeAddress($direccionRaw);
        if ($coords) {
            $stmtGeo = $_conexion->prepare('UPDATE PLAZA SET Lat=?, Lng=? WHERE ID_plaza=?');
            $stmtGeo->bind_param('ddi', $coords[0], $coords[1], $id_plaza);
            $stmtGeo->execute();
            $stmtGeo->close();
        }
    }

    respondJson(200, ['success' => true]);
} catch (mysqli_sql_exception $e) {
    respondJson(500, ['success' => false, 'error' => 'Error al guardar. Inténtalo de nuevo.']);
}
