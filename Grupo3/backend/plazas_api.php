<?php
/**
 * Returns parking spots (PLAZA) with owner info for the map.
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

require_once __DIR__ . '/sesion/conexion.php';

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
    $result = $_conexion->query($sqlWithPrecio);
} catch (Throwable $e) {
    $result = $_conexion->query($sqlNoPrecio);
    $hasPrecio = false;
}

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
    exit;
}

while ($row = $result->fetch_assoc()) {
    $list[] = [
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

echo json_encode(['plazas' => $list]);
