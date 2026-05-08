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

//require_once __DIR__ . '/../../sesion/conexion.php';
require_once __DIR__ . '/../sesion/conexion.php';
$_conexion->set_charset('utf8mb4');

$dni = $_SESSION['dni'] ?? '';

// ─────────────────────────────────────────────
// GET → listar métodos de pago
// ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $_conexion->prepare(
        "SELECT ID_metodo, Tipo, Alias, Ultimos4, Marca, Caducidad, Email_paypal, Es_defecto, Fecha_alta
         FROM METODO_PAGO WHERE DNI = ? ORDER BY Es_defecto DESC, Fecha_alta ASC"
    );
    $stmt->bind_param('s', $dni);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    respondJson(200, [
        'success' => true,
        'metodos' => array_map(function($r) {
            return [
                'id'           => (int) $r['ID_metodo'],
                'tipo'         => $r['Tipo'],
                'alias'        => $r['Alias'],
                'ultimos4'     => $r['Ultimos4'],
                'marca'        => $r['Marca'],
                'caducidad'    => $r['Caducidad'],
                'email_paypal' => $r['Email_paypal'],
                'es_defecto'   => (bool) $r['Es_defecto'],
            ];
        }, $rows),
    ]);
}

// ─────────────────────────────────────────────
// POST → acciones
// ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data   = json_decode(file_get_contents('php://input'), true) ?? [];
    $accion = $data['accion'] ?? '';

    // ── Añadir método ────────────────────────
    if ($accion === 'añadir') {
        $tipo  = $data['tipo'] ?? '';
        $alias = trim($data['alias'] ?? '');
        $errors = [];

        if (!in_array($tipo, ['paypal', 'tarjeta'])) $errors[] = 'Tipo no válido';
        if ($alias === '') $errors[] = 'El alias es obligatorio';

        if ($tipo === 'paypal') {
            $email = trim($data['email_paypal'] ?? '');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email de PayPal no válido';
        }

        if ($tipo === 'tarjeta') {
            $ultimos4  = trim($data['ultimos4']  ?? '');
            $marca     = trim($data['marca']     ?? '');
            $caducidad = trim($data['caducidad'] ?? '');
            if (!preg_match('/^\d{4}$/', $ultimos4))        $errors[] = 'Los últimos 4 dígitos no son válidos';
            if (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $caducidad)) $errors[] = 'Fecha de caducidad no válida (MM/AA)';
        }

        if (!empty($errors)) respondJson(422, ['success' => false, 'errors' => $errors]);

        // Contar métodos existentes para saber si es el primero (será defecto)
        $stmtCount = $_conexion->prepare("SELECT COUNT(*) AS total FROM METODO_PAGO WHERE DNI = ?");
        $stmtCount->bind_param('s', $dni);
        $stmtCount->execute();
        $esElPrimero = (int) $stmtCount->get_result()->fetch_assoc()['total'] === 0;
        $stmtCount->close();

        $esDefecto = $esElPrimero ? 1 : (isset($data['es_defecto']) && $data['es_defecto'] ? 1 : 0);

        if ($esDefecto) {
            // Quitar defecto a los demás
            $stmtReset = $_conexion->prepare("UPDATE METODO_PAGO SET Es_defecto = 0 WHERE DNI = ?");
            $stmtReset->bind_param('s', $dni);
            $stmtReset->execute();
            $stmtReset->close();
        }

        if ($tipo === 'paypal') {
            $stmt = $_conexion->prepare(
                "INSERT INTO METODO_PAGO (DNI, Tipo, Alias, Email_paypal, Es_defecto) VALUES (?,?,?,?,?)"
            );
            $stmt->bind_param('ssssi', $dni, $tipo, $alias, $email, $esDefecto);
        } else {
            $marcaClean = htmlspecialchars(strtolower($marca), ENT_QUOTES, 'UTF-8'); //NO ES ERROR
            $stmt = $_conexion->prepare(
                "INSERT INTO METODO_PAGO (DNI, Tipo, Alias, Ultimos4, Marca, Caducidad, Es_defecto) VALUES (?,?,?,?,?,?,?)"
            );
            $stmt->bind_param('ssssssi', $dni, $tipo, $alias, $ultimos4, $marcaClean, $caducidad, $esDefecto);
        }

        $stmt->execute();
        $newId = (int) $_conexion->insert_id;
        $stmt->close();

        // Actualizar Token_pago en USUARIO si es PayPal y defecto
        if ($tipo === 'paypal' && $esDefecto) {
            $stmtTok = $_conexion->prepare("UPDATE USUARIO SET Token_pago = ? WHERE DNI = ?");
            $stmtTok->bind_param('ss', $email, $dni);
            $stmtTok->execute();
            $stmtTok->close();
        }

        respondJson(201, ['success' => true, 'id' => $newId]);
    }

    // ── Eliminar método ──────────────────────
    if ($accion === 'eliminar') {
        $id = (int)($data['id_metodo'] ?? 0);
        if ($id <= 0) respondJson(400, ['success' => false, 'error' => 'ID inválido']);

        // Comprobar si era el defecto
        $stmtCheck = $_conexion->prepare("SELECT Es_defecto FROM METODO_PAGO WHERE ID_metodo = ? AND DNI = ?");
        $stmtCheck->bind_param('is', $id, $dni);
        $stmtCheck->execute();
        $row = $stmtCheck->get_result()->fetch_assoc();
        $stmtCheck->close();

        if (!$row) respondJson(404, ['success' => false, 'error' => 'Método no encontrado']);

        $eraDefecto = (bool) $row['Es_defecto'];

        $stmt = $_conexion->prepare("DELETE FROM METODO_PAGO WHERE ID_metodo = ? AND DNI = ?");
        $stmt->bind_param('is', $id, $dni);
        $stmt->execute();
        $stmt->close();

        // Si era el defecto, asignar el siguiente
        if ($eraDefecto) {
            $stmtNext = $_conexion->prepare("SELECT ID_metodo FROM METODO_PAGO WHERE DNI = ? ORDER BY Fecha_alta ASC LIMIT 1");
            $stmtNext->bind_param('s', $dni);
            $stmtNext->execute();
            $next = $stmtNext->get_result()->fetch_assoc();
            $stmtNext->close();
            if ($next) {
                $stmtSet = $_conexion->prepare("UPDATE METODO_PAGO SET Es_defecto = 1 WHERE ID_metodo = ?");
                $stmtSet->bind_param('i', $next['ID_metodo']);
                $stmtSet->execute();
                $stmtSet->close();
            }
        }

        respondJson(200, ['success' => true]);
    }

    // ── Establecer defecto ───────────────────
    if ($accion === 'defecto') {
        $id = (int)($data['id_metodo'] ?? 0);
        if ($id <= 0) respondJson(400, ['success' => false, 'error' => 'ID inválido']);

        // Quitar defecto a todos
        $stmtReset = $_conexion->prepare("UPDATE METODO_PAGO SET Es_defecto = 0 WHERE DNI = ?");
        $stmtReset->bind_param('s', $dni);
        $stmtReset->execute();
        $stmtReset->close();

        // Poner defecto al seleccionado
        $stmtSet = $_conexion->prepare("UPDATE METODO_PAGO SET Es_defecto = 1 WHERE ID_metodo = ? AND DNI = ?");
        $stmtSet->bind_param('is', $id, $dni);
        $stmtSet->execute();
        $stmtSet->close();

        // Actualizar Token_pago en USUARIO si es PayPal
        $stmtInfo = $_conexion->prepare("SELECT Tipo, Email_paypal FROM METODO_PAGO WHERE ID_metodo = ? AND DNI = ?");
        $stmtInfo->bind_param('is', $id, $dni);
        $stmtInfo->execute();
        $info = $stmtInfo->get_result()->fetch_assoc();
        $stmtInfo->close();
        if ($info && $info['Tipo'] === 'paypal' && $info['Email_paypal']) {
            $stmtTok = $_conexion->prepare("UPDATE USUARIO SET Token_pago = ? WHERE DNI = ?");
            $stmtTok->bind_param('ss', $info['Email_paypal'], $dni);
            $stmtTok->execute();
            $stmtTok->close();
        }

        respondJson(200, ['success' => true]);
    }

    respondJson(400, ['success' => false, 'error' => 'Acción no válida']);
}

respondJson(405, ['success' => false, 'error' => 'Método no permitido']);