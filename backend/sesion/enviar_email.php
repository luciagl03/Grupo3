<?php

function enviarConfirmacion($email, $token) {
    $to = $email;
    $subject = "Confirma tu cuenta en Zpot";

    $link = "https://zpot.great-site.net/confirmar.php?token=$token";

    $message = "
    <html>
    <head>
      <title>Confirma tu cuenta en Zpot</title>
    </head>
    <body>
      <h2>Bienvenido a Zpot</h2>
      <p>Haz click en el siguiente enlace para confirmar tu cuenta:</p>
      <p><a href='$link'>Confirmar cuenta</a></p>
    </body>
    </html>
    ";

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";

    // Remitente genérico del servidor InfinityFree
    $headers .= "From: Zpot <noreply@zpot.great-site.net>\r\n";
    $headers .= "Reply-To: noreply@zpot.great-site.net\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    // Enviar correo y devolver resultado
    if(mail($to, $subject, $message, $headers)) {
        return ['success' => true];
    } else {
        return ['success' => false, 'error' => 'No se pudo enviar el email'];
    }
}