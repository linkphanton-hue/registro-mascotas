<?php
require_once __DIR__ . '/../includes/sesion.php';
requerirRol('admin', '../login.php');

require_once __DIR__ . '/../includes/conexion.php';

$clientes = [];
$errorListado = '';

if (!$pdo) {
    $errorListado = $errorConexion ?? 'No se pudo conectar con la base de datos para consultar los clientes.';
} else {
    try {
        $sql = "SELECT
                    usuarios.id,
                    usuarios.nombre,
                    usuarios.correo,
                    COUNT(mascotas.id) AS total_mascotas
                FROM usuarios
                LEFT JOIN mascotas
                    ON mascotas.usuario_id = usuarios.id
                   AND mascotas.estado = :estado_mascota
                WHERE usuarios.rol = :rol
                  AND usuarios.estado = :estado_usuario
                GROUP BY usuarios.id, usuarios.nombre, usuarios.correo
                ORDER BY usuarios.id ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':estado_mascota' => 'Activo',
            ':rol'            => 'cliente',
            ':estado_usuario' => 'Activo',
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
        Consulta los datos de los dueños de las mascotas.
    </p>

    <!-- JavaScript utiliza este campo para filtrar la tabla. -->
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
                    Clientes registrados con datos de ejemplo
                </caption>

                <thead>
                    <tr>
                        <th scope="col">Nombre completo</th>
                        <th scope="col">Correo electrónico</th>
                        <th scope="col">Mascotas</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <!--
                        data-cliente identifica las filas que se pueden buscar.
                    -->
                    <?php foreach ($clientes as $cliente): ?>
                        <tr data-cliente>
                            <th scope="row" class="cliente-nombre">
                                <?= htmlspecialchars($cliente['nombre'], ENT_QUOTES, 'UTF-8') ?>
                            </th>

                            <td class="cliente-correo">
                                <?= htmlspecialchars($cliente['correo'], ENT_QUOTES, 'UTF-8') ?>
                            </td>

                            <td><?= (int) $cliente['total_mascotas'] ?></td>

                            <td>
                                <div class="acciones-tabla">
                                    <button
                                        type="button"
                                        class="accion-tabla accion-editar"
                                        aria-label="Editar a <?= htmlspecialchars($cliente['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                        onclick="window.location.href='formulario-cliente.php?id=<?= (int) $cliente['id'] ?>'"
                                    >
                                        Editar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <tr id="clientes-vacio" <?= $totalClientes > 0 ? 'hidden' : '' ?>>
                        <td colspan="4">
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

    <p class="aviso-demostracion">
        Datos de ejemplo. La edición todavía no está habilitada.
    </p>
</main>
    </div>
</body>
</html>
