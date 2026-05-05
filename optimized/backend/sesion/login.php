<?php
require "conexion.php";

$err_email = null;
$err_contrasena = null;
$global_error = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $tmp_email = trim($_POST["email"] ?? '');
    $tmp_contrasena = $_POST["contrasena"] ?? '';

    if ($tmp_email === '') {
        $err_email = "Introduce un email";
    }
    if ($tmp_contrasena === '') {
        $err_contrasena = "Introduce una contraseña";
    }

    if (!$err_email && !$err_contrasena) {
        $stmt = $_conexion->prepare("SELECT * FROM USUARIO WHERE Email = ?");
        $stmt->bind_param("s", $tmp_email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $stmt->close();

        if ($resultado->num_rows === 0) {
            $global_error = "El email no existe en la base de datos";
        } else {
            $info_usuario = $resultado->fetch_assoc();
            if (!password_verify($tmp_contrasena, $info_usuario["Contrasena_encriptada"])) {
                $global_error = "La contraseña no coincide";
            } else {
                if ($info_usuario["confirmado"] == 0) {
                    $global_error = "Debes confirmar tu email antes de iniciar sesión";
                } else {
                    session_start();
                    $_SESSION["usuario"] = $tmp_email;
                    $_SESSION["dni"] = $info_usuario["DNI"];
                    header("Location: ../index.php");
                    exit;
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#ffffff">
    <title>Iniciar sesión — Zpot</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="signup.css">
</head>
<body class="auth-page">
    <div class="layout">
        <main class="card">
            <div class="logo"><a href="../index.php"><img src="../../frontend/assets/images/logo.png" alt="Zpot"></a></div>
            <h1 class="headline">Iniciar sesión</h1>
            <p class="support">Entra en tu cuenta para reservar o publicar plazas.</p>

            <?php if (isset($_GET['confirmed'])): ?>
                <div class="global-success" role="alert">
                    ✓ &nbsp;¡Cuenta confirmada! Ya puedes iniciar sesión.
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="global-error" role="alert">
                    <?php
                    $msgs = [
                        'token_invalido' => 'El enlace de confirmación no es válido.',
                        'token_expirado' => 'El enlace ha caducado (24h). Regístrate de nuevo.',
                    ];
                    echo htmlspecialchars($msgs[$_GET['error']] ?? 'Ha ocurrido un error.');
                    ?>
                </div>
            <?php endif; ?>

            <?php if ($global_error): ?>
                <div class="global-error" role="alert"><?php echo htmlspecialchars($global_error); ?></div>
            <?php endif; ?>

            <form action="" method="post">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="<?php echo $err_email ? 'error' : ''; ?>" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" autocomplete="email">
                    <?php if ($err_email): ?><span class="field-error" aria-live="polite"><?php echo htmlspecialchars($err_email); ?></span><?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="contrasena">Contraseña</label>
                    <input type="password" id="contrasena" name="contrasena" autocomplete="current-password" class="<?php echo $err_contrasena ? 'error' : ''; ?>">
                    <?php if ($err_contrasena): ?><span class="field-error" aria-live="polite"><?php echo htmlspecialchars($err_contrasena); ?></span><?php endif; ?>
                </div>
                <button type="submit" class="btn btn-primary">Iniciar sesión</button>
            </form>

            <div class="divider">¿No tienes cuenta?</div>
            <a href="signup.html" class="btn btn-secondary">Crear cuenta</a>
        </main>
    </div>
</body>
</html>
