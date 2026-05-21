<?php

function crearNotificacion($conexion, $dni, $tipo, $titulo, $mensaje, $id_ref = null) {
    try {
        $stmt = $conexion->prepare(
            "INSERT INTO NOTIFICACION (DNI, Tipo, Titulo, Mensaje, ID_ref) VALUES (?, ?, ?, ?, ?)"
        );
        if (!$stmt) {
            error_log("Error preparando notificación: " . $conexion->error);
            return false;
        }
        $stmt->bind_param('ssssi', $dni, $tipo, $titulo, $mensaje, $id_ref);
        $result = $stmt->execute();
        if (!$result) {
            error_log("Error ejecutando notificación: " . $stmt->error);
        }
        $stmt->close();
        return $result;
    } catch (Exception $e) {
        // No interrumpir el flujo principal si falla la notificación
        error_log("Excepción en crearNotificacion: " . $e->getMessage());
        return false;
    }
}
