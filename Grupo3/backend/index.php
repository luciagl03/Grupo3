<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: sesion/login.php');
    exit;
}
header('Location: app.html');
exit;
