<?php
/**
 * notificaciones_helper.php
 * Ubicación: backend/notificaciones/notificaciones_helper.php
 *
 * Incluir con require_once en los archivos que generan eventos:
 *   - confirmacion.php       → reserva_confirmada
 *   - resenas_api.php        → nueva_resena (al propietario de la plaza)
 *   - eliminar_reserva.php   → reserva_cancelada
 *
 * Uso:
 *   require_once __DIR__ . '/../notificaciones/notificaciones_helper.php';
 *   crearNotificacion($conexion, $dni, 'reserva_confirmada', 'Título', 'Mensaje', $id_ref);
 */

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
