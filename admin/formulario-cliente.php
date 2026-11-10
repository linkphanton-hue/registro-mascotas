<?php
require_once __DIR__ . '/../includes/sesion.php';
requerirRol('admin', '../login.php');

require_once __DIR__ . '/../includes/conexion.php';

$mensajeTexto = '';
$mensajeTipo = '';

$clienteId = filter_var($_GET['id'] ?? ($_POST['id'] ?? null), FILTER_VALIDATE_INT);
$modoEdicion = ($clienteId !== false && $clienteId !== null && $clienteId > 0);

$valores = [
    'nombre' => '',
    'correo' => '',
];

if (!$pdo) {
    $mensajeTexto = $errorConexion ?? 'No se pudo conectar con la base de datos.';
    $mensajeTipo = 'error';
} else {
    if ($modoEdicion && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        try {
            $stmtCargar = $pdo->prepare(
                "SELECT id, nombre, correo FROM usuarios WHERE id = :id AND rol = :rol AND estado = :estado LIMIT 1"
            );
            $stmtCargar->execute([
                ':id'     => $clienteId,
                ':rol'    => 'cliente',
                ':estado' => 'Activo',
            ]);
            $clienteExistente = $stmtCargar->fetch();

            if ($clienteExistente) {
                $valores['nombre'] = $clienteExistente['nombre'];
                $valores['correo'] = $clienteExistente['correo'];
            } else {
                $modoEdicion = false;
                $clienteId = null;
                $mensajeTexto = 'El cliente solicitado no existe o se encuentra inactivo.';
                $mensajeTipo = 'error';
            }
        } catch (PDOException $e) {
            error_log('Error al cargar cliente en formulario-cliente.php: ' . $e->getMessage());
            $mensajeTexto = 'Ocurrió un error al consultar los datos del cliente.';
            $mensajeTipo = 'error';
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre       = trim((string) ($_POST['nombre'] ?? ''));
        $correo       = trim((string) ($_POST['correo'] ?? ''));
        $contrasena   = (string) ($_POST['contrasena'] ?? '');
        $confirmacion = (string) ($_POST['confirmar_contrasena'] ?? '');

        $valores['nombre'] = $nombre;
        $valores['correo'] = $correo;

        $correoValido = filter_var($correo, FILTER_VALIDATE_EMAIL);

        if ($nombre === '' || mb_strlen($nombre) > 100) {
            $mensajeTexto = 'Escribe el nombre del cliente (máximo 100 caracteres, sin estar vacío).';
            $mensajeTipo = 'error';
        } elseif ($correoValido === false || mb_strlen($correo) > 254) {
            $mensajeTexto = 'Ingresa un correo electrónico con formato válido.';
            $mensajeTipo = 'error';
        } elseif (!$modoEdicion && mb_strlen($contrasena) < 8) {
            $mensajeTexto = 'La contraseña debe tener al menos 8 caracteres.';
            $mensajeTipo = 'error';
        } elseif ($modoEdicion && $contrasena !== '' && mb_strlen($contrasena) < 8) {
            $mensajeTexto = 'Si deseas actualizar la contraseña, utiliza al menos 8 caracteres.';
            $mensajeTipo = 'error';
        } elseif ($contrasena !== $confirmacion) {
            $mensajeTexto = 'Las contraseñas no coinciden.';
            $mensajeTipo = 'error';
        } else {
            try {
                // Verifica que el correo no esté registrado en otro usuario
                $sqlCorreo = "SELECT id FROM usuarios WHERE correo = :correo";
                $paramCorreo = [':correo' => $correo];

                if ($modoEdicion) {
                    $sqlCorreo .= " AND id != :id";
                    $paramCorreo[':id'] = $clienteId;
                }

                $stmtCorreo = $pdo->prepare($sqlCorreo . " LIMIT 1");
                $stmtCorreo->execute($paramCorreo);

                if ($stmtCorreo->fetch()) {
                    $mensajeTexto = 'Ya existe una cuenta registrada con ese correo electrónico.';
                    $mensajeTipo = 'error';
                } elseif ($modoEdicion) {
                    $stmtExisteCliente = $pdo->prepare(
                        "SELECT id FROM usuarios WHERE id = :id AND rol = :rol AND estado = :estado LIMIT 1"
                    );
                    $stmtExisteCliente->execute([
                        ':id'     => $clienteId,
                        ':rol'    => 'cliente',
                        ':estado' => 'Activo',
                    ]);

                    if (!$stmtExisteCliente->fetch()) {
                        $mensajeTexto = 'El cliente solicitado no existe o se encuentra inactivo.';
                        $mensajeTipo = 'error';
                    } else {
                        if ($contrasena !== '') {
                            $hash = password_hash($contrasena, PASSWORD_DEFAULT);
                            $stmtActualizar = $pdo->prepare(
                                "UPDATE usuarios SET nombre = :nombre, correo = :correo, contrasena = :contrasena WHERE id = :id AND rol = :rol AND estado = :estado"
                            );
                            $stmtActualizar->execute([
                                ':nombre'     => $nombre,
                                ':correo'     => $correo,
                                ':contrasena' => $hash,
                                ':id'         => $clienteId,
                                ':rol'        => 'cliente',
                                ':estado'     => 'Activo',
                            ]);
                        } else {
                            $stmtActualizar = $pdo->prepare(
                                "UPDATE usuarios SET nombre = :nombre, correo = :correo WHERE id = :id AND rol = :rol AND estado = :estado"
                            );
                            $stmtActualizar->execute([
                                ':nombre' => $nombre,
                                ':correo' => $correo,
                                ':id'     => $clienteId,
                                ':rol'    => 'cliente',
                                ':estado' => 'Activo',
                            ]);
                        }

                        $mensajeTexto = 'Datos del cliente actualizados correctamente.';
                        $mensajeTipo = 'informacion';
                    }
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

                    $mensajeTexto = 'Cliente registrado correctamente en la base de datos.';
                    $mensajeTipo = 'informacion';

                    $valores = [
                        'nombre' => '',
                        'correo' => '',
                    ];
                }
            } catch (PDOException $e) {
                error_log('Error al guardar cliente en formulario-cliente.php: ' . $e->getMessage());
                $mensajeTexto = 'Ocurrió un error interno al guardar los datos del cliente.';
                $mensajeTipo = 'error';
            }
        }
    }
}

$tituloPagina = $modoEdicion ? 'Editar cliente' : 'Registrar cliente';
$textoBoton   = $modoEdicion ? 'Guardar cambios' : 'Registrar cliente';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($tituloPagina, ENT_QUOTES, 'UTF-8') ?> | Administración | Cuidado de tus Mascotas</title>

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
    <link rel="stylesheet" href="../assets/css/admin.css?v=<?= filemtime(__DIR__ . '/../assets/css/admin.css') ?>">
    <link rel="stylesheet" href="../assets/css/admin-formularios.css?v=<?= filemtime(__DIR__ . '/../assets/css/admin-formularios.css') ?>">
    <link rel="stylesheet" href="../assets/css/tema.css?v=<?= filemtime(__DIR__ . '/../assets/css/tema.css') ?>">
    <!-- defer espera a que el HTML exista antes de conectar filtros y validaciones. -->
    <script src="../assets/js/interfaz.js?v=<?= filemtime(__DIR__ . '/../assets/js/interfaz.js') ?>" defer></script>
    <script src="../assets/js/formulario-cliente.js?v=<?= filemtime(__DIR__ . '/../assets/js/formulario-cliente.js') ?>" defer></script>
</head>

<body class="pagina-panel" data-modulo="clientes">
    <div class="estructura-panel">

        <!--
            El menú permanece visible en escritorio mediante
            .barra-lateral, definida en panel.css.
        -->
        <aside class="barra-lateral">
            <a href="../index.php" class="marca-panel">
                <span aria-hidden="true">🐾</span>
                <span>Cuidado de<br>tus Mascotas</span>
            </a>

            <p class="etiqueta-menu">ADMINISTRACIÓN</p>

            <nav class="menu-panel" aria-label="Menú del administrador">
                <!-- Solo la página actual lleva la clase activo. -->
                <a href="panel.php">
                    <span aria-hidden="true">⌂</span>
                    Inicio
                </a>

                <a href="mascotas.php">
                    <span aria-hidden="true">🐾</span>
                    Mascotas
                </a>

                <a href="clientes.php" class="activo" aria-current="page">
                    <span aria-hidden="true">👥</span>
                    Clientes
                </a>
            </nav>

            <a href="../index.php" class="volver-inicio">
                ← Volver al sitio
            </a>

            <a href="../logout.php" class="volver-inicio">
                Cerrar sesión
            </a>
        </aside>

        <main class="contenido-panel">
    <header class="cabecera-panel">
        <div>
            <p class="etiqueta-seccion">ADMINISTRACIÓN / CLIENTES</p>
            <h1><?= htmlspecialchars($tituloPagina, ENT_QUOTES, 'UTF-8') ?></h1>
        </div>

        <a href="clientes.php" class="enlace-volver">
            ← Volver a clientes
        </a>
    </header>

    <p class="descripcion-listado">
        Completa los datos para preparar una cuenta de cliente.
    </p>

    <section class="formulario-admin" aria-labelledby="titulo-datos">
        <h2 id="titulo-datos">Datos del cliente</h2>

        <p class="ayuda-formulario">
            <?= $modoEdicion
                ? 'Actualiza el nombre o correo. Deja las contraseñas en blanco si deseas conservar la actual.'
                : 'Todos los campos son obligatorios.' ?>
        </p>

        <!--
            El rol no se selecciona aquí.
            Esta operación gestiona únicamente cuentas con rol de cliente.
        -->
        <form id="formulario-cliente" method="post" action="formulario-cliente.php<?= $modoEdicion ? '?id=' . (int) $clienteId : '' ?>">
            <?php if ($modoEdicion): ?>
                <input type="hidden" name="id" value="<?= (int) $clienteId ?>">
            <?php endif; ?>

            <div class="rejilla-formulario">
                <div class="campo-admin">
                    <label for="nombre">Nombre completo</label>

                    <input
                        type="text"
                        id="nombre"
                        name="nombre"
                        placeholder="Escribe el nombre completo"
                        autocomplete="name"
                        maxlength="100"
                        value="<?= htmlspecialchars($valores['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>

                <div class="campo-admin">
                    <label for="correo">Correo electrónico</label>

                    <input
                        type="email"
                        id="correo"
                        name="correo"
                        placeholder="cliente@correo.com"
                        autocomplete="off"
                        maxlength="254"
                        value="<?= htmlspecialchars($valores['correo'], ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>

                <div class="campo-admin">
                    <label for="contrasena"><?= $modoEdicion ? 'Nueva contraseña (opcional)' : 'Contraseña inicial' ?></label>

                    <input
                        type="password"
                        id="contrasena"
                        name="contrasena"
                        placeholder="Mínimo 8 caracteres"
                        autocomplete="new-password"
                        minlength="8"
                        aria-describedby="ayuda-clave"
                        <?= $modoEdicion ? '' : 'required' ?>
                    >

                    <small id="ayuda-clave">
                        Utiliza al menos 8 caracteres.
                    </small>
                </div>

                <div class="campo-admin">
                    <label for="confirmar-contrasena">
                        Confirmar contraseña
                    </label>

                    <input
                        type="password"
                        id="confirmar-contrasena"
                        name="confirmar_contrasena"
                        placeholder="Repite la contraseña"
                        autocomplete="new-password"
                        minlength="8"
                        <?= $modoEdicion ? '' : 'required' ?>
                    >
                </div>
            </div>

            <p
                id="mensaje-cliente"
                class="mensaje-admin<?= $mensajeTipo !== '' ? ' ' . htmlspecialchars($mensajeTipo, ENT_QUOTES, 'UTF-8') : '' ?>"
                role="status"
            ><?= htmlspecialchars($mensajeTexto, ENT_QUOTES, 'UTF-8') ?></p>

            <div class="acciones-formulario">
                <button type="submit" class="boton-admin">
                    <?= htmlspecialchars($textoBoton, ENT_QUOTES, 'UTF-8') ?>
                </button>

                <a href="clientes.php" class="boton-cancelar">
                    Cancelar
                </a>
            </div>
        </form>
    </section>

    <p class="aviso-demostracion">
        Formulario de práctica. Todavía no guarda cuentas.
    </p>
</main>
    </div>
</body>
</html>
