<?php
require 'conexion.php';

$token = $_GET['token'] ?? '';

if (!$token) {
    header("Location: login.php?error=token_invalido");
    exit;
}

$stmt = $_conexion->prepare("SELECT * FROM USUARIO WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: login.php?error=token_invalido");
    exit;
}

$user = $result->fetch_assoc();

// Comprobar expiración
if ($user['token_expires_at'] && strtotime($user['token_expires_at']) < time()) {
    header("Location: login.php?error=token_expirado");
    exit;
}

$stmt = $_conexion->prepare("UPDATE USUARIO SET confirmado = 1, token = NULL, token_expires_at = NULL WHERE Email = ?");
$stmt->bind_param("s", $user['Email']);
$stmt->execute();

header("Location: login.php?confirmed=1");
exit;