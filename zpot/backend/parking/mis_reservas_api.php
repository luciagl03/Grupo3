<?php
/**
 * mis_reservas_api.php
 * GET ?todas=1  → todas las reservas del usuario (para panel de chat)
 * GET           → reservas de HOY confirmadas (para timers)
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

// Resolver DNI desde sesión o email
$dni = $_SESSION['dni'] ?? '';
if (empty($dni) && isset($_SESSION['usuario'])) {
    $email = $_SESSION['usuario'];
    $stmtDni = $_conexion->prepare('SELECT DNI FROM USUARIO WHERE Email = ?');
    $stmtDni->bind_param('s', $email);
    $stmtDni->execute();
    $rowDni = $stmtDni->get_result()->fetch_assoc();
    $stmtDni->close();
    if ($rowDni) {
        $dni = $rowDni['DNI'];
        $_SESSION['dni'] = $dni;
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Usuario no encontrado']);
        exit;
    }
}

$todas = isset($_GET['todas']) && $_GET['todas'] === '1';
$hoy   = date('Y-m-d');

if ($todas) {
    // Devuelve TODAS las reservas (pendiente + confirmada) — usadas por el panel de chat
    $stmt = $_conexion->prepare(
        "SELECT r.ID_reserva, r.Hora_entrada, r.Hora_salida, r.Fecha, r.Estado,
                p.Direccion
         FROM RESERVA r
         LEFT JOIN PLAZA p ON r.ID_plaza = p.ID_plaza
         WHERE r.DNI = ? AND r.Estado IN ('pendiente','confirmada')
         ORDER BY r.Fecha DESC, r.Hora_entrada DESC
         LIMIT 50"
    );
    $stmt->bind_param('s', $dni);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $reservas = array_map(function($r) {
        return [
            'id'          => (int) $r['ID_reserva'],
            'direccion'   => $r['Direccion'] ?? 'Plaza',
            'hora_entrada'=> $r['Hora_entrada'],
            'hora_salida' => $r['Hora_salida'],
            'fecha'       => $r['Fecha'],
            'estado'      => $r['Estado'],
        ];
    }, $rows);

} else {
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
            'fecha'       => $r['Fecha'],
            'ts_salida'   => strtotime($hoy . ' ' . $r['Hora_salida'])  * 1000,
            'ts_entrada'  => strtotime($hoy . ' ' . $r['Hora_entrada']) * 1000,
        ];
    }, $rows);
}

echo json_encode(['reservas' => $reservas], JSON_UNESCAPED_UNICODE);