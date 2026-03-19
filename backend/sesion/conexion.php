<?php

    $_servidor = "localhost";
    $_usuario = "admin";
    $_contrasena = "admin";
    $_bd = "zpot_bd";
 
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $_conexion = new mysqli($_servidor, $_usuario, $_contrasena, $_bd);

    if($_conexion->connect_error){
        die("Error en la conexión: ".$_conexion->connect_error);
    }
?>