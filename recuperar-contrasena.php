<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Recuperar cuenta | Cuidado de tus Mascotas</title>

    <script>
        try {
            if (localStorage.getItem('tema-preferido') === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        } catch (e) {}
    </script>

    <!-- Primero los estilos compartidos; después los exclusivos de esta pantalla.
         filemtime solicita una versión nueva al guardar cada CSS. -->
    <link rel="stylesheet" href="assets/css/estilos.css?v=<?= filemtime(__DIR__ . '/assets/css/estilos.css') ?>">
    <link rel="stylesheet" href="assets/css/tema.css?v=<?= filemtime(__DIR__ . '/assets/css/tema.css') ?>">
    <!-- defer espera a que el HTML exista antes de conectar filtros y validaciones. -->
    <script src="assets/js/interfaz.js?v=<?= filemtime(__DIR__ . '/assets/js/interfaz.js') ?>" defer></script>
    <script src="assets/js/principal.js" defer></script>
</head>
<body class="pagina-login">

<!-- Fondo decorativo con ondas. -->
<div class="fondo-ondas" aria-hidden="true">
    <svg
        viewBox="0 0 1536 1024"
        preserveAspectRatio="none"
        xmlns="http://www.w3.org/2000/svg"
    >
        <defs>
            <linearGradient id="onda-celeste" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0%" stop-color="#80f2ff" stop-opacity=".8"/>
                <stop offset="50%" stop-color="#7eafff" stop-opacity=".25"/>
                <stop offset="100%" stop-color="#b69aff" stop-opacity=".65"/>
            </linearGradient>

            <linearGradient id="onda-violeta" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0%" stop-color="#b2cbff" stop-opacity=".65"/>
                <stop offset="50%" stop-color="#5843ff" stop-opacity=".15"/>
                <stop offset="100%" stop-color="#d4acff" stop-opacity=".75"/>
            </linearGradient>
        </defs>

        <g class="ondas-superiores">
            <path
                d="M-100 180
                   C180 480 250 -70 580 90
                   S1020 340 1210 -60
                   L1650 -100 L-100 -100 Z"
                fill="url(#onda-celeste)"
            />

            <path
                d="M-100 340
                   C180 310 220 600 530 420
                   S1000 80 1270 190
                   S1490 190 1650 80
                   L1650 -100 L-100 -100 Z"
                fill="url(#onda-violeta)"
            />

            <path
                d="M-100 180
                   C180 480 250 -70 580 90
                   S1020 340 1210 -60"
                class="borde-onda"
            />
        </g>

        <g class="ondas-centrales">
            <path
                d="M-100 530
                   C160 320 240 780 600 590
                   S1080 170 1400 390
                   S1540 460 1650 420
                   L1650 1100 L-100 1100 Z"
                fill="url(#onda-celeste)"
            />

            <path
                d="M-100 530
                   C160 320 240 780 600 590
                   S1080 170 1400 390
                   S1540 460 1650 420"
                class="borde-onda"
            />
        </g>

        <g class="ondas-inferiores">
            <path
                d="M-100 830
                   C100 470 300 850 620 710
                   S1020 1080 1300 920
                   S1500 850 1650 940
                   L1650 1100 L-100 1100 Z"
                fill="url(#onda-violeta)"
            />

            <path
                d="M-100 830
                   C100 470 300 850 620 710
                   S1020 1080 1300 920
                   S1500 850 1650 940"
                class="borde-onda"
            />
        </g>
    </svg>
</div>

<!-- Capa decorativa para las huellas animadas. -->
<div id="huellas" class="capa-huellas" aria-hidden="true"></div>

<main class="tarjeta-login">
    <header class="login-encabezado">
        <div class="login-logo" aria-hidden="true">🐾</div>

        <h1 class="titulo-animal">Recupera tu cuent<span class="letra-cola">a</span></h1>
        <p>
            Ingresa tu correo para solicitar un enlace
            de recuperación.
        </p>
    </header>

    <form id="formulario-recuperacion" method="post">
        <div class="campo">
            <label for="correo">Correo electrónico</label>

            <input
                type="email"
                id="correo"
                name="correo"
                placeholder="tu@correo.com"
                autocomplete="email"
                maxlength="254"
                required
            >
        </div>

        <button type="button" class="boton-login">
            Solicitar enlace
        </button>
    </form>

    <nav class="login-enlaces" aria-label="Opciones de acceso">
        <a href="login.php">Volver al inicio de sesión</a>
    </nav>

    <p class="login-pie">
        Cuidado y organización en un solo lugar
    </p>
</main>

</body>
</html>