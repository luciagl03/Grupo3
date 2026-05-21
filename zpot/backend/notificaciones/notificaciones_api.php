<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

function respondJson($status, $payload) {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_SESSION['usuario'])) {
    respondJson(401, ['success' => false, 'error' => 'No autenticado']);
}

//PARA INFINITY : require_once __DIR__ . '/../../sesion/conexion.php';
require_once __DIR__ . '/../sesion/conexion.php';
$_conexion->set_charset('utf8mb4');

// Obtener DNI desde sesión; si no existe, resolverlo desde el email
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
        $_SESSION['dni'] = $dni; // cachear para próximas llamadas
    } else {
        respondJson(401, ['success' => false, 'error' => 'Usuario no encontrado']);
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $_conexion->prepare(
        "SELECT ID_notif, Tipo, Titulo, Mensaje, Leida, Fecha, ID_ref
         FROM NOTIFICACION WHERE DNI = ?
         ORDER BY Fecha DESC LIMIT 50"
    );
    $stmt->bind_param('s', $dni);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $stmtCount = $_conexion->prepare(
        "SELECT COUNT(*) AS total FROM NOTIFICACION WHERE DNI = ? AND Leida = 0"
    );
    $stmtCount->bind_param('s', $dni);
    $stmtCount->execute();
    $noLeidas = (int) $stmtCount->get_result()->fetch_assoc()['total'];
    $stmtCount->close();

    respondJson(200, [
        'success'   => true,
        'no_leidas' => $noLeidas,
        'notifs'    => array_map(function($r) {
            return [
                'id'      => (int) $r['ID_notif'],
                'tipo'    => $r['Tipo'],
                'titulo'  => $r['Titulo'],
                'mensaje' => $r['Mensaje'],
                'leida'   => (bool) $r['Leida'],
                'fecha'   => $r['Fecha'],
                'id_ref'  => $r['ID_ref'] ? (int) $r['ID_ref'] : null,
            ];
        }, $rows),
    ]);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data   = json_decode(file_get_contents('php://input'), true) ?? [];
    $accion = $data['accion'] ?? '';

    if ($accion === 'marcar_leida') {
        $id = (int)($data['id_notif'] ?? 0);
        if ($id <= 0) respondJson(400, ['success' => false, 'error' => 'ID inválido']);
        $stmt = $_conexion->prepare("UPDATE NOTIFICACION SET Leida=1 WHERE ID_notif=? AND DNI=?");
        $stmt->bind_param('is', $id, $dni);
        $stmt->execute();
        $stmt->close();
        respondJson(200, ['success' => true]);
    }

    if ($accion === 'marcar_todas') {
        $stmt = $_conexion->prepare("UPDATE NOTIFICACION SET Leida=1 WHERE DNI=? AND Leida=0");
        $stmt->bind_param('s', $dni);
        $stmt->execute();
        $stmt->close();
        respondJson(200, ['success' => true]);
    }

    if ($accion === 'crear') {
        $tipo    = trim($data['tipo']    ?? '');
        $titulo  = trim($data['titulo']  ?? '');
        $mensaje = trim($data['mensaje'] ?? '');
        $id_ref  = isset($data['id_ref']) ? (int)$data['id_ref'] : null;

        if (!$tipo || !$titulo || !$mensaje) {
            respondJson(422, ['success' => false, 'error' => 'Faltan campos']);
        }

        $stmt = $_conexion->prepare(
            "INSERT INTO NOTIFICACION (DNI, Tipo, Titulo, Mensaje, ID_ref) VALUES (?,?,?,?,?)"
        );
        $stmt->bind_param('ssssi', $dni, $tipo, $titulo, $mensaje, $id_ref);
        $stmt->execute();
        $stmt->close();
        respondJson(201, ['success' => true]);
    }

    respondJson(400, ['success' => false, 'error' => 'Acción no válida']);
}

respondJson(405, ['success' => false, 'error' => 'Método no permitido']);