<?php
/**
 * disponibilidad_api.php
 * GET ?id_plaza=N  → reservas confirmadas futuras de esa plaza (para bloquear el calendario)
 */
error_reporting(0);
ini_set('display_errors', '0');

session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

require_once __DIR__ . '/../sesion/conexion.php';
$_conexion->set_charset('utf8mb4');

$id_plaza = isset($_GET['id_plaza']) ? (int)$_GET['id_plaza'] : 0;
if ($id_plaza <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de plaza inválido']);
    exit;
}

$stmt = $_conexion->prepare(
    "SELECT Fecha, Hora_entrada, Hora_salida
     FROM RESERVA
     WHERE ID_plaza = ? AND Estado = 'confirmada' AND Fecha >= CURDATE()
     ORDER BY Fecha ASC, Hora_entrada ASC"
);
$stmt->bind_param('i', $id_plaza);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode([
    'reservas' => array_map(function($r) {
        return [
            'fecha'        => $r['Fecha'],
            'hora_entrada' => substr($r['Hora_entrada'], 0, 5),
            'hora_salida'  => substr($r['Hora_salida'],  0, 5),
        ];
    }, $rows)
], JSON_UNESCAPED_UNICODE);
