<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

require 'conexion.php';
require 'enviar_email.php'; // Asegúrate de que enviar_email.php tenga la función mail()

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit;
}

// Limpieza de datos
$dni = htmlspecialchars(trim($data['dni'] ?? ''), ENT_QUOTES, 'UTF-8');
$nombre = htmlspecialchars(trim($data['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
$apellidos = htmlspecialchars(trim($data['apellidos'] ?? ''), ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars(trim($data['email'] ?? ''), ENT_QUOTES, 'UTF-8');
$contrasena = $data['contrasena'] ?? '';

$errors = [];

// Validaciones
if ($dni === '') $errors['dni'] = 'Inserta un DNI';
elseif (!preg_match('/^[0-9]{8}[A-Za-z]$/', $dni)) $errors['dni'] = 'Formato de DNI incorrecto (8 dígitos + 1 letra)';

if ($nombre === '') $errors['nombre'] = 'Inserta un nombre';
if ($apellidos === '') $errors['apellidos'] = 'Inserta los apellidos';
if ($email === '') $errors['email'] = 'Inserta un email';
elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Email no válido';

if ($contrasena === '') $errors['contrasena'] = 'Inserta una contraseña';
elseif (strlen($contrasena) < 8) $errors['contrasena'] = 'Debe tener al menos 8 caracteres';
elseif (!preg_match('/[0-9]/', $contrasena)) $errors['contrasena'] = 'Debe contener al menos un número';

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

try {
    // Verificar si el email ya existe
    $stmt = $_conexion->prepare('SELECT DNI FROM USUARIO WHERE Email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $stmt->close();
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'error' => 'Este email ya está registrado',
            'errors' => ['email' => 'Este email ya está registrado']
        ]);
        exit;
    }
    $stmt->close();

    // Insertar nuevo usuario
    $token = bin2hex(random_bytes(32));
    $hash = password_hash($contrasena, PASSWORD_DEFAULT);

    $stmt = $_conexion->prepare('
        INSERT INTO USUARIO 
        (DNI, Nombre, Apellidos, Direccion, Foto, Telefono, Email, Contrasena_encriptada, token, confirmado) 
        VALUES (?, ?, ?, NULL, NULL, NULL, ?, ?, ?, 0)
    ');
    $stmt->bind_param('ssssss', $dni, $nombre, $apellidos, $email, $hash, $token);
    $stmt->execute();
    $stmt->close();

    // Enviar email de confirmación usando mail()
    $mailResult = enviarConfirmacion($email, $token);

    if (!$mailResult['success']) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Usuario registrado pero fallo al enviar email: ' . $mailResult['error']
        ]);
        exit;
    }

    // Respuesta exitosa
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Usuario registrado. Revisa tu email para confirmar la cuenta.'
    ]);

} catch (mysqli_sql_exception $e) {
    if ($_conexion->errno === 1062) {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'error' => 'DNI o email ya registrado',
            'errors' => ['dni' => 'Este DNI ya está registrado']
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al registrar. Inténtalo de nuevo.',
            'exception' => $e->getMessage()
        ]);
    }
}