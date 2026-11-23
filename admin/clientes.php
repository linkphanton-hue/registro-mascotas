<?php
/**
 * Controlador del listado administrativo de clientes.
 *
 * Consulta todos los usuarios con rol 'cliente' y calcula cuántas
 * mascotas activas tiene registrado cada dueño para mostrarlo en la tabla.
 *
 * @package RegistroMascotas\Admin
 */

/* Exige sesión activa con rol 'admin'; redirige a login.php si no cumple */
require_once __DIR__ . '/../includes/sesion.php';
requerirRol('admin', '../login.php');

require_once __DIR__ . '/../includes/conexion.php';

$clientes = [];
$errorListado = '';
$errorEstado = '';
$csrfEstado = $_SESSION['csrf_estado_cliente'] ??= bin2hex(random_bytes(32));
$avisoEstado = $_SESSION['aviso_estado_cliente'] ?? '';
unset($_SESSION['aviso_estado_cliente']);

if (!$pdo) {
    $errorListado = $errorConexion ?? 'No se pudo conectar con la base de datos para consultar los clientes.';
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf'] ?? '';
        $clienteId = filter_var($_POST['cliente_id'] ?? null, FILTER_VALIDATE_INT);
        $accion = $_POST['accion'] ?? '';
        if (!is_string($token) || !hash_equals($csrfEstado, $token) || !$clienteId || $clienteId < 1
            || !in_array($accion, ['desactivar', 'reactivar'], true)) {
            $errorEstado = 'No se pudo validar el cambio de estado.';
        } else {
            $desactivar = $accion === 'desactivar';
            try {
                $actualizar = $pdo->prepare(
                    "UPDATE usuarios SET estado = :nuevo WHERE id = :id AND rol = 'cliente' AND estado = :actual"
                );
                $actualizar->execute([
                    ':id' => $clienteId,
                    ':nuevo' => $desactivar ? 'Inactivo' : 'Activo',
                    ':actual' => $desactivar ? 'Activo' : 'Inactivo',
                ]);
                if ($actualizar->rowCount() === 1) {
                    $_SESSION['aviso_estado_cliente'] = $desactivar ? 'Cuenta desactivada.' : 'Cuenta reactivada.';
                    header('Location: clientes.php', true, 303);
                    exit;
                }
                $errorEstado = 'La cuenta ya cambió de estado o no está disponible.';
            } catch (PDOException $e) {
                error_log('Error al cambiar el estado del cliente: ' . $e->getMessage());
                $errorEstado = 'No se pudo cambiar el estado de la cuenta.';
            }
        }
    }

    try {
        /*
         * Consulta preparada con LEFT JOIN y GROUP BY:
         * Se utiliza LEFT JOIN (con la condición mascotas.estado = 'Activo' en el ON)
         * para incluir también a los clientes que aún tienen 0 mascotas registradas.
         */
        $sql = "SELECT
                    usuarios.id,
                    usuarios.nombre,
                    usuarios.correo,
                    usuarios.estado,
                    COUNT(mascotas.id) AS total_mascotas
                FROM usuarios
                LEFT JOIN mascotas
                    ON mascotas.usuario_id = usuarios.id
                   AND mascotas.estado = :estado_mascota
                WHERE usuarios.rol = :rol
                GROUP BY usuarios.id, usuarios.nombre, usuarios.correo, usuarios.estado
                ORDER BY usuarios.id ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':estado_mascota' => 'Activo',
            ':rol'            => 'cliente',
        ]);
        $clientes = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Error al consultar listado de clientes en admin/clientes.php: ' . $e->getMessage());
        $errorListado = 'Ocurrió un error al obtener los clientes registrados.';
    }
}

$totalClientes = count($clientes);
$textoContadorInicial = 'Mostrando ' . $totalClientes . ' ' . ($totalClientes === 1 ? 'cliente' : 'clientes');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Clientes | Administración | Cuidado de tus Mascotas</title>

    <!-- Carga el script para inicializar el tema -->
    <script src="../assets/js/tema-inicial.js?v=<?= filemtime(__DIR__ . '/../assets/js/tema-inicial.js') ?>"></script>

    <!-- Primero los estilos compartidos; después los exclusivos de esta pantalla.
         filemtime solicita una versión nueva al guardar cada CSS. -->
    <link rel="stylesheet" href="../assets/css/panel.css?v=<?= filemtime(__DIR__ . '/../assets/css/panel.css') ?>">
    <link rel="stylesheet" href="../assets/css/admin.css?v=<?= filemtime(__DIR__ . '/../assets/css/admin.css') ?>">
    <link rel="stylesheet" href="../assets/css/admin-clientes.css?v=<?= filemtime(__DIR__ . '/../assets/css/admin-clientes.css') ?>">
    <link rel="stylesheet" href="../assets/css/tema.css?v=<?= filemtime(__DIR__ . '/../assets/css/tema.css') ?>">
    <!-- defer espera a que el HTML exista antes de conectar filtros y validaciones. -->
    <script src="../assets/js/interfaz.js?v=<?= filemtime(__DIR__ . '/../assets/js/interfaz.js') ?>" defer></script>
    <script src="../assets/js/admin-clientes.js?v=<?= filemtime(__DIR__ . '/../assets/js/admin-clientes.js') ?>" defer></script>
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

                <a href="mensajes.php">
                    <span aria-hidden="true">✉️</span>
                    Mensajes
                </a>
                <a href="servicios.php">Servicios</a>
                <a href="citas.php">
                    <span aria-hidden="true">📅</span>
                    Citas
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
            <p class="etiqueta-seccion">ADMINISTRACIÓN</p>
            <h1>Clientes</h1>
        </div>

        <a href="formulario-cliente.php" class="boton-admin">
            + Registrar cliente
        </a>
    </header>

    <p class="descripcion-listado">
        Administra el acceso de los clientes y consulta sus mascotas registradas.
    </p>

    <?php if ($avisoEstado !== ''): ?>
        <p role="status"><?= htmlspecialchars($avisoEstado, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <?php if ($errorEstado !== ''): ?>
        <p role="alert"><?= htmlspecialchars($errorEstado, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <div class="barra-filtros">
        <div class="grupo-filtro">
            <label for="buscar-cliente">
                Buscar por nombre o correo
            </label>

            <input
                type="search"
                id="buscar-cliente"
                placeholder="Escribe un nombre o correo"
                aria-controls="tabla-clientes"
            >
        </div>

        <div class="grupo-filtro">
            <label for="filtrar-estado-cliente">Estado de cuenta</label>
            <select id="filtrar-estado-cliente" aria-controls="tabla-clientes">
                <option value="">Todas las cuentas</option>
                <option value="Activo">Activas</option>
                <option value="Inactivo">Inactivas</option>
            </select>
        </div>

        <p id="resultado-clientes" role="status">
            <?= htmlspecialchars($textoContadorInicial, ENT_QUOTES, 'UTF-8') ?>
        </p>
    </div>

    <section aria-labelledby="titulo-clientes">
        <h2 id="titulo-clientes" class="titulo-listado">
            Clientes registrados
        </h2>

        <div
            class="tabla-contenedor"
            role="region"
            aria-labelledby="titulo-clientes"
            tabindex="0"
        >
            <table id="tabla-clientes" class="tabla-admin">
                <caption class="solo-lectores">
                    Clientes registrados
                </caption>

                <thead>
                    <tr>
                        <th scope="col">Nombre completo</th>
                        <th scope="col">Correo electrónico</th>
                        <th scope="col">Mascotas</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <!--
                        data-cliente identifica las filas que se pueden buscar.
                    -->
                    <?php foreach ($clientes as $cliente): ?>
                        <?php $activo = $cliente['estado'] === 'Activo'; ?>
                        <tr data-cliente data-estado="<?= $activo ? 'Activo' : 'Inactivo' ?>">
                            <th scope="row" class="cliente-nombre">
                                <?= htmlspecialchars($cliente['nombre'], ENT_QUOTES, 'UTF-8') ?>
                            </th>

                            <td class="cliente-correo">
                                <?= htmlspecialchars($cliente['correo'], ENT_QUOTES, 'UTF-8') ?>
                            </td>

                            <td><?= (int) $cliente['total_mascotas'] ?></td>
                            <td><?= $activo ? 'Activo' : 'Inactivo' ?></td>

                            <td>
                                <div class="acciones-tabla">
                                    <?php if ($activo): ?>
                                        <a class="accion-tabla accion-editar" href="formulario-cliente.php?id=<?= (int) $cliente['id'] ?>"
                                           aria-label="Editar a <?= htmlspecialchars($cliente['nombre'], ENT_QUOTES, 'UTF-8') ?>">Editar</a>
                                    <?php endif; ?>
                                    <form method="post" action="clientes.php">
                                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrfEstado, ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="cliente_id" value="<?= (int) $cliente['id'] ?>">
                                        <button type="submit" name="accion" value="<?= $activo ? 'desactivar' : 'reactivar' ?>"
                                                class="accion-tabla <?= $activo ? 'accion-eliminar' : 'accion-editar' ?>"
                                                <?php if ($activo): ?>data-confirmar="Pulsa de nuevo para bloquear el acceso de este cliente."<?php endif; ?>>
                                            <?= $activo ? 'Desactivar' : 'Reactivar' ?>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <tr id="clientes-vacio" <?= $totalClientes > 0 ? 'hidden' : '' ?>>
                        <td colspan="5">
                            <div class="estado-vacio-ilustrado">
                                <span class="estado-vacio-icono" aria-hidden="true">👥</span>
                                <h3><?= $errorListado !== '' ? 'No se pudieron cargar los clientes' : 'Sin clientes para mostrar' ?></h3>
                                <p>
                                    <?= $errorListado !== ''
                                        ? htmlspecialchars($errorListado, ENT_QUOTES, 'UTF-8')
                                        : 'No se encontraron clientes con esa búsqueda.' ?>
                                </p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

</main>
    </div>
</body>
</html>
