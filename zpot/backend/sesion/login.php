<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
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
                    <div class="password-wrapper">
                        <input type="password" id="contrasena" name="contrasena" autocomplete="current-password" class="<?php echo $err_contrasena ? 'error' : ''; ?>">
                        <button type="button" class="toggle-password" aria-label="Mostrar contraseña" onclick="togglePasswordVisibility('contrasena', this)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-icon">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                    <?php if ($err_contrasena): ?><span class="field-error" aria-live="polite"><?php echo htmlspecialchars($err_contrasena); ?></span><?php endif; ?>
                </div>
                <button type="submit" class="btn btn-primary">Iniciar sesión</button>
            </form>

            <div class="divider">¿No tienes cuenta?</div>
            <a href="signup.html" class="btn btn-secondary">Crear cuenta</a>
        </main>
    </div>

    <script>
        function togglePasswordVisibility(inputId, button) {
            const input = document.getElementById(inputId);
            const eyeIcon = button.querySelector('.eye-icon');
            
            if (input.type === 'password') {
                input.type = 'text';
                button.setAttribute('aria-label', 'Ocultar contraseña');
                eyeIcon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
            } else {
                input.type = 'password';
                button.setAttribute('aria-label', 'Mostrar contraseña');
                eyeIcon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
            }
        }
    </script>
</body>
</html>
