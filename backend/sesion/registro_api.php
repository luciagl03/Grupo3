<?php
/**
 * Registration API for Zpot.
 * Accepts POST JSON: dni, nombre, apellidos, email, contrasena
 * Returns JSON and sets session on success.
 */
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

require 'conexion.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit;
}

$dni         = isset($data['dni']) ? trim($data['dni']) : '';
$nombre      = isset($data['nombre']) ? trim($data['nombre']) : '';
$apellidos   = isset($data['apellidos']) ? trim($data['apellidos']) : '';
$email       = isset($data['email']) ? trim($data['email']) : '';
$contrasena  = isset($data['contrasena']) ? $data['contrasena'] : '';

$dni        = htmlspecialchars($dni, ENT_QUOTES, 'UTF-8');
$nombre     = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
$apellidos  = htmlspecialchars($apellidos, ENT_QUOTES, 'UTF-8');
$email      = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');

$errors = [];

if ($dni === '') {
    $errors['dni'] = 'Inserta un DNI';
} elseif (!preg_match('/^[0-9]{8}[A-Za-z]$/', $dni)) {
    $errors['dni'] = 'Formato de DNI incorrecto (8 dígitos + 1 letra)';
}

if ($nombre === '') {
    $errors['nombre'] = 'Inserta un nombre';
}

if ($apellidos === '') {
    $errors['apellidos'] = 'Inserta los apellidos';
}

if ($email === '') {
    $errors['email'] = 'Inserta un email';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Email no válido';
}

if ($contrasena === '') {
    $errors['contrasena'] = 'Inserta una contraseña';
} elseif (strlen($contrasena) < 8) {
    $errors['contrasena'] = 'Debe tener al menos 8 caracteres';
} elseif (!preg_match('/[0-9]/', $contrasena)) {
    $errors['contrasena'] = 'Debe contener al menos un número';
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
    if ($result->num_rows > 0) {
        $stmt->close();
        http_response_code(409);
        echo json_encode(['success' => false, 'error' => 'Este email ya está registrado', 'errors' => ['email' => 'Este email ya está registrado']]);
        exit;
    }
    $stmt->close();

    $hash = password_hash($contrasena, PASSWORD_DEFAULT);
    $stmt = $_conexion->prepare('INSERT INTO USUARIO (DNI, Nombre, Apellidos, Direccion, Foto, Telefono, Email, Contrasena_encriptada) VALUES (?, ?, ?, NULL, NULL, NULL, ?, ?)');
    $stmt->bind_param('sssss', $dni, $nombre, $apellidos, $email, $hash);
    $stmt->execute();
    $stmt->close();

    session_start();
    $_SESSION['usuario'] = $email;

    http_response_code(201);
    echo json_encode(['success' => true]);
} catch (mysqli_sql_exception $e) {
    if ($_conexion->errno === 1062) {
        http_response_code(409);
        echo json_encode(['success' => false, 'error' => 'DNI o email ya registrado', 'errors' => ['dni' => 'Este DNI ya está registrado']]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error al registrar. Inténtalo de nuevo.']);
    }
}
