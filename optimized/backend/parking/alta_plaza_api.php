<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
/**
 * Create parking spot (PLAZA) for the logged-in user.
 * POST JSON: direccion, foto?, ancho?, largo?, descripcion?, precio?
 * DNI is taken from session (user identified by email); client cannot set owner.
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

// Sends JSON response and exits.
function respondJson($status, $payload) {
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

// Geocodes an address string via Nominatim. Returns [lat, lng] or null on failure.
function geocodeAddress($address) {
    $url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' . urlencode($address);
    $ctx = stream_context_create(['http' => ['header' => "User-Agent: Zpot/1.0\r\n", 'timeout' => 5]]);
    $json = @file_get_contents($url, false, $ctx);
    if (!$json) return null;
    $results = json_decode($json, true);
    if (empty($results)) return null;
    return [(float) $results[0]['lat'], (float) $results[0]['lon']];
}

// Sanitizes optional text fields.
function cleanNullableText($value) {
    $clean = trim((string) $value);
    return $clean !== '' ? htmlspecialchars($clean, ENT_QUOTES, 'UTF-8') : null;
}

// Normalizes optional numeric fields with min validation.
function parseOptionalNumber($value, $fieldName, &$errors) {
    if ($value === null || $value === '') {
        return null;
    }
    $num = is_numeric($value) ? (float) $value : null;
    if ($num === null || $num < 0) {
        $errors[$fieldName] = ucfirst($fieldName) . ' debe ser un número mayor o igual a 0';
        return null;
    }
    return $num;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respondJson(405, ['success' => false, 'error' => 'Method not allowed']);
}

if (!isset($_SESSION['usuario'])) {
    respondJson(401, ['success' => false, 'error' => 'Not authenticated']);
}

require_once __DIR__ . '/../sesion/conexion.php';

$email = $_SESSION['usuario'];
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    respondJson(400, ['success' => false, 'error' => 'Invalid JSON']);
}

// -------- Input normalization --------
$direccionRaw = isset($data['direccion']) ? trim((string) $data['direccion']) : '';
$foto = cleanNullableText($data['foto'] ?? '');
$descripcion = cleanNullableText($data['descripcion'] ?? '');
$escritura = cleanNullableText($data['escritura'] ?? '');
$ubicacionesValidas = ['cubierto', 'garaje', 'exterior'];
$ubicacion = isset($data['ubicacion']) && in_array($data['ubicacion'], $ubicacionesValidas, true) ? $data['ubicacion'] : null;

$extrasValidos = ['ev', 'vigilado', '24h'];
$extrasRaw = isset($data['extras']) && is_array($data['extras']) ? $data['extras'] : [];
$extrasRaw = array_filter($extrasRaw, fn($e) => in_array($e, $extrasValidos, true));
$extras = count($extrasRaw) > 0 ? implode(',', array_values($extrasRaw)) : null;

$errors = [];

if ($direccionRaw === '') {
    $errors['direccion'] = 'La dirección es obligatoria';
} elseif (strpos($direccionRaw, ',') === false) {
    $errors['direccion'] = 'Incluye la ciudad separada por coma (ej: Calle Larios 2, Málaga)';
}
$direccion = $direccionRaw !== '' ? htmlspecialchars($direccionRaw, ENT_QUOTES, 'UTF-8') : '';

$ancho = parseOptionalNumber($data['ancho'] ?? null, 'ancho', $errors);
$largo = parseOptionalNumber($data['largo'] ?? null, 'largo', $errors);
$precioRaw = $data['precio'] ?? null;
if ($precioRaw === null || $precioRaw === '') {
    $errors['precio'] = 'El precio es obligatorio';
    $precio = null;
} else {
    $precio = parseOptionalNumber($precioRaw, 'precio', $errors);
    if ($precio !== null && $precio <= 0) {
        $errors['precio'] = 'El precio debe ser mayor que 0';
    }
}

if ($foto !== null && $foto !== '' && !filter_var($foto, FILTER_VALIDATE_URL)) {
    $errors['foto'] = 'La URL de la foto no es válida';
}

if (!empty($errors)) {
    respondJson(422, ['success' => false, 'errors' => $errors]);
}

try {
    // -------- Resolve owner DNI from authenticated email --------
    $stmt = $_conexion->prepare('SELECT DNI FROM USUARIO WHERE Email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        $stmt->close();
        respondJson(401, ['success' => false, 'error' => 'Not authenticated']);
    }
    $row = $result->fetch_assoc();
    $dni = $row['DNI'];
    $stmt->close();

    // -------- Insert parking spot --------
    $stmt = $_conexion->prepare(
        'INSERT INTO PLAZA (DNI, Direccion, Foto, Ancho, Largo, Descripcion, Escritura, Precio, Ubicacion, Extras) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param(
        'sssddssdss',
        $dni,
        $direccion,
        $foto,
        $ancho,
        $largo,
        $descripcion,
        $escritura,
        $precio,
        $ubicacion,
        $extras
    );
    $stmt->execute();
    $id = (int) $_conexion->insert_id;
    $stmt->close();

    $coords = geocodeAddress($direccion);
    if ($coords) {
        $stmtGeo = $_conexion->prepare('UPDATE PLAZA SET Lat = ?, Lng = ? WHERE ID_plaza = ?');
        $stmtGeo->bind_param('ddi', $coords[0], $coords[1], $id);
        $stmtGeo->execute();
        $stmtGeo->close();
    }

    respondJson(201, ['success' => true, 'id' => $id, 'geocoded' => $coords !== null]);
} catch (mysqli_sql_exception $e) {
    respondJson(500, ['success' => false, 'error' => 'Error al guardar la plaza. Inténtalo de nuevo.']);
}
