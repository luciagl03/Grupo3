<?php

session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

require_once __DIR__ . '/../sesion/conexion.php';
$_conexion->set_charset('utf8mb4');

// Resolver DNI desde sesión
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
        echo json_encode(['success' => false, 'error' => 'Usuario no encontrado']);
        exit;
    }
}

$chats = [];

// 1. Chats como INQUILINO 
$stmtInq = $_conexion->prepare(
    "SELECT DISTINCT r.ID_reserva, r.ID_plaza, p.Direccion,
            u.Nombre AS nombre_prop, u.Apellidos AS apellidos_prop,
            (SELECT COUNT(*) FROM MENSAJE WHERE ID_reserva = r.ID_reserva AND DNI_emisor != ? AND Leido = 0) AS no_leidos
     FROM RESERVA r
     JOIN PLAZA p ON r.ID_plaza = p.ID_plaza
     JOIN USUARIO u ON p.DNI = u.DNI
     WHERE r.DNI = ? AND r.Estado IN ('pendiente', 'confirmada')
     ORDER BY r.Fecha DESC, r.Hora_entrada DESC
     LIMIT 50"
);
$stmtInq->bind_param('ss', $dni, $dni);
$stmtInq->execute();
$rowsInq = $stmtInq->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtInq->close();

foreach ($rowsInq as $row) {
    $chats[] = [
        'tipo'        => 'reserva',
        'id_reserva'  => (int) $row['ID_reserva'],
        'id_plaza'    => (int) $row['ID_plaza'],
        'nombre'      => $row['nombre_prop'] . ' ' . mb_substr($row['apellidos_prop'], 0, 1) . '.',
        'plaza'       => $row['Direccion'] ?? 'Plaza',
        'no_leidos'   => (int) $row['no_leidos'],
        'rol'         => 'inquilino'
    ];
}

// 2. Chats como PROPIETARIO 
$stmtProp = $_conexion->prepare(
    "SELECT DISTINCT r.ID_reserva, r.ID_plaza, p.Direccion,
            u.Nombre AS nombre_inq, u.Apellidos AS apellidos_inq,
            (SELECT COUNT(*) FROM MENSAJE WHERE ID_reserva = r.ID_reserva AND DNI_emisor != ? AND Leido = 0) AS no_leidos
     FROM RESERVA r
     JOIN PLAZA p ON r.ID_plaza = p.ID_plaza
     JOIN USUARIO u ON r.DNI = u.DNI
     WHERE p.DNI = ? AND r.Estado IN ('pendiente', 'confirmada')
       AND EXISTS (SELECT 1 FROM MENSAJE WHERE ID_reserva = r.ID_reserva)
     ORDER BY (SELECT MAX(Fecha) FROM MENSAJE WHERE ID_reserva = r.ID_reserva) DESC
     LIMIT 50"
);
$stmtProp->bind_param('ss', $dni, $dni);
$stmtProp->execute();
$rowsProp = $stmtProp->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtProp->close();

foreach ($rowsProp as $row) {
    $chats[] = [
        'tipo'         => 'reserva',
        'id_reserva'   => (int) $row['ID_reserva'],
        'id_plaza'     => (int) $row['ID_plaza'],
        'dni_inquilino'=> '',
        'nombre'       => $row['nombre_inq'] . ' ' . mb_substr($row['apellidos_inq'], 0, 1) . '.',
        'plaza'        => $row['Direccion'] ?? 'Plaza',
        'no_leidos'    => (int) $row['no_leidos'],
        'rol'          => 'propietario'
    ];
}

// Calcular total de no leídos
$total_no_leidos = array_sum(array_column($chats, 'no_leidos'));

echo json_encode([
    'success'         => true,
    'chats'           => $chats,
    'total_no_leidos' => $total_no_leidos
], JSON_UNESCAPED_UNICODE);
