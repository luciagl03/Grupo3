<?php
/**
 * Returns parking spots (PLAZA) with owner info for the map.
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

function respondJson($status, $payload) {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function mapPlazaRow($row, $hasPrecio) {
    return [
        'id' => (int) $row['ID_plaza'],
        'direccion' => $row['Direccion'] ?? '',
        'foto' => $row['Foto'] ?? '',
        'ancho' => $row['Ancho'] ? (float) $row['Ancho'] : null,
        'largo' => $row['Largo'] ? (float) $row['Largo'] : null,
        'descripcion' => $row['Descripcion'] ?? '',
        'precio' => $hasPrecio && isset($row['Precio']) && $row['Precio'] !== null ? round((float) $row['Precio'] * 1.20, 2) : null,
        'owner' => trim(($row['owner_nombre'] ?? '') . ' ' . ($row['owner_apellidos'] ?? '')),
        'owner_dni' => $row['DNI'] ?? null,
        'ubicacion' => $row['Ubicacion'] ?? null,
        'extras' => !empty($row['Extras']) ? explode(',', $row['Extras']) : [],
        'lat' => isset($row['Lat']) && $row['Lat'] !== null ? (float) $row['Lat'] : null,
        'lng' => isset($row['Lng']) && $row['Lng'] !== null ? (float) $row['Lng'] : null,
    ];
}

if (!isset($_SESSION['usuario'])) {
    respondJson(401, ['error' => 'Not authenticated']);
}

require_once __DIR__ . '/../sesion/conexion.php';
$_conexion->set_charset('utf8mb4');

$list = [];
$hasPrecio = true;

$sqlWithPrecio = "SELECT p.ID_plaza, p.DNI, p.Direccion, p.Foto, p.Ancho, p.Largo, p.Descripcion, p.Precio, p.Ubicacion, p.Extras, p.Lat, p.Lng,
    u.Nombre AS owner_nombre, u.Apellidos AS owner_apellidos
    FROM PLAZA p
    LEFT JOIN USUARIO u ON p.DNI = u.DNI
    ORDER BY p.ID_plaza";

$sqlNoPrecio = "SELECT p.ID_plaza, p.DNI, p.Direccion, p.Foto, p.Ancho, p.Largo, p.Descripcion, p.Ubicacion, p.Extras, p.Lat, p.Lng,
    u.Nombre AS owner_nombre, u.Apellidos AS owner_apellidos
    FROM PLAZA p
    LEFT JOIN USUARIO u ON p.DNI = u.DNI
    ORDER BY p.ID_plaza";

try {
    $result = $_conexion->query($sqlWithPrecio);
} catch (Throwable $e) {
    try {
        $result = $_conexion->query($sqlNoPrecio);
    } catch (Throwable $e2) {
        $result = false;
    }
    $hasPrecio = false;
}

if (!$result) {
    respondJson(500, ['error' => 'Database error']);
}

while ($row = $result->fetch_assoc()) {
    $list[] = mapPlazaRow($row, $hasPrecio);
}

echo json_encode(['plazas' => $list], JSON_UNESCAPED_UNICODE);