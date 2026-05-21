<?php
/**
 * notificaciones_helper.php
 * Ubicación: backend/notificaciones/notificaciones_helper.php
 *
 * Tipos de notificaciones implementadas:
 *   - plaza_publicada              → Propietario: plaza publicada en el mapa
 *   - reserva_pendiente            → Inquilino: reserva creada, pendiente de pago
 *   - reserva_confirmada           → Inquilino: reserva pagada y confirmada
 *   - nueva_reserva_propietario    → Propietario: nueva reserva recibida
 *   - reserva_cancelada            → Propietario: reserva cancelada por inquilino
 *   - nueva_consulta               → Propietario: mensaje de contacto sobre plaza
 *   - nuevo_mensaje_reserva        → Ambos: mensaje en chat de reserva existente
 *   - nueva_resena                 → Propietario: nueva reseña recibida
 *
 * Uso:
 *   require_once __DIR__ . '/../notificaciones/notificaciones_helper.php';
 *   crearNotificacion($conexion, $dni, 'tipo_notificacion', 'Título', 'Mensaje', $id_ref);
 */

function crearNotificacion($conexion, $dni, $tipo, $titulo, $mensaje, $id_ref = null) {
    try {
        $stmt = $conexion->prepare(
            "INSERT INTO NOTIFICACION (DNI, Tipo, Titulo, Mensaje, ID_ref) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('ssssi', $dni, $tipo, $titulo, $mensaje, $id_ref);
        $stmt->execute();
        $stmt->close();
    } catch (Exception $e) {
        // No interrumpir el flujo principal si falla la notificación
    }
}
