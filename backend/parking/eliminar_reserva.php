<?php
session_start();
require_once '../sesion/conexion.php';

if (!isset($_SESSION['dni'])) {
    header("Location: ../sesion/login.php");
    exit;
}

$id_reserva = (int) $_POST['id_reserva'];
$dni = $_SESSION['dni'];

// Obtener información de la reserva antes de eliminarla para notificar al propietario
$stmtInfo = $_conexion->prepare(
    "SELECT r.ID_plaza, r.Fecha, r.Estado, p.DNI AS dni_propietario, p.Direccion
     FROM RESERVA r
     JOIN PLAZA p ON r.ID_plaza = p.ID_plaza
     WHERE r.ID_reserva = ? AND r.DNI = ?"
);
$stmtInfo->bind_param("is", $id_reserva, $dni);
$stmtInfo->execute();
$reservaInfo = $stmtInfo->get_result()->fetch_assoc();
$stmtInfo->close();

// Eliminar la reserva
$sql = "DELETE FROM RESERVA WHERE ID_reserva = ? AND DNI = ?";
$stmt = $_conexion->prepare($sql);

if (!$stmt) {
    die("Error SQL: " . $_conexion->error);
}

$stmt->bind_param("is", $id_reserva, $dni);
$stmt->execute();
$stmt->close();

// Notificar al propietario si la reserva estaba confirmada
if ($reservaInfo && $reservaInfo['Estado'] === 'confirmada') {
    require_once __DIR__ . '/../notificaciones/notificaciones_helper.php';
    $direccion = $reservaInfo['Direccion'] ?? 'tu plaza';
    $fecha = date('d/m/Y', strtotime($reservaInfo['Fecha']));
    
    crearNotificacion(
        $_conexion,
        $reservaInfo['dni_propietario'],
        'reserva_cancelada',
        'Reserva cancelada',
        'Se ha cancelado una reserva en ' . $direccion . ' para el ' . $fecha . '.',
        $reservaInfo['ID_plaza']
    );
}

header("Location: ../parking/mis_reservas.php");
exit;
