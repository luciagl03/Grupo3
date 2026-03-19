<?php
/**
 * Create parking spot (PLAZA) for the logged-in user.
 * POST JSON: direccion, foto?, ancho?, largo?, descripcion?, precio?
 * DNI is taken from session (user identified by email); client cannot set owner.
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

require_once __DIR__ . '/sesion/conexion.php';

$email = $_SESSION['usuario'];
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit;
}

$direccion  = isset($data['direccion']) ? trim($data['direccion']) : '';
$foto       = isset($data['foto']) ? trim($data['foto']) : '';
$ancho      = isset($data['ancho']) ? $data['ancho'] : null;
$largo      = isset($data['largo']) ? $data['largo'] : null;
$descripcion = isset($data['descripcion']) ? trim($data['descripcion']) : '';
$precio     = isset($data['precio']) ? $data['precio'] : null;
$escritura  = isset($data['escritura']) ? trim($data['escritura']) : '';

$direccion = $direccion !== '' ? htmlspecialchars($direccion, ENT_QUOTES, 'UTF-8') : '';
$foto = $foto !== '' ? htmlspecialchars($foto, ENT_QUOTES, 'UTF-8') : null;
$descripcion = $descripcion !== '' ? htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8') : null;
$escritura = $escritura !== '' ? htmlspecialchars($escritura, ENT_QUOTES, 'UTF-8') : null;

$errors = [];

if ($direccion === '') {
    $errors['direccion'] = 'La dirección es obligatoria';
}

if ($ancho !== null && $ancho !== '') {
    $anchoNum = is_numeric($ancho) ? (float) $ancho : null;
    if ($anchoNum === null || $anchoNum < 0) {
        $errors['ancho'] = 'Ancho debe ser un número mayor o igual a 0';
    } else {
        $ancho = $anchoNum;
    }
} else {
    $ancho = null;
}

if ($largo !== null && $largo !== '') {
    $largoNum = is_numeric($largo) ? (float) $largo : null;
    if ($largoNum === null || $largoNum < 0) {
        $errors['largo'] = 'Largo debe ser un número mayor o igual a 0';
    } else {
        $largo = $largoNum;
    }
} else {
    $largo = null;
}

if ($precio !== null && $precio !== '') {
    $precioNum = is_numeric($precio) ? (float) $precio : null;
    if ($precioNum === null || $precioNum < 0) {
        $errors['precio'] = 'Precio debe ser un número mayor o igual a 0';
    } else {
        $precio = $precioNum;
    }
} else {
    $precio = null;
}

if ($foto !== null && $foto !== '' && !filter_var($foto, FILTER_VALIDATE_URL)) {
    $errors['foto'] = 'La URL de la foto no es válida';
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

try {
    $stmt = $_conexion->prepare('SELECT DNI FROM USUARIO WHERE Email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        $stmt->close();
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Not authenticated']);
        exit;
    }
    $row = $result->fetch_assoc();
    $dni = $row['DNI'];
    $stmt->close();

    $stmt = $_conexion->prepare(
        'INSERT INTO PLAZA (DNI, Direccion, Foto, Ancho, Largo, Descripcion, Escritura, Precio) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param(
        'ssddsssd',
        $dni,
        $direccion,
        $foto,
        $ancho,
        $largo,
        $descripcion,
        $escritura,
        $precio
    );
    $stmt->execute();
    $id = (int) $_conexion->insert_id;
    $stmt->close();

    http_response_code(201);
    echo json_encode(['success' => true, 'id' => $id]);
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al guardar la plaza. Inténtalo de nuevo.']);
}
