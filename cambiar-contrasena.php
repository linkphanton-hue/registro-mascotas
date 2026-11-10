<?php
require_once __DIR__ . '/includes/sesion.php';
requerirRol(null, 'login.php');

require_once __DIR__ . '/includes/conexion.php';

$mensajeTexto = '';
$mensajeTipo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actual    = (string) ($_POST['contrasena_actual'] ?? '');
    $nueva     = (string) ($_POST['nueva_contrasena'] ?? '');
    $confirmar = (string) ($_POST['confirmar_contrasena'] ?? '');

    if (!$pdo) {
        $mensajeTexto = $errorConexion ?? 'No se pudo conectar con la base de datos.';
        $mensajeTipo = 'error';
    } elseif ($actual === '' || $nueva === '' || $confirmar === '') {
        $mensajeTexto = 'Completa todos los campos de contraseña.';
        $mensajeTipo = 'error';
    } elseif (mb_strlen($nueva) < 8) {
        $mensajeTexto = 'La nueva contraseña debe tener al menos 8 caracteres.';
        $mensajeTipo = 'error';
    } elseif ($nueva !== $confirmar) {
        $mensajeTexto = 'La nueva contraseña y su confirmación no coinciden.';
        $mensajeTipo = 'error';
    } elseif ($nueva === $actual) {
        $mensajeTexto = 'La nueva contraseña debe ser diferente de la actual.';
        $mensajeTipo = 'error';
    } else {
        try {
            $stmtUsuario = $pdo->prepare(
                "SELECT id, contrasena FROM usuarios WHERE id = :id AND estado = :estado LIMIT 1"
            );
            $stmtUsuario->execute([
                ':id'     => (int) $_SESSION['usuario_id'],
                ':estado' => 'Activo',
            ]);

            $usuario = $stmtUsuario->fetch();

            if (!$usuario) {
                destruirSesionYRedirigir('login.php');
            } elseif (!password_verify($actual, $usuario['contrasena'])) {
                $mensajeTexto = 'La contraseña actual ingresada es incorrecta.';
                $mensajeTipo = 'error';
            } else {
                $nuevoHash = password_hash($nueva, PASSWORD_DEFAULT);
                $stmtActualizar = $pdo->prepare(
                    "UPDATE usuarios SET contrasena = :contrasena WHERE id = :id"
                );
                $stmtActualizar->execute([
                    ':contrasena' => $nuevoHash,
                    ':id'         => (int) $usuario['id'],
                ]);

                $mensajeTexto = 'Contraseña actualizada correctamente en la base de datos.';
                $mensajeTipo = 'informacion';
            }
        } catch (PDOException $e) {
            error_log('Error al actualizar contraseña en cambiar-contrasena.php: ' . $e->getMessage());
            $mensajeTexto = 'Ocurrió un error interno al actualizar la contraseña.';
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

    <title>Cambiar contraseña | Cuidado de tus Mascotas</title>

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
    <link rel="stylesheet" href="assets/css/cambiar-contrasena.css?v=<?= filemtime(__DIR__ . '/assets/css/cambiar-contrasena.css') ?>">
    <link rel="stylesheet" href="assets/css/tema.css?v=<?= filemtime(__DIR__ . '/assets/css/tema.css') ?>">
    <!-- defer espera a que el HTML exista antes de conectar filtros y validaciones. -->
    <script src="assets/js/interfaz.js?v=<?= filemtime(__DIR__ . '/assets/js/interfaz.js') ?>" defer></script>
    <script src="assets/js/principal.js" defer></script>
    <script src="assets/js/cambiar-contrasena.js?v=<?= filemtime(__DIR__ . '/assets/js/cambiar-contrasena.js') ?>" defer></script>
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

        <h1>Cambiar contraseña</h1>
        <p>Actualiza la contraseña de tu cuenta</p>
    </header>

    <form id="formulario-cambio-contrasena" method="post" action="cambiar-contrasena.php">
        <div class="campo">
            <label for="contrasena-actual">Contraseña actual</label>

            <input
                type="password"
                id="contrasena-actual"
                name="contrasena_actual"
                autocomplete="current-password"
                placeholder="Escribe tu contraseña actual"
                required
            >
        </div>

        <div class="campo">
            <label for="nueva-contrasena">Nueva contraseña</label>

            <input
                type="password"
                id="nueva-contrasena"
                name="nueva_contrasena"
                autocomplete="new-password"
                placeholder="Mínimo 8 caracteres"
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
                Confirmar nueva contraseña
            </label>

            <input
                type="password"
                id="confirmar-contrasena"
                name="confirmar_contrasena"
                autocomplete="new-password"
                placeholder="Repite la nueva contraseña"
                minlength="8"
                required
            >
        </div>

        <label class="opcion-mostrar">
            <input type="checkbox" id="mostrar-contrasenas">
            Mostrar contraseñas
        </label>

        <p
            id="mensaje-contrasena"
            class="mensaje-formulario<?= $mensajeTipo !== '' ? ' ' . htmlspecialchars($mensajeTipo, ENT_QUOTES, 'UTF-8') : '' ?>"
            role="status"
        ><?= htmlspecialchars($mensajeTexto, ENT_QUOTES, 'UTF-8') ?></p>

        <button type="submit" class="boton-login">
            Guardar contraseña
        </button>
    </form>

    <nav class="login-enlaces" aria-label="Opciones de navegación">
        <a href="cliente/perfil.php">← Volver a mi perfil</a>
    </nav>
</main>

</body>
</html>
