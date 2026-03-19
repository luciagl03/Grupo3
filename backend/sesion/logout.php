<?php
session_start(); //Recogemos la sesion
$_SESSION = []; // Limpiamos el array de la sesión
session_destroy(); // Eliminamos todos los datos de la sesión del servidor PERO la cookie PHPSESSID sigue existiendo en el navedor (pero sin datos asociados)
header("location: login.php"); // Redigir al cliente al login
exit();