<?php
require_once __DIR__ . '/../includes/sesion.php';
requerirRol('cliente', '../login.php');

require_once __DIR__ . '/../includes/conexion.php';

$clienteId     = (int) $_SESSION['usuario_id'];
$nombreCliente = trim((string) $_SESSION['usuario_nombre']);
$correoCliente = trim((string) $_SESSION['usuario_correo']);
$inicialAvatar = mb_strtoupper(mb_substr($nombreCliente, 0, 1));
$avisoEdicion  = 'La edición estará disponible cuando conectemos el sistema a la base de datos.';
$tipoToast     = '';

if ($pdo) {
    try {
        $stmtCliente = $pdo->prepare(
            "SELECT id, nombre, correo FROM usuarios WHERE id = :id AND rol = :rol AND estado = :estado LIMIT 1"
        );
        $stmtCliente->execute([
            ':id'     => $clienteId,
            ':rol'    => 'cliente',
            ':estado' => 'Activo',
        ]);
        $clienteActual = $stmtCliente->fetch();

        if (!$clienteActual) {
            destruirSesionYRedirigir('../login.php');
        }

        $clienteId     = (int) $clienteActual['id'];
        $nombreCliente = trim((string) $clienteActual['nombre']);
        $correoCliente = trim((string) $clienteActual['correo']);
        $inicialAvatar = mb_strtoupper(mb_substr($nombreCliente, 0, 1));

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nuevoNombre  = trim((string) ($_POST['nombre'] ?? ''));
            $nuevoCorreo  = trim((string) ($_POST['correo'] ?? ''));
            $correoValido = filter_var($nuevoCorreo, FILTER_VALIDATE_EMAIL);

            if ($nuevoNombre === '' || mb_strlen($nuevoNombre) > 100) {
                $avisoEdicion = 'El nombre no puede estar vacío ni superar los 100 caracteres.';
                $tipoToast    = 'error';
            } elseif ($correoValido === false || mb_strlen($nuevoCorreo) > 254) {
                $avisoEdicion = 'Ingresa un correo electrónico válido.';
                $tipoToast    = 'error';
            } else {
                $stmtDuplicado = $pdo->prepare(
                    "SELECT id FROM usuarios WHERE correo = :correo AND id != :id LIMIT 1"
                );
                $stmtDuplicado->execute([
                    ':correo' => $nuevoCorreo,
                    ':id'     => $clienteId,
                ]);

                if ($stmtDuplicado->fetch()) {
                    $avisoEdicion = 'Ese correo electrónico ya pertenece a otra cuenta.';
                    $tipoToast    = 'error';
                } else {
                    $stmtUpdate = $pdo->prepare(
                        "UPDATE usuarios SET nombre = :nombre, correo = :correo WHERE id = :id AND rol = :rol AND estado = :estado"
                    );
                    $stmtUpdate->execute([
                        ':nombre' => $nuevoNombre,
                        ':correo' => $nuevoCorreo,
                        ':id'     => $clienteId,
                        ':rol'    => 'cliente',
                        ':estado' => 'Activo',
                    ]);

                    $nombreCliente = $nuevoNombre;
                    $correoCliente = $nuevoCorreo;
                    $inicialAvatar = mb_strtoupper(mb_substr($nombreCliente, 0, 1));

                    $_SESSION['usuario_nombre'] = $nombreCliente;
                    $_SESSION['usuario_correo'] = $correoCliente;

                    $avisoEdicion = 'Tus datos han sido actualizados correctamente.';
                    $tipoToast    = 'exito';
                }
            }
        }
    } catch (PDOException $e) {
        error_log('Error al cargar o actualizar perfil en cliente/perfil.php: ' . $e->getMessage());
        $avisoEdicion = 'Ocurrió un error al procesar tus datos.';
        $tipoToast    = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mi perfil | Cuidado de tus Mascotas</title>

    <script>
        try {
            if (localStorage.getItem('tema-preferido') === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        } catch (e) {}
    </script>

    <!-- Primero los estilos compartidos; después los exclusivos de esta pantalla.
         filemtime solicita una versión nueva al guardar cada CSS. -->
    <link rel="stylesheet" href="../assets/css/panel.css?v=<?= filemtime(__DIR__ . '/../assets/css/panel.css') ?>">
    <link rel="stylesheet" href="../assets/css/cliente-perfil.css?v=<?= filemtime(__DIR__ . '/../assets/css/cliente-perfil.css') ?>">
    <link rel="stylesheet" href="../assets/css/tema.css?v=<?= filemtime(__DIR__ . '/../assets/css/tema.css') ?>">
    <script src="../assets/js/interfaz.js?v=<?= filemtime(__DIR__ . '/../assets/js/interfaz.js') ?>" defer></script>
</head>

<body class="pagina-panel" data-modulo="clientes">
    <div class="estructura-panel">

        <!-- MENU LATERAL: se mantiene visible al desplazar la pagina en escritorio.
             La regla .barra-lateral de panel.css usa position: sticky y top: 0.
             En celulares pasa arriba del contenido para dejar espacio a las tarjetas. -->
        <aside class="barra-lateral">
            <!-- Marca del sistema: vuelve a la pagina publica. -->
            <a href="../index.php" class="marca-panel">
                <span aria-hidden="true">🐾</span>
                <span>Cuidado de<br>tus Mascotas</span>
            </a>

            <p class="etiqueta-menu">MI ESPACIO</p>

            <nav class="menu-panel" aria-label="Menú del cliente">
                <!-- El enlace activo identifica la pagina actual; no necesita JavaScript. -->
                <a href="panel.php">
                    <span aria-hidden="true">⌂</span>
                    Inicio
                </a>

                <a href="mis-mascotas.php">
                    <span aria-hidden="true">🐾</span>
                    Mis mascotas
                </a>

                <a href="perfil.php" class="activo" aria-current="page">
                    <span aria-hidden="true">👤</span>
                    Mi perfil
                </a>
            </nav>

            <a href="../index.php" class="volver-inicio">
                ← Volver al sitio
            </a>

            <a href="../logout.php" class="volver-inicio">
                Cerrar sesión
            </a>
        </aside>

        <!-- Contenido principal. -->
        <main class="contenido-panel">
    <header class="cabecera-panel">
        <div>
            <p class="etiqueta-seccion">MI ESPACIO</p>
            <h1>Mi perfil</h1>
        </div>

        <div class="usuario-panel">
            <span class="avatar" aria-hidden="true"><?= htmlspecialchars($inicialAvatar, ENT_QUOTES, 'UTF-8') ?></span>

            <div>
                <strong><?= htmlspecialchars($nombreCliente, ENT_QUOTES, 'UTF-8') ?></strong>
                <span>Cliente</span>
            </div>
        </div>
    </header>

    <p class="descripcion-listado">
        Consulta la información de tu cuenta.
    </p>

    <section class="perfil-tarjeta" aria-labelledby="titulo-perfil">
        <div class="perfil-presentacion">
            <span class="perfil-avatar" aria-hidden="true"><?= htmlspecialchars($inicialAvatar, ENT_QUOTES, 'UTF-8') ?></span>

            <div>
                <h2 id="titulo-perfil"><?= htmlspecialchars($nombreCliente, ENT_QUOTES, 'UTF-8') ?></h2>
                <p>Cuenta de cliente</p>
            </div>
        </div>

        <form id="form-perfil-cliente" method="post" action="perfil.php">
            <div class="perfil-datos">
                <div class="perfil-campo">
                    <label for="perfil-nombre">Nombre completo</label>

                    <input
                        type="text"
                        id="perfil-nombre"
                        name="nombre"
                        maxlength="100"
                        value="<?= htmlspecialchars($nombreCliente, ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>

                <div class="perfil-campo">
                    <label for="perfil-correo">Correo electrónico</label>

                    <input
                        type="email"
                        id="perfil-correo"
                        name="correo"
                        maxlength="254"
                        value="<?= htmlspecialchars($correoCliente, ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>
            </div>

            <div class="perfil-acciones">
                <button
                    type="submit"
                    class="boton-editar-perfil"
                    aria-describedby="aviso-edicion"
                >
                    Editar mis datos
                </button>

                <p
                    id="aviso-edicion"
                    class="perfil-ayuda"
                    role="status"
                    <?= $tipoToast !== '' ? 'data-toast="' . htmlspecialchars($tipoToast, ENT_QUOTES, 'UTF-8') . '"' : '' ?>
                >
                    <?= htmlspecialchars($avisoEdicion, ENT_QUOTES, 'UTF-8') ?>
                </p>
            </div>
        </form>
    </section>

    <section class="perfil-seguridad" aria-labelledby="titulo-seguridad">
        <div>
            <h2 id="titulo-seguridad">Seguridad de la cuenta</h2>
            <p>Accede al formulario para cambiar tu contraseña.</p>
        </div>

        <a href="../cambiar-contrasena.php" class="enlace-contrasena">
            Cambiar contraseña →
        </a>
    </section>

    <p class="aviso-demostracion">
        Vista de práctica con datos de ejemplo.
    </p>
</main>
    </div>
</body>
</html>
