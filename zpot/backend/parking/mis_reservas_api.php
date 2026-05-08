<?php
/**
 * mis_reservas_api.php
 * Ubicación: backend/parking/mis_reservas_api.php
 *
 * GET → devuelve las reservas de HOY del usuario logueado
 *       para que el JS calcule los timers de "quedan 15 min"
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado']);
    exit;
}
// PARA INFINITY : require_once __DIR__ . '/../../sesion/conexion.php';
require_once __DIR__ . '/../sesion/conexion.php';
$_conexion->set_charset('utf8mb4');

$dni  = $_SESSION['dni'] ?? '';
$hoy  = date('Y-m-d');

$stmt = $_conexion->prepare(
    "SELECT r.ID_reserva, r.Hora_entrada, r.Hora_salida, r.Fecha, r.Estado,
            p.Direccion
     FROM RESERVA r
     LEFT JOIN PLAZA p ON r.ID_plaza = p.ID_plaza
     WHERE r.DNI = ? AND r.Fecha = ? AND r.Estado = 'confirmada'
     ORDER BY r.Hora_entrada ASC"
);
$stmt->bind_param('ss', $dni, $hoy);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$reservas = array_map(function($r) use ($hoy) {
    return [
        'id'          => (int) $r['ID_reserva'],
        'direccion'   => $r['Direccion'] ?? 'Plaza',
        'hora_entrada'=> $r['Hora_entrada'],
        'hora_salida' => $r['Hora_salida'],
        // timestamp Unix de salida para calcular el timer en JS
        'ts_salida'   => strtotime($hoy . ' ' . $r['Hora_salida']) * 1000,
        'ts_entrada'  => strtotime($hoy . ' ' . $r['Hora_entrada']) * 1000,
    ];
}, $rows);

echo json_encode(['reservas' => $reservas], JSON_UNESCAPED_UNICODE);
