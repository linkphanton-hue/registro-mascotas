<?php
require_once __DIR__ . '/../includes/sesion.php';
requerirRol('admin', '../login.php');

require_once __DIR__ . '/../includes/conexion.php';

$clientes = [];
$mensajeTexto = '';
$mensajeTipo = '';

$mascotaId = filter_var($_GET['id'] ?? ($_POST['id'] ?? null), FILTER_VALIDATE_INT);
$modoEdicion = ($mascotaId !== false && $mascotaId !== null && $mascotaId > 0);

$especiesPermitidas = [
    'perro'  => 'Perro',
    'gato'   => 'Gato',
    'ave'    => 'Ave',
    'conejo' => 'Conejo',
    'otro'   => 'Otra especie',
];

$valores = [
    'nombre'     => '',
    'especie'    => '',
    'raza'       => '',
    'edad_anios' => '0',
    'edad_meses' => '0',
    'dueno'      => '',
];

if (!$pdo) {
    $mensajeTexto = $errorConexion ?? 'No se pudo conectar con la base de datos. No es posible cargar los clientes ni registrar mascotas.';
    $mensajeTipo = 'error';
} else {
    try {
        $stmtClientes = $pdo->prepare(
            "SELECT id, nombre, correo FROM usuarios WHERE rol = :rol AND estado = :estado ORDER BY nombre ASC"
        );
        $stmtClientes->execute([
            ':rol'    => 'cliente',
            ':estado' => 'Activo',
        ]);
        $clientes = $stmtClientes->fetchAll();
    } catch (PDOException $e) {
        error_log('Error al consultar clientes en formulario-mascota.php: ' . $e->getMessage());
        $mensajeTexto = 'Error al cargar el listado de clientes desde la base de datos.';
        $mensajeTipo = 'error';
    }

    if ($modoEdicion && $_SERVER['REQUEST_METHOD'] !== 'POST' && $mensajeTipo !== 'error') {
        try {
            $stmtMascota = $pdo->prepare(
                "SELECT id, nombre, especie, raza, edad_anios, edad_meses, usuario_id FROM mascotas WHERE id = :id AND estado = :estado LIMIT 1"
            );
            $stmtMascota->execute([
                ':id'     => $mascotaId,
                ':estado' => 'Activo',
            ]);
            $mascotaActual = $stmtMascota->fetch();

            if ($mascotaActual) {
                $valores = [
                    'nombre'     => $mascotaActual['nombre'],
                    'especie'    => strtolower(trim((string) $mascotaActual['especie'])),
                    'raza'       => (string) ($mascotaActual['raza'] ?? ''),
                    'edad_anios' => (string) $mascotaActual['edad_anios'],
                    'edad_meses' => (string) $mascotaActual['edad_meses'],
                    'dueno'      => (string) $mascotaActual['usuario_id'],
                ];
            } else {
                $modoEdicion = false;
                $mascotaId = null;
                $mensajeTexto = 'La mascota solicitada no existe o se encuentra inactiva.';
                $mensajeTipo = 'error';
            }
        } catch (PDOException $e) {
            error_log('Error al cargar mascota en formulario-mascota.php: ' . $e->getMessage());
            $mensajeTexto = 'Ocurrió un error al consultar los datos de la mascota.';
            $mensajeTipo = 'error';
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $mensajeTipo !== 'error') {
        $nombre      = trim((string) ($_POST['nombre'] ?? ''));
        $especie     = strtolower(trim((string) ($_POST['especie'] ?? '')));
        $razaRaw     = trim((string) ($_POST['raza'] ?? ''));
        $raza        = $razaRaw !== '' ? $razaRaw : 'No especificada';
        $aniosRaw    = trim((string) ($_POST['edad_anios'] ?? ($_POST['edad'] ?? '')));
        $mesesRaw    = trim((string) ($_POST['edad_meses'] ?? (isset($_POST['edad']) ? '0' : '')));
        $duenoRaw    = trim((string) ($_POST['dueno'] ?? ''));

        $valores = [
            'nombre'     => $nombre,
            'especie'    => $especie,
            'raza'       => $razaRaw,
            'edad_anios' => $aniosRaw !== '' ? $aniosRaw : '0',
            'edad_meses' => $mesesRaw !== '' ? $mesesRaw : '0',
            'dueno'      => $duenoRaw,
        ];

        $edadAnios = filter_var($aniosRaw, FILTER_VALIDATE_INT);
        $edadMeses = filter_var($mesesRaw, FILTER_VALIDATE_INT);
        $duenoId   = filter_var($duenoRaw, FILTER_VALIDATE_INT);

        if ($nombre === '' || mb_strlen($nombre) > 100) {
            $mensajeTexto = 'Escribe un nombre válido para la mascota (máximo 100 caracteres, sin estar vacío).';
            $mensajeTipo = 'error';
        } elseif (!array_key_exists($especie, $especiesPermitidas)) {
            $mensajeTexto = 'Selecciona una especie válida de la lista.';
            $mensajeTipo = 'error';
        } elseif (mb_strlen($razaRaw) > 100 || (array_key_exists('raza', $_POST) && $razaRaw === '')) {
            $mensajeTexto = 'Escribe una raza válida para la mascota (máximo 100 caracteres, sin estar vacía).';
            $mensajeTipo = 'error';
        } elseif ($edadAnios === false || $edadAnios < 0 || $edadAnios > 100) {
            $mensajeTexto = 'Los años de la mascota deben ser un número entero entre 0 y 100.';
            $mensajeTipo = 'error';
        } elseif ($edadMeses === false || $edadMeses < 0 || $edadMeses > 11) {
            $mensajeTexto = 'Los meses de la mascota deben ser un número entero entre 0 y 11.';
            $mensajeTipo = 'error';
        } elseif ($duenoId === false || $duenoId <= 0) {
            $mensajeTexto = 'Selecciona un cliente válido como dueño de la mascota.';
            $mensajeTipo = 'error';
        } else {
            try {
                // Verifica que el cliente exista en la BD, tenga rol cliente y esté Activo
                $stmtVerificar = $pdo->prepare(
                    "SELECT id FROM usuarios WHERE id = :id AND rol = :rol AND estado = :estado LIMIT 1"
                );
                $stmtVerificar->execute([
                    ':id'     => $duenoId,
                    ':rol'    => 'cliente',
                    ':estado' => 'Activo',
                ]);

                if (!$stmtVerificar->fetch()) {
                    $mensajeTexto = 'El cliente seleccionado no existe o no se encuentra activo en la base de datos.';
                    $mensajeTipo = 'error';
                } elseif ($modoEdicion) {
                    $stmtExisteMascota = $pdo->prepare(
                        "SELECT id FROM mascotas WHERE id = :id AND estado = :estado LIMIT 1"
                    );
                    $stmtExisteMascota->execute([
                        ':id'     => $mascotaId,
                        ':estado' => 'Activo',
                    ]);

                    if (!$stmtExisteMascota->fetch()) {
                        $mensajeTexto = 'La mascota solicitada no existe o se encuentra inactiva.';
                        $mensajeTipo = 'error';
                    } else {
                        $stmtActualizar = $pdo->prepare(
                            "UPDATE mascotas
                             SET nombre = :nombre, especie = :especie, raza = :raza, edad_anios = :edad_anios, edad_meses = :edad_meses, usuario_id = :usuario_id
                             WHERE id = :id AND estado = :estado"
                        );
                        $stmtActualizar->execute([
                            ':nombre'     => $nombre,
                            ':especie'    => $especie,
                            ':raza'       => $raza,
                            ':edad_anios' => $edadAnios,
                            ':edad_meses' => $edadMeses,
                            ':usuario_id' => $duenoId,
                            ':id'         => $mascotaId,
                            ':estado'     => 'Activo',
                        ]);

                        $mensajeTexto = 'Datos de la mascota actualizados correctamente.';
                        $mensajeTipo = 'informacion';
                    }
                } else {
                    $stmtInsertar = $pdo->prepare(
                        "INSERT INTO mascotas (nombre, especie, raza, edad_anios, edad_meses, usuario_id, estado)
                         VALUES (:nombre, :especie, :raza, :edad_anios, :edad_meses, :usuario_id, :estado)"
                    );
                    $stmtInsertar->execute([
                        ':nombre'     => $nombre,
                        ':especie'    => $especie,
                        ':raza'       => $raza,
                        ':edad_anios' => $edadAnios,
                        ':edad_meses' => $edadMeses,
                        ':usuario_id' => $duenoId,
                        ':estado'     => 'Activo',
                    ]);

                    $mensajeTexto = 'Mascota registrada correctamente en la base de datos.';
                    $mensajeTipo = 'informacion';

                    $valores = [
                        'nombre'     => '',
                        'especie'    => '',
                        'raza'       => '',
                        'edad_anios' => '0',
                        'edad_meses' => '0',
                        'dueno'      => '',
                    ];
                }
            } catch (PDOException $e) {
                error_log('Error al guardar mascota en formulario-mascota.php: ' . $e->getMessage());
                $mensajeTexto = 'Ocurrió un error interno al guardar la mascota en la base de datos.';
                $mensajeTipo = 'error';
            }
        }
    }
}

$tituloPagina = $modoEdicion ? 'Editar mascota' : 'Registrar mascota';
$textoBoton   = $modoEdicion ? 'Guardar cambios' : 'Registrar mascota';
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

    <!-- Reutiliza el diseño general de los paneles. -->
    <link rel="stylesheet" href="../assets/css/panel.css?v=<?= filemtime(__DIR__ . '/../assets/css/panel.css') ?>">
    <link rel="stylesheet" href="../assets/css/admin.css?v=<?= filemtime(__DIR__ . '/../assets/css/admin.css') ?>">
    <link rel="stylesheet" href="../assets/css/admin-formularios.css?v=<?= filemtime(__DIR__ . '/../assets/css/admin-formularios.css') ?>">
    <link rel="stylesheet" href="../assets/css/tema.css?v=<?= filemtime(__DIR__ . '/../assets/css/tema.css') ?>">
    
    <!-- defer espera a que el HTML exista antes de conectar filtros y validaciones. -->
    <script src="../assets/js/interfaz.js?v=<?= filemtime(__DIR__ . '/../assets/js/interfaz.js') ?>" defer></script>
    <script src="../assets/js/formulario-mascota.js?v=<?= filemtime(__DIR__ . '/../assets/js/formulario-mascota.js') ?>" defer></script>
</head>

<body class="pagina-panel" data-modulo="mascotas">
    <div class="estructura-panel">

        <!-- Barra lateral de navegación administrativa -->
        <aside class="barra-lateral">
            <a href="../index.php" class="marca-panel">
                <span aria-hidden="true">🐾</span>
                <span>Cuidado de<br>tus Mascotas</span>
            </a>

            <p class="etiqueta-menu">ADMINISTRACIÓN</p>

            <nav class="menu-panel" aria-label="Menú del administrador">
                <a href="panel.php">
                    <span aria-hidden="true">⌂</span>
                    Inicio
                </a>

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
                    <p class="etiqueta-seccion">ADMINISTRACIÓN / MASCOTAS</p>
                    <h1><?= htmlspecialchars($tituloPagina, ENT_QUOTES, 'UTF-8') ?></h1>
                </div>

                <a href="mascotas.php" class="enlace-volver">
                    ← Volver a mascotas
                </a>
            </header>

            <p class="descripcion-listado">
                Completa los datos para registrar una nueva mascota atendida en la veterinaria.
            </p>

            <section class="formulario-admin" aria-labelledby="titulo-datos">
                <h2 id="titulo-datos">Datos de la mascota</h2>

                <p class="ayuda-formulario">
                    Todos los campos marcados con asterisco (*) son obligatorios.
                </p>

                <form id="formulario-mascota" method="post" action="formulario-mascota.php<?= $modoEdicion ? '?id=' . (int) $mascotaId : '' ?>">
                    <?php if ($modoEdicion): ?>
                        <input type="hidden" name="id" value="<?= (int) $mascotaId ?>">
                    <?php endif; ?>

                    <div class="rejilla-formulario">
                        <div class="campo-admin">
                            <label for="nombre">Nombre de la mascota *</label>
                            <input
                                type="text"
                                id="nombre"
                                name="nombre"
                                placeholder="Ej. Max, Luna, Toby"
                                autocomplete="off"
                                maxlength="100"
                                value="<?= htmlspecialchars($valores['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                required
                            >
                        </div>

                        <div class="campo-admin">
                            <label for="especie">Especie *</label>
                            <select id="especie" name="especie" required>
                                <option value="" disabled <?= $valores['especie'] === '' ? 'selected' : '' ?>>Selecciona una especie</option>
                                <?php foreach ($especiesPermitidas as $claveEspecie => $etiquetaEspecie): ?>
                                    <option
                                        value="<?= htmlspecialchars($claveEspecie, ENT_QUOTES, 'UTF-8') ?>"
                                        <?= $valores['especie'] === $claveEspecie ? 'selected' : '' ?>
                                    >
                                        <?= htmlspecialchars($etiquetaEspecie, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="campo-admin">
                            <label for="raza">Raza *</label>
                            <input
                                type="text"
                                id="raza"
                                name="raza"
                                placeholder="Ej. Golden Retriever, Siamés, Mestizo"
                                autocomplete="off"
                                maxlength="100"
                                value="<?= htmlspecialchars($valores['raza'], ENT_QUOTES, 'UTF-8') ?>"
                                required
                            >
                        </div>

                        <div class="campo-admin">
                            <label for="edad-anios">Edad detallada (años y meses) *</label>
                            <div class="grupo-edad">
                                <div class="subcampo-edad">
                                    <input
                                        type="number"
                                        id="edad-anios"
                                        name="edad_anios"
                                        aria-label="Edad en años"
                                        placeholder="0"
                                        min="0"
                                        max="100"
                                        step="1"
                                        value="<?= htmlspecialchars($valores['edad_anios'], ENT_QUOTES, 'UTF-8') ?>"
                                        required
                                    >
                                    <span>años</span>
                                </div>

                                <div class="subcampo-edad">
                                    <input
                                        type="number"
                                        id="edad-meses"
                                        name="edad_meses"
                                        aria-label="Edad en meses"
                                        placeholder="0"
                                        min="0"
                                        max="11"
                                        step="1"
                                        value="<?= htmlspecialchars($valores['edad_meses'], ENT_QUOTES, 'UTF-8') ?>"
                                        required
                                    >
                                    <span>meses</span>
                                </div>
                            </div>
                        </div>

                        <div class="campo-admin">
                            <label for="dueno">Dueño / Cliente *</label>
                            <select id="dueno" name="dueno" required>
                                <option value="" disabled <?= $valores['dueno'] === '' ? 'selected' : '' ?>>Selecciona un cliente</option>
                                <?php foreach ($clientes as $cli): ?>
                                    <option
                                        value="<?= htmlspecialchars((string) $cli['id'], ENT_QUOTES, 'UTF-8') ?>"
                                        <?= (string) $valores['dueno'] === (string) $cli['id'] ? 'selected' : '' ?>
                                    >
                                        <?= htmlspecialchars($cli['nombre'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($cli['correo'], ENT_QUOTES, 'UTF-8') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <p
                        id="mensaje-mascota"
                        class="mensaje-admin<?= $mensajeTipo !== '' ? ' ' . htmlspecialchars($mensajeTipo, ENT_QUOTES, 'UTF-8') : '' ?>"
                        role="status"
                    ><?= htmlspecialchars($mensajeTexto, ENT_QUOTES, 'UTF-8') ?></p>

                    <div class="acciones-formulario">
                        <button type="submit" class="boton-admin">
                            <?= htmlspecialchars($textoBoton, ENT_QUOTES, 'UTF-8') ?>
                        </button>

                        <a href="mascotas.php" class="boton-cancelar">
                            Cancelar
                        </a>
                    </div>
                </form>
            </section>

            <p class="aviso-demostracion">
                Formulario conectado a MySQL mediante PDO y consultas preparadas.
            </p>
        </main>
    </div>
</body>
</html>
