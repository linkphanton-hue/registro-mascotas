<?php
require_once __DIR__ . '/includes/conexion.php';

$mensajeTexto = '';
$mensajeTipo = '';

$valores = [
    'nombre'  => '',
    'correo'  => '',
    'asunto'  => '',
    'mensaje' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre  = trim((string) ($_POST['nombre'] ?? ''));
    $correo  = trim((string) ($_POST['correo'] ?? ''));
    $asunto  = trim((string) ($_POST['asunto'] ?? ''));
    $mensaje = trim((string) ($_POST['mensaje'] ?? ''));

    $valores = [
        'nombre'  => $nombre,
        'correo'  => $correo,
        'asunto'  => $asunto,
        'mensaje' => $mensaje,
    ];

    $correoValido = filter_var($correo, FILTER_VALIDATE_EMAIL);

    if (!$pdo) {
        $mensajeTexto = $errorConexion ?? 'No se pudo conectar con la base de datos.';
        $mensajeTipo = 'error';
    } elseif ($nombre === '' || mb_strlen($nombre) > 100) {
        $mensajeTexto = 'Escribe tu nombre completo (máximo 100 caracteres).';
        $mensajeTipo = 'error';
    } elseif ($correoValido === false || mb_strlen($correo) > 254) {
        $mensajeTexto = 'Ingresa un correo electrónico válido.';
        $mensajeTipo = 'error';
    } elseif ($asunto === '' || mb_strlen($asunto) > 150) {
        $mensajeTexto = 'Indica el asunto de tu consulta (máximo 150 caracteres).';
        $mensajeTipo = 'error';
    } elseif ($mensaje === '' || mb_strlen($mensaje) < 10) {
        $mensajeTexto = 'Escribe un mensaje de al menos 10 caracteres.';
        $mensajeTipo = 'error';
    } else {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO mensajes_contacto (nombre, correo, asunto, mensaje) VALUES (:nombre, :correo, :asunto, :mensaje)"
            );
            $stmt->execute([
                ':nombre'  => $nombre,
                ':correo'  => $correo,
                ':asunto'  => $asunto,
                ':mensaje' => $mensaje,
            ]);

            $mensajeTexto = '¡Gracias por escribirnos! Hemos recibido tu mensaje correctamente.';
            $mensajeTipo = 'informacion';

            $valores = [
                'nombre'  => '',
                'correo'  => '',
                'asunto'  => '',
                'mensaje' => '',
            ];
        } catch (PDOException $e) {
            error_log('Error al guardar mensaje en contacto.php: ' . $e->getMessage());
            $mensajeTexto = 'Ocurrió un error al enviar tu mensaje. Inténtalo de nuevo.';
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

    <title>Contacto | Cuidado de tus Mascotas</title>

    <script>
        try {
            if (localStorage.getItem('tema-preferido') === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        } catch (e) {}
    </script>

    <link rel="stylesheet" href="assets/css/inicio.css?v=<?= filemtime(__DIR__ . '/assets/css/inicio.css') ?>">
    <link rel="stylesheet" href="assets/css/admin.css?v=<?= filemtime(__DIR__ . '/assets/css/admin.css') ?>">
    <link rel="stylesheet" href="assets/css/admin-formularios.css?v=<?= filemtime(__DIR__ . '/assets/css/admin-formularios.css') ?>">
    <link rel="stylesheet" href="assets/css/tema.css?v=<?= filemtime(__DIR__ . '/assets/css/tema.css') ?>">
    <script src="assets/js/interfaz.js?v=<?= filemtime(__DIR__ . '/assets/js/interfaz.js') ?>" defer></script>
</head>

<body data-modulo="mascotas">
    <a class="saltar-contenido" href="#contenido">
        Ir al contenido
    </a>

    <header class="cabecera">
        <nav class="navegacion contenedor" aria-label="Menú principal">
            <a class="marca" href="index.php">
                <span aria-hidden="true">🐾</span>
                Cuidado de tus Mascotas
            </a>

            <div class="menu">
                <a href="index.php#servicios">Servicios</a>
                <a href="index.php#nosotros">Nosotros</a>
                <a href="contacto.php" aria-current="page">Contacto</a>
                <a href="login.php" class="boton boton-pequeno">
                    Iniciar sesión
                </a>
            </div>
        </nav>
    </header>

    <main id="contenido">
        <section class="seccion contenedor" aria-labelledby="titulo-contacto">
            <div class="contacto-rejilla">
                <div>
                    <p class="etiqueta">ESTAMOS PARA AYUDARTE</p>
                    <h1 id="titulo-contacto">Contáctanos</h1>
                    <p class="descripcion">
                        ¿Tienes dudas sobre el registro de tus mascotas o nuestros servicios veterinarios?
                        Escríbenos y nuestro equipo te responderá a la brevedad.
                    </p>

                    <div class="rejilla" style="grid-template-columns: 1fr;">
                        <article class="tarjeta">
                            <h3>📍 Atención presencial</h3>
                            <p>Av. Principal Veterinaria #124. Lunes a sábado de 8:00 a 19:00 h.</p>
                        </article>

                        <article class="tarjeta">
                            <h3>✉️ Correo y soporte</h3>
                            <p>contacto@cuidadomascotas.com · Respuesta en menos de 24 horas hábiles.</p>
                        </article>
                    </div>
                </div>

                <section class="formulario-admin" aria-labelledby="titulo-formulario-contacto">
                    <h2 id="titulo-formulario-contacto">Envíanos un mensaje</h2>
                    <p class="ayuda-formulario">
                        Completa todos los campos para registrar tu consulta.
                    </p>

                    <form id="formulario-contacto" method="post" action="contacto.php">
                        <div class="rejilla-formulario">
                            <div class="campo-admin">
                                <label for="nombre">Nombre completo *</label>
                                <input
                                    type="text"
                                    id="nombre"
                                    name="nombre"
                                    placeholder="Tu nombre"
                                    autocomplete="name"
                                    maxlength="100"
                                    value="<?= htmlspecialchars($valores['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                    required
                                >
                            </div>

                            <div class="campo-admin">
                                <label for="correo">Correo electrónico *</label>
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

                            <div class="campo-admin campo-completo">
                                <label for="asunto">Asunto *</label>
                                <input
                                    type="text"
                                    id="asunto"
                                    name="asunto"
                                    placeholder="Ej. Consulta sobre registro de mascota"
                                    maxlength="150"
                                    value="<?= htmlspecialchars($valores['asunto'], ENT_QUOTES, 'UTF-8') ?>"
                                    required
                                >
                            </div>

                            <div class="campo-admin campo-completo">
                                <label for="mensaje">Mensaje *</label>
                                <textarea
                                    id="mensaje"
                                    name="mensaje"
                                    placeholder="Escribe los detalles de tu consulta (mínimo 10 caracteres)"
                                    minlength="10"
                                    required
                                ><?= htmlspecialchars($valores['mensaje'], ENT_QUOTES, 'UTF-8') ?></textarea>
                            </div>
                        </div>

                        <p
                            id="mensaje-contacto"
                            class="mensaje-admin<?= $mensajeTipo !== '' ? ' ' . htmlspecialchars($mensajeTipo, ENT_QUOTES, 'UTF-8') : '' ?>"
                            role="status"
                        ><?= htmlspecialchars($mensajeTexto, ENT_QUOTES, 'UTF-8') ?></p>

                        <div class="acciones-formulario">
                            <button type="submit" class="boton-admin">
                                Enviar mensaje
                            </button>

                            <a href="index.php" class="boton-cancelar">
                                Volver al inicio
                            </a>
                        </div>
                    </form>
                </section>
            </div>
        </section>
    </main>

    <footer id="contacto" class="pie">
        <div class="contenedor pie-contenido">
            <div>
                <p class="marca">🐾 Cuidado de tus Mascotas</p>
                <p>Unidos por su bienestar.</p>
            </div>

            <nav aria-label="Enlaces del pie">
                <a href="contacto.php">Contacto</a>
                <a href="login.php">Iniciar sesión</a>
                <a href="registro.php">Registrarse</a>
                <a href="recuperar-contrasena.php">
                    Recuperar contraseña
                </a>
            </nav>
        </div>
    </footer>
</body>
</html>

