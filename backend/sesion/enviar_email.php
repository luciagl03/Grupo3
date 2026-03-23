<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

function enviarConfirmacion($email, $token) {

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;

        $mail->Username   = 'AQUI SE PONE NUESTRO GMAIL PARA PRUEBAS';

        $mail->Password   = 'HAY QUE CREAR CLAVE APP DE NUESTRO EMAIL'; 

        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('no-reply@zpot.com', 'Zpot');
        $mail->addAddress($email);

        $link = "http://localhost/zpot/confirmar.php?token=$token";

        $mail->isHTML(true);
        $mail->Subject = 'Confirma tu cuenta en Zpot';
        $mail->Body = "
            <h2>Bienvenido a Zpot </h2>
            <p>Haz click en el siguiente enlace para confirmar tu cuenta:</p>
            <a href='$link'>Confirmar cuenta</a>
        ";

        $mail->send();

    } catch (Exception $e) {
        echo "ERROR AL ENVIAR EMAIL: " . $mail->ErrorInfo;
        exit;
    }
}