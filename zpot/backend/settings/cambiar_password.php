<?php
require_once __DIR__ . '/../sesion/conexion.php';
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$current = $data['current'] ?? '';
$new     = $data['pass'] ?? '';

$userId = $_SESSION['dni'] ?? null;

if (!$userId) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

// SELECT
$stmt = $_conexion->prepare("SELECT Contrasena_encriptada FROM USUARIO WHERE DNI = ?");
if (!$stmt) {
    echo json_encode(['error' => $_conexion->error]);
    exit;
}

$stmt->bind_param("s", $userId);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();

if (!$res) {
    echo json_encode(['error' => 'Usuario no encontrado']);
    exit;
}

// verificar contraseña actual
if (!password_verify($current, $res['Contrasena_encriptada'])) {
    echo json_encode(['error' => 'Contraseña actual incorrecta']);
    exit;
}

// actualizar contraseña
$hash = password_hash($new, PASSWORD_DEFAULT);

$stmt = $_conexion->prepare("UPDATE USUARIO SET Contrasena_encriptada=? WHERE DNI=?");
if (!$stmt) {
    echo json_encode(['error' => $_conexion->error]);
    exit;
}

$stmt->bind_param("ss", $hash, $userId);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'No se actualizó']);
}