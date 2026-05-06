<?php
require_once __DIR__ . '/../sesion/conexion.php';
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$password = $data['password'] ?? '';

$userId = $_SESSION['dni'] ?? null;

if (!$userId) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

// comprobar contraseña
$stmt = $_conexion->prepare("SELECT Contrasena_encriptada FROM USUARIO WHERE DNI=?");
if (!$stmt) {
    echo json_encode(['error' => $_conexion->error]);
    exit;
}

$stmt->bind_param("s", $userId);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();

if (!$res || !password_verify($password, $res['Contrasena_encriptada'])) {
    echo json_encode(['error' => 'Contraseña incorrecta']);
    exit;
}

// eliminar usuario
$stmt = $_conexion->prepare("DELETE FROM USUARIO WHERE DNI=?");
if (!$stmt) {
    echo json_encode(['error' => $_conexion->error]);
    exit;
}

$stmt->bind_param("s", $userId);
$stmt->execute();

session_destroy();

echo json_encode(['success' => true]);