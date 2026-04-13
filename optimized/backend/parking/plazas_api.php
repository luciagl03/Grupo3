<?php
/**
 * Returns parking spots (PLAZA) with owner info for the map.
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

// Sends JSON response and exits.
function respondJson($status, $payload) {
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

// Maps DB rows to the API response contract used by app.js.
function mapPlazaRow($row, $hasPrecio) {
    return [
        'id' => (int) $row['ID_plaza'],
        'direccion' => $row['Direccion'] ?? '',
        'foto' => $row['Foto'] ?? '',
        'ancho' => $row['Ancho'] ? (float) $row['Ancho'] : null,
        'largo' => $row['Largo'] ? (float) $row['Largo'] : null,
        'descripcion' => $row['Descripcion'] ?? '',
        'precio' => $hasPrecio && isset($row['Precio']) && $row['Precio'] !== null ? (float) $row['Precio'] : null,
        'owner' => trim(($row['owner_nombre'] ?? '') . ' ' . ($row['owner_apellidos'] ?? '')),
        'owner_dni' => $row['DNI'] ?? null
    ];
}

if (!isset($_SESSION['usuario'])) {
    respondJson(401, ['error' => 'Not authenticated']);
}

require_once __DIR__ . '/../sesion/conexion.php';

$list = [];
$hasPrecio = true;

$sqlWithPrecio = "SELECT p.ID_plaza, p.DNI, p.Direccion, p.Foto, p.Ancho, p.Largo, p.Descripcion, p.Precio,
    u.Nombre AS owner_nombre, u.Apellidos AS owner_apellidos
    FROM PLAZA p
    LEFT JOIN USUARIO u ON p.DNI = u.DNI
    ORDER BY p.ID_plaza";

$sqlNoPrecio = "SELECT p.ID_plaza, p.DNI, p.Direccion, p.Foto, p.Ancho, p.Largo, p.Descripcion,
    u.Nombre AS owner_nombre, u.Apellidos AS owner_apellidos
    FROM PLAZA p
    LEFT JOIN USUARIO u ON p.DNI = u.DNI
    ORDER BY p.ID_plaza";

try {
    // Prefer query with 'Precio'; fallback keeps compatibility with older schema.
    $result = $_conexion->query($sqlWithPrecio);
} catch (Throwable $e) {
    $result = $_conexion->query($sqlNoPrecio);
    $hasPrecio = false;
}

if (!$result) {
    respondJson(500, ['error' => 'Database error']);
}

// Build final list consumed by map markers/details.
while ($row = $result->fetch_assoc()) {
    $list[] = mapPlazaRow($row, $hasPrecio);
}

echo json_encode(['plazas' => $list]);
