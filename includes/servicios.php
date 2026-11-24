<?php
if (!isset($rolServicios) || !in_array($rolServicios, ['admin', 'cliente'], true)) {
    http_response_code(403);
    exit;
}

require_once __DIR__ . '/sesion.php';
requerirRol($rolServicios, '../login.php');

$esAdmin = $rolServicios === 'admin';
$servicios = [];
$error = $errorConexion ?? '';
$aviso = $_SESSION['aviso_servicios'] ?? '';
unset($_SESSION['aviso_servicios']);
$csrf = $_SESSION['csrf_servicios'] ??= bin2hex(random_bytes(32));

if ($pdo && $esAdmin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf'] ?? '';
    $id = filter_var($_POST['servicio_id'] ?? null, FILTER_VALIDATE_INT);
    $activo = $_POST['activo'] ?? '';
    if (!is_string($token) || !hash_equals($csrf, $token) || !$id || $id < 1 || !in_array($activo, ['0', '1'], true)) {
        $error = 'No se pudo validar el cambio de disponibilidad.';
    } else {
        try {
            $actualizar = $pdo->prepare('UPDATE servicios_veterinarios SET activo = :activo WHERE id = :id');
            $actualizar->execute([':activo' => (int) $activo, ':id' => $id]);
            $_SESSION['aviso_servicios'] = 'Disponibilidad actualizada.';
            header('Location: servicios.php', true, 303);
            exit;
        } catch (PDOException $e) {
            error_log('Error al actualizar servicio: ' . $e->getMessage());
            $error = 'No se pudo actualizar el servicio.';
        }
    }
}

if ($pdo) {
    try {
        $sql = 'SELECT id, nombre, descripcion, activo FROM servicios_veterinarios';
        if (!$esAdmin) {
            $sql .= ' WHERE activo = 1';
        }
        $servicios = $pdo->query($sql . ' ORDER BY id')->fetchAll();
    } catch (PDOException $e) {
        error_log('Error al consultar servicios: ' . $e->getMessage());
        $error = 'No se pudo cargar el catálogo de servicios.';
    }
}

$menu = $esAdmin
    ? ['panel.php' => 'Inicio', 'mascotas.php' => 'Mascotas', 'clientes.php' => 'Clientes', 'citas.php' => 'Citas', 'servicios.php' => 'Servicios', 'mensajes.php' => 'Mensajes']
    : ['panel.php' => 'Inicio', 'mis-mascotas.php' => 'Mis mascotas', 'citas.php' => 'Citas', 'servicios.php' => 'Servicios', 'perfil.php' => 'Mi perfil'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servicios veterinarios | Cuidado de tus Mascotas</title>
    <script src="../assets/js/tema-inicial.js?v=<?= filemtime(__DIR__ . '/../assets/js/tema-inicial.js') ?>"></script>
    <link rel="stylesheet" href="../assets/css/panel.css?v=<?= filemtime(__DIR__ . '/../assets/css/panel.css') ?>">
    <link rel="stylesheet" href="../assets/css/admin.css?v=<?= filemtime(__DIR__ . '/../assets/css/admin.css') ?>">
    <link rel="stylesheet" href="../assets/css/servicios.css?v=<?= filemtime(__DIR__ . '/../assets/css/servicios.css') ?>">
    <link rel="stylesheet" href="../assets/css/tema.css?v=<?= filemtime(__DIR__ . '/../assets/css/tema.css') ?>">
    <script src="../assets/js/interfaz.js?v=<?= filemtime(__DIR__ . '/../assets/js/interfaz.js') ?>" defer></script>
</head>
<body class="pagina-panel">
    <div class="estructura-panel">
        <aside class="barra-lateral">
            <a href="../index.php" class="marca-panel">
                <span aria-hidden="true">🐾</span>
                <span>Cuidado de<br>tus Mascotas</span>
            </a>
            <nav class="menu-panel" aria-label="Menú del panel">
                <?php foreach ($menu as $ruta => $etiqueta): ?>
                    <a href="<?= $ruta ?>"<?= $ruta === 'servicios.php' ? ' class="activo" aria-current="page"' : '' ?>><?= $etiqueta ?></a>
                <?php endforeach; ?>
            </nav>
            <a href="../logout.php" class="volver-inicio">Cerrar sesión</a>
        </aside>

        <main class="contenido-panel">
            <header class="cabecera-panel">
                <h1>Servicios veterinarios</h1>
            </header>
            <?php if ($error !== ''): ?>
                <p role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <?php if ($aviso !== ''): ?>
                <p role="status"><?= htmlspecialchars($aviso, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <?php if (!$servicios && $error === ''): ?>
                <p>En este momento no hay servicios disponibles.</p>
            <?php endif; ?>
            <div class="rejilla-servicios">
                <?php foreach ($servicios as $servicio): ?>
                    <article class="tarjeta-servicio">
                        <h2><?= htmlspecialchars($servicio['nombre'], ENT_QUOTES, 'UTF-8') ?></h2>
                        <p><?= htmlspecialchars($servicio['descripcion'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php if ($esAdmin): ?>
                            <p><strong><?= $servicio['activo'] ? 'Disponible para citas' : 'Desactivado' ?></strong></p>
                            <form method="post" action="servicios.php">
                                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="servicio_id" value="<?= (int) $servicio['id'] ?>">
                                <button class="boton-admin" type="submit" name="activo" value="<?= $servicio['activo'] ? '0' : '1' ?>">
                                    <?= $servicio['activo'] ? 'Desactivar' : 'Activar' ?>
                                </button>
                            </form>
                        <?php else: ?>
                            <a class="boton-admin" href="citas.php?servicio=<?= (int) $servicio['id'] ?>">Solicitar cita</a>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
</body>
</html>
