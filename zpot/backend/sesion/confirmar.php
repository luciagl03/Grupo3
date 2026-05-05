<?php
require 'conexion.php';

$token = $_GET['token'] ?? '';
$ok = false;

if ($token) {
    $stmt = $_conexion->prepare("SELECT * FROM USUARIO WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $stmt2 = $_conexion->prepare("UPDATE USUARIO SET confirmado = 1, token = NULL WHERE Email = ?");
        $stmt2->bind_param("s", $user['Email']);
        $stmt2->execute();
        $ok = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $ok ? 'Cuenta confirmada' : 'Enlace inválido'; ?> — Zpot</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,600;9..40,700;9..40,800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: #f1f0ea;
            color: #3a382f;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            -webkit-font-smoothing: antialiased;
        }
        .card {
            background: #fff;
            border-radius: 20px;
            padding: 3rem 2.5rem;
            max-width: 420px;
            width: 100%;
            text-align: center;
            box-shadow: 0 10px 30px rgba(58,56,47,0.08);
            border: 1px solid rgba(207,204,196,0.3);
        }
        .icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 1.75rem;
        }
        .icon-ok  { background: #f4dd49; }
        .icon-err { background: #fee2e2; }
        h1 {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin-bottom: 0.75rem;
        }
        p {
            color: #706e66;
            font-size: 0.95rem;
            line-height: 1.55;
            margin-bottom: 2rem;
        }
        .btn {
            display: inline-block;
            padding: 0.9rem 2rem;
            background: #3a382f;
            color: #fff;
            font-family: inherit;
            font-size: 1rem;
            font-weight: 700;
            border-radius: 12px;
            text-decoration: none;
            transition: background 0.15s, transform 0.15s;
        }
        .btn:hover { background: #1a1915; transform: translateY(-1px); }
        .btn-secondary {
            background: transparent;
            color: #3a382f;
            border: 1.5px solid #cfccc4;
            margin-top: 0.75rem;
            display: block;
        }
        .btn-secondary:hover { border-color: #3a382f; background: transparent; }
    </style>
</head>
<body>
    <div class="card">
        <?php if ($ok): ?>
            <div class="icon icon-ok">✓</div>
            <h1>Cuenta confirmada</h1>
            <p>Tu cuenta está activa. Ya puedes iniciar sesión y empezar a usar Zpot.</p>
            <a href="login.php" class="btn">Iniciar sesión</a>
        <?php else: ?>
            <div class="icon icon-err">✗</div>
            <h1>Enlace inválido</h1>
            <p>Este enlace de confirmación no es válido o ya fue usado.</p>
            <a href="login.php" class="btn">Ir al inicio</a>
        <?php endif; ?>
    </div>
</body>
</html>
