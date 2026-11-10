<?php
require_once __DIR__ . '/../includes/sesion.php';
requerirRol('admin', '../login.php');

require_once __DIR__ . '/../includes/conexion.php';
require_once __DIR__ . '/../includes/formato.php';

$mascotas = [];
$errorListado = '';

$etiquetasEspecie = [
    'perro'  => 'Perro',
    'gato'   => 'Gato',
    'ave'    => 'Ave',
    'conejo' => 'Conejo',
    'otro'   => 'Otra especie',
];

if (!$pdo) {
    $errorListado = $errorConexion ?? 'No se pudo conectar con la base de datos para consultar las mascotas.';
} else {
    // Borrado lógico (estado = 'Inactivo') mediante método POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'eliminar') {
        $eliminarId = filter_var($_POST['mascota_id'] ?? null, FILTER_VALIDATE_INT);

        if ($eliminarId !== false && $eliminarId !== null && $eliminarId > 0) {
            try {
                $stmtEliminar = $pdo->prepare(
                    "UPDATE mascotas SET estado = :estado WHERE id = :id AND estado = :estado_actual"
                );
                $stmtEliminar->execute([
                    ':estado'        => 'Inactivo',
                    ':id'            => $eliminarId,
                    ':estado_actual' => 'Activo',
                ]);

                if ($stmtEliminar->rowCount() > 0) {
                    header('Location: mascotas.php?eliminado=1');
                    exit;
                }

                $errorListado = 'La mascota seleccionada no existe o ya se encuentra inactiva.';
            } catch (PDOException $e) {
                error_log('Error al aplicar borrado lógico a mascota en admin/mascotas.php: ' . $e->getMessage());
                $errorListado = 'No fue posible desactivar el registro seleccionado.';
            }
        } else {
            $errorListado = 'Identificador de mascota inválido para dar de baja.';
        }
    }

    try {
        $sql = "SELECT
                    mascotas.id,
                    mascotas.nombre,
                    mascotas.especie,
                    mascotas.raza,
                    mascotas.edad_anios,
                    mascotas.edad_meses,
                    usuarios.nombre AS dueno_nombre
                FROM mascotas
                INNER JOIN usuarios ON mascotas.usuario_id = usuarios.id
                WHERE mascotas.estado = :estado_mascota
                  AND usuarios.estado = :estado_usuario
                ORDER BY mascotas.id ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':estado_mascota' => 'Activo',
            ':estado_usuario' => 'Activo',
        ]);
        $mascotas = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Error al consultar listado de mascotas en admin/mascotas.php: ' . $e->getMessage());
        $errorListado = 'Ocurrió un error al obtener los registros de mascotas.';
    }
}

$totalMascotas = count($mascotas);
$textoContadorInicial = 'Mostrando ' . $totalMascotas . ' ' . ($totalMascotas === 1 ? 'mascota' : 'mascotas');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mascotas | Administración | Cuidado de tus Mascotas</title>

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
    <link rel="stylesheet" href="../assets/css/admin-mascotas.css?v=<?= filemtime(__DIR__ . '/../assets/css/admin-mascotas.css') ?>">
    <link rel="stylesheet" href="../assets/css/tema.css?v=<?= filemtime(__DIR__ . '/../assets/css/tema.css') ?>">
    <!-- defer espera a que el HTML exista antes de conectar filtros y validaciones. -->
    <script src="../assets/js/interfaz.js?v=<?= filemtime(__DIR__ . '/../assets/js/interfaz.js') ?>" defer></script>
    <script src="../assets/js/admin-mascotas.js?v=<?= filemtime(__DIR__ . '/../assets/js/admin-mascotas.js') ?>" defer></script>
</head>

<body class="pagina-panel" data-modulo="mascotas">
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

                <!-- Solo la página actual lleva activo y aria-current. -->
                <a href="mascotas.php" class="activo" aria-current="page">
                    <span aria-hidden="true">🐾</span>
                    Mascotas
                </a>

                <a href="clientes.php">
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
            <h1>Mascotas</h1>
        </div>

        <a href="formulario-mascota.php" class="boton-admin">
            + Registrar mascota
        </a>
    </header>

    <p class="descripcion-listado">
        Consulta y organiza las mascotas registradas en la veterinaria.
    </p>

    <!-- El filtro funciona sobre las filas cargadas desde MySQL. -->
    <div class="barra-filtros">
        <div class="grupo-filtro">
            <label for="filtro-especie">Filtrar por especie</label>

            <select id="filtro-especie">
                <option value="todas">Todas las especies</option>
                <option value="perro">Perros</option>
                <option value="gato">Gatos</option>
                <option value="ave">Aves</option>
                <option value="conejo">Conejos</option>
                <option value="otro">Otras especies</option>
            </select>
        </div>

        <p id="resultado-filtro" role="status">
            <?= htmlspecialchars($textoContadorInicial, ENT_QUOTES, 'UTF-8') ?>
        </p>
    </div>

    <section aria-labelledby="titulo-listado">
        <h2 id="titulo-listado" class="titulo-listado">
            Listado de mascotas
        </h2>

        <div
            class="tabla-contenedor"
            role="region"
            aria-labelledby="titulo-listado"
            tabindex="0"
        >
            <table class="tabla-admin">
                <caption class="solo-lectores">
                    Mascotas registradas con datos de ejemplo
                </caption>

                <thead>
                    <tr>
                        <th scope="col">Mascota</th>
                        <th scope="col">Especie</th>
                        <th scope="col">Raza</th>
                        <th scope="col">Edad</th>
                        <th scope="col">Dueño</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($mascotas as $mascota): ?>
                        <?php
                        $especieClave = strtolower(trim((string) $mascota['especie']));
                        $especieNombre = $etiquetasEspecie[$especieClave] ?? ucfirst($especieClave);
                        $razaTexto = trim((string) ($mascota['raza'] ?? '')) ?: 'No especificada';
                        $edadTexto = formatearEdad((int) $mascota['edad_anios'], (int) $mascota['edad_meses']);
                        ?>
                        <!-- data-especie permite identificar cada fila. -->
                        <tr data-especie="<?= htmlspecialchars($especieClave, ENT_QUOTES, 'UTF-8') ?>">
                            <th scope="row"><?= htmlspecialchars($mascota['nombre'], ENT_QUOTES, 'UTF-8') ?></th>
                            <td><?= htmlspecialchars($especieNombre, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($razaTexto, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($edadTexto, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($mascota['dueno_nombre'], ENT_QUOTES, 'UTF-8') ?></td>

                            <td>
                                <div class="acciones-tabla">
                                    <button
                                        type="button"
                                        class="accion-tabla accion-editar"
                                        aria-label="Editar a <?= htmlspecialchars($mascota['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                        onclick="window.location.href='formulario-mascota.php?id=<?= (int) $mascota['id'] ?>'"
                                    >
                                        Editar
                                    </button>

                                    <button
                                        type="submit"
                                        form="form-eliminar-<?= (int) $mascota['id'] ?>"
                                        class="accion-tabla accion-eliminar"
                                        aria-label="Eliminar a <?= htmlspecialchars($mascota['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-confirmar="Haz clic en Confirmar para dar de baja a <?= htmlspecialchars($mascota['nombre'], ENT_QUOTES, 'UTF-8') ?>."
                                    >
                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <!-- Esta fila aparece si no existen coincidencias. -->
                    <tr id="listado-vacio" <?= $totalMascotas > 0 ? 'hidden' : '' ?>>
                        <td colspan="6">
                            <div class="estado-vacio-ilustrado">
                                <span class="estado-vacio-icono" aria-hidden="true">🐾</span>
                                <h3><?= $errorListado !== '' ? 'No se pudieron cargar las mascotas' : 'Aún no hay mascotas para mostrar' ?></h3>
                                <p>
                                    <?= $errorListado !== ''
                                        ? htmlspecialchars($errorListado, ENT_QUOTES, 'UTF-8')
                                        : 'No se encontraron mascotas activas para la especie seleccionada.' ?>
                                </p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <?php foreach ($mascotas as $mascota): ?>
            <form
                id="form-eliminar-<?= (int) $mascota['id'] ?>"
                method="post"
                action="mascotas.php"
                hidden
            >
                <input type="hidden" name="accion" value="eliminar">
                <input type="hidden" name="mascota_id" value="<?= (int) $mascota['id'] ?>">
            </form>
        <?php endforeach; ?>
    </section>

    <p class="aviso-demostracion">
        Datos de ejemplo. Las acciones de editar y eliminar
        todavía no están habilitadas.
    </p>
</main>
    </div>
</body>
</html>
