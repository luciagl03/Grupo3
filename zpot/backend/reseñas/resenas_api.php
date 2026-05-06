<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
header('Content-Type: application/json; charset=utf-8');

function respondJson($status, $payload) {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

require_once __DIR__ . '/../sesion/conexion.php';
$_conexion->set_charset('utf8mb4');

// ─────────────────────────────────────────────
// GET → listar reseñas de una plaza
// ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $id_plaza = isset($_GET['id_plaza']) ? (int) $_GET['id_plaza'] : 0;
    if ($id_plaza <= 0) {
        respondJson(400, ['success' => false, 'error' => 'ID de plaza inválido']);
    }

    $stmt = $_conexion->prepare(
        "SELECT r.ID_resena, r.Puntuacion, r.Comentario, r.Fecha,
                u.Nombre, u.Apellidos
         FROM RESENA r
         JOIN USUARIO u ON r.DNI = u.DNI
         WHERE r.ID_plaza = ?
         ORDER BY r.Fecha DESC"
    );
    $stmt->bind_param('i', $id_plaza);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $stmtMedia = $_conexion->prepare(
        "SELECT ROUND(AVG(Puntuacion), 1) AS media, COUNT(*) AS total
         FROM RESENA WHERE ID_plaza = ?"
    );
    $stmtMedia->bind_param('i', $id_plaza);
    $stmtMedia->execute();
    $stats = $stmtMedia->get_result()->fetch_assoc();
    $stmtMedia->close();

    $yaReseno     = false;
    $puedeResenar = false;

    if (isset($_SESSION['dni'])) {
        $dni = $_SESSION['dni'];

        $stmtCheck = $_conexion->prepare(
            "SELECT 1 FROM RESENA WHERE ID_plaza = ? AND DNI = ?"
        );
        $stmtCheck->bind_param('is', $id_plaza, $dni);
        $stmtCheck->execute();
        $yaReseno = $stmtCheck->get_result()->num_rows > 0;
        $stmtCheck->close();

        $stmtRes = $_conexion->prepare(
            "SELECT 1 FROM RESERVA
             WHERE ID_plaza = ? AND DNI = ? AND Estado = 'confirmada'
             LIMIT 1"
        );
        $stmtRes->bind_param('is', $id_plaza, $dni);
        $stmtRes->execute();
        $tieneReserva = $stmtRes->get_result()->num_rows > 0;
        $stmtRes->close();

        $puedeResenar = $tieneReserva && !$yaReseno;
    }

    $resenas = array_map(function ($r) {
        return [
            'id'         => (int) $r['ID_resena'],
            'puntuacion' => (int) $r['Puntuacion'],
            'comentario' => $r['Comentario'],
            'fecha'      => date('d/m/Y', strtotime($r['Fecha'])),
            'autor'      => trim($r['Nombre'] . ' ' . mb_substr($r['Apellidos'], 0, 1) . '.'),
        ];
    }, $rows);

    respondJson(200, [
        'success'       => true,
        'resenas'       => $resenas,
        'media'         => $stats['media'] !== null ? (float) $stats['media'] : null,
        'total'         => (int) $stats['total'],
        'puede_resenar' => $puedeResenar,
        'ya_reseno'     => $yaReseno,
    ]);
}

// ─────────────────────────────────────────────
// POST → crear reseña
// ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_SESSION['usuario'])) {
        respondJson(401, ['success' => false, 'error' => 'Debes iniciar sesión para reseñar']);
    }

    $dni  = $_SESSION['dni'] ?? '';
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        respondJson(400, ['success' => false, 'error' => 'JSON inválido']);
    }

    $id_plaza   = isset($data['id_plaza'])   ? (int) $data['id_plaza']   : 0;
    $puntuacion = isset($data['puntuacion']) ? (int) $data['puntuacion'] : 0;
    $comentario = isset($data['comentario']) ? trim((string) $data['comentario']) : '';

    $errors = [];
    if ($id_plaza <= 0)                     $errors['id_plaza']   = 'Plaza no válida';
    if ($puntuacion < 1 || $puntuacion > 5) $errors['puntuacion'] = 'La puntuación debe estar entre 1 y 5';
    if (mb_strlen($comentario) > 500)       $errors['comentario'] = 'El comentario no puede superar los 500 caracteres';

    if (!empty($errors)) {
        respondJson(422, ['success' => false, 'errors' => $errors]);
    }

    $stmtRes = $_conexion->prepare(
        "SELECT 1 FROM RESERVA
         WHERE ID_plaza = ? AND DNI = ? AND Estado = 'confirmada'
         LIMIT 1"
    );
    $stmtRes->bind_param('is', $id_plaza, $dni);
    $stmtRes->execute();
    if ($stmtRes->get_result()->num_rows === 0) {
        $stmtRes->close();
        respondJson(403, ['success' => false, 'error' => 'Solo puedes reseñar plazas donde hayas aparcado']);
    }
    $stmtRes->close();

    $stmtDup = $_conexion->prepare(
        "SELECT 1 FROM RESENA WHERE ID_plaza = ? AND DNI = ?"
    );
    $stmtDup->bind_param('is', $id_plaza, $dni);
    $stmtDup->execute();
    if ($stmtDup->get_result()->num_rows > 0) {
        $stmtDup->close();
        respondJson(409, ['success' => false, 'error' => 'Ya has reseñado esta plaza']);
    }
    $stmtDup->close();

    $comentarioClean = $comentario !== '' ? htmlspecialchars($comentario, ENT_QUOTES, 'UTF-8') : null;

    try {
        $stmt = $_conexion->prepare(
            "INSERT INTO RESENA (ID_plaza, DNI, Puntuacion, Comentario) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param('isis', $id_plaza, $dni, $puntuacion, $comentarioClean);
        $stmt->execute();
        $stmt->close();

        respondJson(201, ['success' => true, 'message' => '¡Reseña publicada!']);
    } catch (mysqli_sql_exception $e) {
        respondJson(500, ['success' => false, 'error' => 'Error al guardar la reseña']);
    }
}

respondJson(405, ['success' => false, 'error' => 'Método no permitido']);
