 <?php
require 'conexion.php';

$token = $_GET['token'] ?? '';

if (!$token) {
    echo "Token inválido";
    exit;
}

$stmt = $_conexion->prepare("SELECT * FROM USUARIO WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Token inválido";
    exit;
}

$user = $result->fetch_assoc();

$stmt = $_conexion->prepare("UPDATE USUARIO SET confirmado = 1, token = NULL WHERE Email = ?");
$stmt->bind_param("s", $user['Email']);
$stmt->execute();

echo "Cuenta confirmada correctamente. Ya puedes iniciar sesión.";