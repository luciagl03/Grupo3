<?php
session_start();
if (isset($_SESSION['usuario'])) {
    header('Location: app.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Zpot — Parking para personas reales</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,700;0,9..40,800;1,9..40,400&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --yellow: #f4dd49;
            --dark:   #3a382f;
            --bg:     #f1f0ea;
            --muted:  #706e66;
            --border: #e0ddd7;
            --radius: 14px;
        }

        html, body {
            height: 100%;
        }

        body {
            font-family: 'DM Sans', -apple-system, sans-serif;
            background: var(--bg);
            color: var(--dark);
            -webkit-font-smoothing: antialiased;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* NAV */
        .nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 2.5rem;
        }

        .nav-logo img {
            height: 40px;
            width: auto;
            display: block;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .btn-ghost {
            padding: 0.55rem 1.1rem;
            border-radius: 999px;
            font-family: inherit;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--dark);
            text-decoration: none;
            border: 1.5px solid transparent;
            transition: background 0.15s, border-color 0.15s;
        }

        .btn-ghost:hover {
            background: rgba(58, 56, 47, 0.07);
        }

        .btn-nav-primary {
            padding: 0.55rem 1.25rem;
            border-radius: 999px;
            font-family: inherit;
            font-size: 0.9rem;
            font-weight: 700;
            color: #fff;
            background: var(--dark);
            text-decoration: none;
            border: none;
            transition: background 0.15s, transform 0.15s;
        }

        .btn-nav-primary:hover {
            background: #1a1915;
            transform: translateY(-1px);
        }

        /* HERO */
        .hero {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 1.5rem 5rem;
        }

        .hero-inner {
            max-width: 680px;
            width: 100%;
            text-align: center;
        }

        .hero-tag {
            display: inline-block;
            background: var(--yellow);
            color: var(--dark);
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 0.35rem 1rem;
            border-radius: 999px;
            margin-bottom: 2rem;
        }

        .hero-title {
            font-size: clamp(2.6rem, 6vw, 4rem);
            font-weight: 800;
            line-height: 1.05;
            letter-spacing: -0.03em;
            margin-bottom: 1.25rem;
        }

        .hero-title em {
            font-style: normal;
            position: relative;
            display: inline-block;
        }

        .hero-title em::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 2px;
            width: 100%;
            height: 6px;
            background: var(--yellow);
            border-radius: 3px;
            z-index: -1;
        }

        .hero-sub {
            font-size: 1.15rem;
            color: var(--muted);
            line-height: 1.6;
            max-width: 480px;
            margin: 0 auto 2.5rem;
        }

        .hero-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .btn-cta-primary {
            display: inline-block;
            padding: 0.9rem 2rem;
            background: var(--dark);
            color: #fff;
            font-family: inherit;
            font-size: 1rem;
            font-weight: 700;
            border-radius: var(--radius);
            text-decoration: none;
            transition: background 0.15s, transform 0.15s, box-shadow 0.15s;
        }

        .btn-cta-primary:hover {
            background: #1a1915;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(58, 56, 47, 0.18);
        }

        .btn-cta-secondary {
            display: inline-block;
            padding: 0.9rem 2rem;
            background: #fff;
            color: var(--dark);
            font-family: inherit;
            font-size: 1rem;
            font-weight: 600;
            border-radius: var(--radius);
            text-decoration: none;
            border: 1.5px solid var(--border);
            transition: border-color 0.15s, transform 0.15s;
        }

        .btn-cta-secondary:hover {
            border-color: var(--dark);
            transform: translateY(-2px);
        }

        /* FEATURES */
        .features {
            border-top: 1px solid var(--border);
            background: #fff;
            padding: 3rem 2.5rem;
        }

        .features-inner {
            max-width: 900px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2.5rem;
        }

        .feature {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .feature-dot {
            width: 10px;
            height: 10px;
            background: var(--yellow);
            border-radius: 50%;
            margin-bottom: 0.25rem;
        }

        .feature-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--dark);
        }

        .feature-desc {
            font-size: 0.9rem;
            color: var(--muted);
            line-height: 1.55;
        }

        /* MOBILE */
        @media (max-width: 600px) {
            .nav { padding: 1rem 1.25rem; }
            .nav-logo img { height: 32px; }

            .hero { padding: 2rem 1.25rem 4rem; }
            .hero-tag { margin-bottom: 1.25rem; }

            .btn-cta-primary, .btn-cta-secondary {
                width: 100%;
                text-align: center;
            }

            .hero-actions { flex-direction: column; }

            .features { padding: 2.5rem 1.25rem; }
            .features-inner { grid-template-columns: 1fr; gap: 1.75rem; }
        }
    </style>
</head>
<body>

    <nav class="nav">
        <a class="nav-logo" href="index.php">
            <img src="../frontend/assets/images/logo.png" alt="Zpot">
        </a>
        <div class="nav-links">
            <a href="sesion/login.php" class="btn-ghost">Iniciar sesión</a>
            <a href="sesion/signup.html" class="btn-nav-primary">Crear cuenta</a>
        </div>
    </nav>

    <section class="hero">
        <div class="hero-inner">
            <span class="hero-tag">Parking entre personas</span>
            <h1 class="hero-title">
                Tu plaza libre.<br>
                El parking de <em>otro</em>.
            </h1>
            <p class="hero-sub">
                Alquila tu plaza cuando no la uses o reserva la de alguien cerca de donde vayas. Sin intermediarios raros.
            </p>
            <div class="hero-actions">
                <a href="sesion/signup.html" class="btn-cta-primary">Empieza gratis</a>
                <a href="sesion/login.php" class="btn-cta-secondary">Ya tengo cuenta</a>
            </div>
        </div>
    </section>

    <footer class="features">
        <div class="features-inner">
            <div class="feature">
                <div class="feature-dot"></div>
                <span class="feature-title">Encuentra aparcamiento</span>
                <p class="feature-desc">Busca plazas disponibles cerca de donde necesites. Filtra por tipo, extras y precio.</p>
            </div>
            <div class="feature">
                <div class="feature-dot"></div>
                <span class="feature-title">Reserva en segundos</span>
                <p class="feature-desc">Elige fechas, paga de forma segura con PayPal y recibe la confirmación al momento.</p>
            </div>
            <div class="feature">
                <div class="feature-dot"></div>
                <span class="feature-title">Publica la tuya</span>
                <p class="feature-desc">¿Tienes una plaza libre? Publícala en minutos y empieza a sacarle rentabilidad.</p>
            </div>
        </div>
    </footer>

</body>
</html>
