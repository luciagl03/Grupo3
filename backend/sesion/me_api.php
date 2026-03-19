<?php
/**
 * Returns the current logged-in user from session, or 401.
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$email = $_SESSION['usuario'];
require_once __DIR__ . '/conexion.php';

$stmt = $_conexion->prepare('SELECT DNI, Nombre, Apellidos, Email FROM USUARIO WHERE Email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    session_destroy();
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}
$user = $result->fetch_assoc();
$stmt->close();

echo json_encode([
    'dni' => $user['DNI'],
    'nombre' => $user['Nombre'],
    'apellidos' => $user['Apellidos'],
    'email' => $user['Email'],
    'displayName' => trim($user['Nombre'] . ' ' . $user['Apellidos'])
]);
