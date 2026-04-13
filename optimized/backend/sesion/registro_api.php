<?php
// ------------------------------------------------------------
// Registration API:
// - validates signup payload
// - creates a non-confirmed user
// - sends confirmation email with token
// ------------------------------------------------------------
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

require 'conexion.php';

// Sends JSON response with status code and stops execution.
function respondJson($status, $payload) {
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

// Basic input sanitation for text fields.
function cleanText($value) {
    return htmlspecialchars(trim((string) $value), ENT_QUOTES, 'UTF-8');
}

// Handles the confirmation email delivery.
function enviarConfirmacion($email, $token) {
    $to = $email;
    $subject = "Confirma tu cuenta en Zpot";
    $link = "https://zpot.great-site.net/confirmar.php?token=$token";
    $message = "
    <html>
    <head>
      <title>Confirma tu cuenta en Zpot</title>
    </head>
    <body>
      <h2>Bienvenido a Zpot</h2>
      <p>Haz click en el siguiente enlace para confirmar tu cuenta:</p>
      <p><a href='$link'>Confirmar cuenta</a></p>
    </body>
    </html>
    ";

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Zpot <noreply@zpot.great-site.net>\r\n";
    $headers .= "Reply-To: noreply@zpot.great-site.net\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    if (mail($to, $subject, $message, $headers)) {
        return ['success' => true];
    }

    return ['success' => false, 'error' => 'No se pudo enviar el email'];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respondJson(405, ['success' => false, 'error' => 'Method not allowed']);
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    respondJson(400, ['success' => false, 'error' => 'Invalid JSON']);
}

// -------- Input normalization --------
$dni = cleanText($data['dni'] ?? '');
$nombre = cleanText($data['nombre'] ?? '');
$apellidos = cleanText($data['apellidos'] ?? '');
$email = cleanText($data['email'] ?? '');
$contrasena = $data['contrasena'] ?? '';

$errors = [];

// -------- Validation rules --------
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
    respondJson(422, ['success' => false, 'errors' => $errors]);
}

try {
    // -------- Uniqueness check (email) --------
    $stmt = $_conexion->prepare('SELECT DNI FROM USUARIO WHERE Email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $stmt->close();
        respondJson(409, [
            'success' => false,
            'error' => 'Este email ya está registrado',
            'errors' => ['email' => 'Este email ya está registrado']
        ]);
    }
    $stmt->close();

    // -------- User creation --------
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

    // -------- Confirmation email --------
    $mailResult = enviarConfirmacion($email, $token);

    if (!$mailResult['success']) {
        respondJson(500, [
            'success' => false,
            'error' => 'Usuario registrado pero fallo al enviar email: ' . $mailResult['error']
        ]);
    }

    // -------- Success response --------
    respondJson(201, [
        'success' => true,
        'message' => 'Usuario registrado. Revisa tu email para confirmar la cuenta.'
    ]);

} catch (mysqli_sql_exception $e) {
    if ($_conexion->errno === 1062) {
        respondJson(409, [
            'success' => false,
            'error' => 'DNI o email ya registrado',
            'errors' => ['dni' => 'Este DNI ya está registrado']
        ]);
    } else {
        respondJson(500, [
            'success' => false,
            'error' => 'Error al registrar. Inténtalo de nuevo.',
            'exception' => $e->getMessage()
        ]);
    }
}