<?php

header('Content-Type: application/json; charset=utf-8');

require 'conexion.php';

function respondJson($status, $payload) {
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

function cleanText($value) {
    return htmlspecialchars(trim((string) $value), ENT_QUOTES, 'UTF-8');
}

function enviarConfirmacion($email, $token) {
    $link = "https://zpot.infinityfreeapp.com/backend/sesion/confirmar.php?token=$token";

    $payload = json_encode([
        'sender'      => ['name' => 'Zpot', 'email' => 'zpot.ofc@gmail.com'],
        'to'          => [['email' => $email]],
        'subject'     => 'Confirma tu cuenta en Zpot',
        'htmlContent' => "
        <html>
        <body style='font-family:sans-serif;color:#3a382f;padding:2rem;'>
          <h2>Bienvenido a Zpot</h2>
          <p>Haz clic en el siguiente enlace para confirmar tu cuenta:</p>
          <p style='margin:2rem 0;'>
            <a href='$link' style='background:#f4dd49;color:#3a382f;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:700;'>
              Confirmar cuenta
            </a>
          </p>
          <p style='color:#888;font-size:0.85rem;'>Si no te has registrado en Zpot, ignora este mensaje.</p>
        </body>
        </html>"
    ]);

    $ch = curl_init('https://api.brevo.com/v3/smtp/email');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'api-key: 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',
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_TIMEOUT        => 10,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 201) {
        return ['success' => true];
    }

    $body = json_decode($response, true);
    return ['success' => false, 'error' => $body['message'] ?? 'Error enviando email'];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respondJson(405, ['success' => false, 'error' => 'Method not allowed']);
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    respondJson(400, ['success' => false, 'error' => 'Invalid JSON']);
}

$dni = cleanText($data['dni'] ?? '');
$nombre = cleanText($data['nombre'] ?? '');
$apellidos = cleanText($data['apellidos'] ?? '');
$email = cleanText($data['email'] ?? '');
$contrasena = $data['contrasena'] ?? '';

$errors = [];

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

    $mailResult = enviarConfirmacion($email, $token);

    if (!$mailResult['success']) {
        respondJson(500, [
            'success' => false,
            'error' => 'Usuario registrado pero fallo al enviar email: ' . $mailResult['error']
        ]);
    }

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