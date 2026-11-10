<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/conexion.php';

$mensajeTexto = '';
$mensajeTipo = '';
$valores = [
    'nombre' => '',
    'correo' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre       = trim((string) ($_POST['nombre'] ?? ''));
    $correo       = trim((string) ($_POST['correo'] ?? ''));
    $contrasena   = (string) ($_POST['contrasena'] ?? '');
    $confirmacion = (string) ($_POST['confirmar_contrasena'] ?? '');

    $valores['nombre'] = $nombre;
    $valores['correo'] = $correo;

    $correoValido = filter_var($correo, FILTER_VALIDATE_EMAIL);

    if (!$pdo) {
        $mensajeTexto = $errorConexion ?? 'No se pudo conectar con la base de datos.';
        $mensajeTipo = 'error';
    } elseif ($nombre === '' || mb_strlen($nombre) > 100) {
        $mensajeTexto = 'Escribe tu nombre completo (sin estar compuesto solo por espacios).';
        $mensajeTipo = 'error';
    } elseif ($correoValido === false || mb_strlen($correo) > 254) {
        $mensajeTexto = 'Ingresa un correo electrónico válido.';
        $mensajeTipo = 'error';
    } elseif (mb_strlen($contrasena) < 8) {
        $mensajeTexto = 'La contraseña debe tener al menos 8 caracteres.';
        $mensajeTipo = 'error';
    } elseif ($contrasena !== $confirmacion) {
        $mensajeTexto = 'Las contraseñas no coinciden.';
        $mensajeTipo = 'error';
    } else {
        try {
            $stmtVerificar = $pdo->prepare(
                "SELECT id FROM usuarios WHERE correo = :correo LIMIT 1"
            );
            $stmtVerificar->execute([':correo' => $correo]);

            if ($stmtVerificar->fetch()) {
                $mensajeTexto = 'Ya existe una cuenta registrada con ese correo electrónico.';
                $mensajeTipo = 'error';
            } else {
                $hash = password_hash($contrasena, PASSWORD_DEFAULT);
                $stmtInsertar = $pdo->prepare(
                    "INSERT INTO usuarios (nombre, correo, contrasena, rol, estado) VALUES (:nombre, :correo, :contrasena, :rol, :estado)"
                );
                $stmtInsertar->execute([
                    ':nombre'     => $nombre,
                    ':correo'     => $correo,
                    ':contrasena' => $hash,
                    ':rol'        => 'cliente',
                    ':estado'     => 'Activo',
                ]);

                $nuevoId = (int) $pdo->lastInsertId();
                session_regenerate_id(true);
                $_SESSION['usuario_id']     = $nuevoId;
                $_SESSION['usuario_nombre'] = $nombre;
                $_SESSION['usuario_correo'] = $correo;
                $_SESSION['usuario_rol']    = 'cliente';

                header('Location: cliente/panel.php');
                exit;
            }
        } catch (PDOException $e) {
            error_log('Error al registrar cuenta en registro.php: ' . $e->getMessage());
            $mensajeTexto = 'Ocurrió un error al crear tu cuenta. Inténtalo nuevamente.';
            $mensajeTipo = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Crear cuenta | Cuidado de tus Mascotas</title>

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
<body
    class="pagina-login"
    <?= $mensajeTexto !== '' ? 'data-toast-mensaje="' . htmlspecialchars($mensajeTexto, ENT_QUOTES, 'UTF-8') . '" data-toast-tipo="' . htmlspecialchars($mensajeTipo, ENT_QUOTES, 'UTF-8') . '"' : '' ?>
>

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

       <h1 class="titulo-animal">Crea tu cuent<span class="letra-cola">a</span></h1>
        <p>Un espacio para cuidar de tus mascotas</p>
    </header>

    <form id="formulario-registro" method="post" action="registro.php">
        <div class="campo">
            <label for="nombre">Nombre completo</label>

            <input
                type="text"
                id="nombre"
                name="nombre"
                placeholder="Escribe tu nombre"
                autocomplete="name"
                maxlength="100"
                value="<?= htmlspecialchars($valores['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                required
            >
        </div>

        <div class="campo">
            <label for="correo">Correo electrónico</label>

            <input
                type="email"
                id="correo"
                name="correo"
                placeholder="tu@correo.com"
                autocomplete="email"
                maxlength="254"
                value="<?= htmlspecialchars($valores['correo'], ENT_QUOTES, 'UTF-8') ?>"
                required
            >
        </div>

        <div class="campo">
            <label for="contrasena">Contraseña</label>

            <input
                type="password"
                id="contrasena"
                name="contrasena"
                placeholder="Mínimo 8 caracteres"
                autocomplete="new-password"
                minlength="8"
                aria-describedby="ayuda-contrasena"
                required
            >

            <small id="ayuda-contrasena" class="campo-ayuda">
                Utiliza al menos 8 caracteres.
            </small>
        </div>

        <div class="campo">
            <label for="confirmar-contrasena">
                Confirmar contraseña
            </label>

            <input
                type="password"
                id="confirmar-contrasena"
                name="confirmar_contrasena"
                placeholder="Repite tu contraseña"
                autocomplete="new-password"
                minlength="8"
                required
            >
        </div>

        <button type="submit" class="boton-login">
            Crear cuenta
        </button>
    </form>

    <nav class="login-enlaces" aria-label="Opciones de acceso">
        <a href="login.php">Ya tengo cuenta. Iniciar sesión</a>
    </nav>

    <p class="login-pie">
        Cuidado y organización en un solo lugar
    </p>
</main>

</body>
</html>
