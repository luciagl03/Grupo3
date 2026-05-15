<?php
/**
 * chat_api.php
 * Ubicación: backend/chat/chat_api.php
 *
 * GET  ?id_reserva=N          → mensajes de esa reserva
 * POST { id_reserva, mensaje } → enviar mensaje
 */
// Evitar que errores PHP rompan la respuesta JSON
error_reporting(0);
ini_set('display_errors', '0');

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

// Local: /../../sesion/conexion.php  |  InfinityFree: igual
// PARA INFINITY: require_once __DIR__ . '/../../sesion/conexion.php';
require_once __DIR__ . '/../sesion/conexion.php';
$_conexion->set_charset('utf8mb4');

// Resolver DNI desde sesión o desde email
$dni = $_SESSION['dni'] ?? '';
if (empty($dni) && isset($_SESSION['usuario'])) {
    $email = $_SESSION['usuario'];
    $st = $_conexion->prepare('SELECT DNI FROM USUARIO WHERE Email = ?');
    $st->bind_param('s', $email);
    $st->execute();
    $row = $st->get_result()->fetch_assoc();
    $st->close();
    if ($row) {
        $dni = $row['DNI'];
        $_SESSION['dni'] = $dni;
    } else {
        respondJson(401, ['success' => false, 'error' => 'Usuario no encontrado']);
    }
}

// ── Verificar acceso a la reserva (inquilino o propietario) ──
function verificarAcceso($con, $id_reserva, $dni) {
    $stmt = $con->prepare(
        "SELECT r.ID_reserva, r.DNI AS dni_inquilino, p.DNI AS dni_propietario,
                p.Direccion, u_i.Nombre AS nombre_inquilino, u_p.Nombre AS nombre_propietario
         FROM RESERVA r
         JOIN PLAZA p ON r.ID_plaza = p.ID_plaza
         JOIN USUARIO u_i ON r.DNI = u_i.DNI
         JOIN USUARIO u_p ON p.DNI = u_p.DNI
         WHERE r.ID_reserva = ? AND (r.DNI = ? OR p.DNI = ?)"
    );
    $stmt->bind_param('iss', $id_reserva, $dni, $dni);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result;
}

// ── GET → listar mensajes ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id_reserva = isset($_GET['id_reserva']) ? (int)$_GET['id_reserva'] : 0;
    if ($id_reserva <= 0) respondJson(400, ['success' => false, 'error' => 'ID inválido']);

    $acceso = verificarAcceso($_conexion, $id_reserva, $dni);
    if (!$acceso) respondJson(403, ['success' => false, 'error' => 'Sin acceso a esta reserva']);

    // Marcar como leídos los mensajes del otro
    $stmtL = $_conexion->prepare("UPDATE MENSAJE SET Leido=1 WHERE ID_reserva=? AND DNI_emisor!=? AND Leido=0");
    $stmtL->bind_param('is', $id_reserva, $dni);
    $stmtL->execute();
    $stmtL->close();

    $stmt = $_conexion->prepare(
        "SELECT m.ID_mensaje, m.DNI_emisor, m.Contenido, m.Leido, m.Fecha,
                u.Nombre, u.Apellidos
         FROM MENSAJE m
         JOIN USUARIO u ON m.DNI_emisor = u.DNI
         WHERE m.ID_reserva = ?
         ORDER BY m.Fecha ASC"
    );
    $stmt->bind_param('i', $id_reserva);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $stmtNL = $_conexion->prepare("SELECT COUNT(*) AS total FROM MENSAJE WHERE ID_reserva=? AND DNI_emisor!=? AND Leido=0");
    $stmtNL->bind_param('is', $id_reserva, $dni);
    $stmtNL->execute();
    $noLeidos = (int) $stmtNL->get_result()->fetch_assoc()['total'];
    $stmtNL->close();

    respondJson(200, [
        'success'   => true,
        'reserva'   => $acceso,
        'yo_soy'    => ($dni === $acceso['dni_inquilino']) ? 'inquilino' : 'propietario',
        'no_leidos' => $noLeidos,
        'mensajes'  => array_map(function($r) use ($dni) {
            return [
                'id'        => (int) $r['ID_mensaje'],
                'es_mio'    => $r['DNI_emisor'] === $dni,
                'emisor'    => $r['Nombre'] . ' ' . mb_substr($r['Apellidos'], 0, 1) . '.',
                'contenido' => $r['Contenido'],
                'leido'     => (bool) $r['Leido'],
                'fecha'     => $r['Fecha'],
            ];
        }, $rows),
    ]);
}

// ── POST → enviar mensaje ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data       = json_decode(file_get_contents('php://input'), true) ?? [];
    $id_reserva = (int)($data['id_reserva'] ?? 0);
    $contenido  = trim($data['mensaje'] ?? '');

    if ($id_reserva <= 0)         respondJson(400, ['success' => false, 'error' => 'ID de reserva inválido']);
    if ($contenido === '')        respondJson(422, ['success' => false, 'error' => 'El mensaje no puede estar vacío']);
    if (mb_strlen($contenido) > 1000) respondJson(422, ['success' => false, 'error' => 'Mensaje demasiado largo']);

    $acceso = verificarAcceso($_conexion, $id_reserva, $dni);
    if (!$acceso) respondJson(403, ['success' => false, 'error' => 'Sin acceso a esta reserva']);

    // Guardar mensaje (sin escapar — la BD usa utf8mb4 y prepared statements)
    $stmt = $_conexion->prepare("INSERT INTO MENSAJE (ID_reserva, DNI_emisor, Contenido) VALUES (?,?,?)");
    $stmt->bind_param('iss', $id_reserva, $dni, $contenido);
    if (!$stmt->execute()) {
        $stmt->close();
        respondJson(500, ['success' => false, 'error' => 'Error al guardar el mensaje']);
    }
    $stmt->close();

    // Notificar al otro participante (en try/catch para no romper la respuesta si falla)
    try {
        // PARA INFINITY: $helperPath = __DIR__ . '/../../notificaciones/notificaciones_helper.php';
        $helperPath = __DIR__ . '/../notificaciones/notificaciones_helper.php';
        if (file_exists($helperPath)) {
            require_once $helperPath;
            $dest = ($dni === $acceso['dni_inquilino'])
                ? $acceso['dni_propietario']
                : $acceso['dni_inquilino'];
            crearNotificacion(
                $_conexion, $dest,
                'nuevo_mensaje', 'Nuevo mensaje',
                'Tienes un nuevo mensaje sobre la reserva en ' . ($acceso['Direccion'] ?? 'tu plaza') . '.',
                $id_reserva
            );
        }
    } catch (Exception $e) {
        // No interrumpir si falla la notificación
    }

    respondJson(201, ['success' => true]);
}

respondJson(405, ['success' => false, 'error' => 'Método no permitido']);