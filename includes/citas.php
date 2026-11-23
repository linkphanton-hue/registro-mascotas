<?php
// Las entradas admin/citas.php y cliente/citas.php indican el rol antes de cargar esta pantalla.
if (!isset($rolCitas) || !in_array($rolCitas, ['admin', 'cliente'], true)) {
    http_response_code(403);
    exit;
}

require_once __DIR__ . '/sesion.php';
requerirRol($rolCitas, '../login.php');
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/notificaciones-citas.php';

require_once __DIR__ . '/horarios-citas.php';

// La preferencia usa un turno predeterminado; la confirmación sigue a cargo del administrador.
function leerDisponibilidadCita(array $datos, DateTimeZone $zona): array
{
    $fecha = $datos['fecha_preferida'] ?? '';
    $hora = $datos['hora_preferida'] ?? '';
    $inicio = validarTurnoCita($fecha, $hora, $zona);
    return [
        'fecha_preferida' => $inicio->format('Y-m-d'),
        'hora_desde' => $inicio->format('H:i'),
        'hora_hasta' => $inicio->modify('+1 hour')->format('H:i'),
    ];
}

$esAdministrador = $rolCitas === 'admin';
$usuarioId = (int) $_SESSION['usuario_id'];
$zonaCitas = new DateTimeZone('America/Santo_Domingo');
$ahora = new DateTimeImmutable('now', $zonaCitas);
$mensajeTexto = '';
$mensajeTipo = '';
$mascotas = [];
$servicios = [];
$mascotaNueva = filter_var($_GET['mascota'] ?? null, FILTER_VALIDATE_INT);
$citas = [];
$correosPendientes = 0;
$configCorreo = configuracionCorreoCitas();
$correoConfigurado = !empty($configCorreo['habilitado']);
$correoDemostracion = ($configCorreo['modo'] ?? 'smtp') === 'demostracion';
$valores = ['mascota_id' => (string) $mascotaNueva, 'servicio_id' => (string) filter_var($_GET['servicio'] ?? null, FILTER_VALIDATE_INT), 'motivo' => '', 'fecha_preferida' => '', 'hora_preferida' => ''];
$programacion = ['cita_id' => 0, 'fecha' => '', 'hora' => ''];
$peticionCambio = ['cita_id' => 0, 'motivo' => '', 'fecha_preferida' => '', 'hora_preferida' => ''];

// El token de sesión protege las operaciones enviadas desde los formularios.
if (!isset($_SESSION['csrf_citas'])) {
    $_SESSION['csrf_citas'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_citas'];
$solicitudToken = bin2hex(random_bytes(32));

if (isset($_SESSION['aviso_citas'])) {
    $mensajeTexto = $_SESSION['aviso_citas'];
    $mensajeTipo = 'informacion';
    unset($_SESSION['aviso_citas']);
}

if (!$pdo) {
    $mensajeTexto = $errorConexion ?? 'No se pudo conectar con la base de datos.';
    $mensajeTipo = 'error';
} else {
    try {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tokenRecibido = $_POST['csrf'] ?? '';
            if (!is_string($tokenRecibido) || !hash_equals($csrf, $tokenRecibido)) {
                throw new InvalidArgumentException('La sesión del formulario venció. Recarga la página e inténtalo de nuevo.');
            }

            if ($esAdministrador && ($_POST['accion'] ?? '') === 'reenviar_correos') {
                if (!$correoConfigurado) {
                    throw new InvalidArgumentException('Configura la cuenta remitente para enviar las confirmaciones pendientes.');
                }
                $pendientes = $pdo->query("SELECT id FROM notificaciones_citas WHERE estado_correo IN ('Pendiente', 'Error') ORDER BY id LIMIT 5")->fetchAll(PDO::FETCH_COLUMN);
                $enviados = 0;
                foreach ($pendientes as $idPendiente) {
                    if (enviarConfirmacionCita($pdo, (int) $idPendiente)) {
                        $enviados++;
                    }
                }
                $_SESSION['aviso_citas'] = ($correoDemostracion ? 'Correos simulados: ' : 'Confirmaciones enviadas por correo: ') . $enviados . '. Consulta el estado de los envíos en el listado.';
                header('Location: citas.php');
                exit;
            }

            if (!$esAdministrador && ($_POST['accion'] ?? '') === 'solicitar') {
                foreach (array_keys($valores) as $campo) {
                    $valor = $_POST[$campo] ?? '';
                    $valores[$campo] = is_string($valor) ? trim($valor) : '';
                }
                $mascotaId = filter_var($valores['mascota_id'], FILTER_VALIDATE_INT);
                $tokenSolicitud = $_POST['solicitud_token'] ?? '';
                if (!is_string($tokenSolicitud) || !preg_match('/^[a-f0-9]{64}$/D', $tokenSolicitud)) {
                    throw new InvalidArgumentException('El formulario no es válido. Recarga la página.');
                }
                $solicitudToken = $tokenSolicitud;
                if (!$mascotaId || $mascotaId < 1) {
                    throw new InvalidArgumentException('Selecciona una de tus mascotas.');
                }
                if ($valores['motivo'] === '' || mb_strlen($valores['motivo']) > 500) {
                    throw new InvalidArgumentException('Escribe el motivo de la consulta, entre 1 y 500 caracteres.');
                }
                $disponibilidad = leerDisponibilidadCita($valores, $zonaCitas);
                // Bloquea la mascota durante la comprobación y la inserción para evitar duplicados simultáneos.
                $pdo->beginTransaction();
                $stmtMascota = $pdo->prepare(
                    "SELECT mascotas.id FROM mascotas
                     INNER JOIN usuarios ON usuarios.id = mascotas.usuario_id
                     WHERE mascotas.id = :id AND mascotas.usuario_id = :usuario_id
                       AND mascotas.estado = 'Activo' AND usuarios.estado = 'Activo'
                       AND usuarios.rol = 'cliente' FOR UPDATE"
                );
                $stmtMascota->execute([':id' => $mascotaId, ':usuario_id' => $usuarioId]);
                if (!$stmtMascota->fetch()) {
                    throw new InvalidArgumentException('La mascota seleccionada no pertenece a tu cuenta o está inactiva.');
                }

                // Un reenvío del mismo formulario devuelve la solicitud existente.
                $stmtReenvio = $pdo->prepare('SELECT id, mascota_id, usuario_id FROM citas WHERE solicitud_token = :token');
                $stmtReenvio->execute([':token' => $tokenSolicitud]);
                $solicitudExistente = $stmtReenvio->fetch();
                if ($solicitudExistente && ((int) $solicitudExistente['mascota_id'] !== $mascotaId || (int) $solicitudExistente['usuario_id'] !== $usuarioId)) {
                    throw new InvalidArgumentException('La solicitud ya fue utilizada. Recarga el formulario.');
                }
                if (!$solicitudExistente) {
                    $servicioId = filter_var($valores['servicio_id'], FILTER_VALIDATE_INT);
                    $stmtServicio = $pdo->prepare('SELECT nombre FROM servicios_veterinarios WHERE id = :id AND activo = 1 FOR UPDATE');
                    $stmtServicio->execute([':id' => $servicioId ?: 0]);
                    $servicioNombre = $stmtServicio->fetchColumn();
                    if ($servicioNombre === false) {
                        throw new InvalidArgumentException('Selecciona un servicio veterinario disponible.');
                    }
                    $inicioPreferido = validarTurnoCita($disponibilidad['fecha_preferida'], $disponibilidad['hora_desde'], $zonaCitas);
                    comprobarTurnoDisponibleCita($pdo, $inicioPreferido, $zonaCitas);
                    $stmtInsertar = $pdo->prepare(
                        'INSERT INTO citas (mascota_id, usuario_id, motivo, servicio_nombre, solicitud_token, fecha_preferida, hora_desde, hora_hasta)
                         VALUES (:mascota_id, :usuario_id, :motivo, :servicio_nombre, :solicitud_token, :fecha_preferida, :hora_desde, :hora_hasta)'
                    );
                    $stmtInsertar->execute([
                        ':mascota_id' => $mascotaId,
                        ':usuario_id' => $usuarioId,
                        ':motivo' => $valores['motivo'],
                        ':servicio_nombre' => $servicioNombre,
                        ':solicitud_token' => $tokenSolicitud,
                        ':fecha_preferida' => $disponibilidad['fecha_preferida'],
                        ':hora_desde' => $disponibilidad['hora_desde'],
                        ':hora_hasta' => $disponibilidad['hora_hasta'],
                    ]);
                }
                $pdo->commit();
                $_SESSION['aviso_citas'] = 'Solicitud registrada. El administrador asignará la fecha y hora de tu cita.';
                header('Location: citas.php');
                exit;
            }

            if (!$esAdministrador && ($_POST['accion'] ?? '') === 'solicitar_cambio') {
                $citaId = filter_var($_POST['cita_id'] ?? null, FILTER_VALIDATE_INT);
                $version = filter_var($_POST['version'] ?? null, FILTER_VALIDATE_INT);
                $motivoCambio = $_POST['motivo_cambio'] ?? '';
                $motivoCambio = is_string($motivoCambio) ? trim($motivoCambio) : '';
                $peticionCambio = ['cita_id' => (int) $citaId, 'motivo' => $motivoCambio];
                foreach (['fecha_preferida', 'hora_preferida'] as $campo) {
                    $valor = $_POST[$campo] ?? '';
                    $peticionCambio[$campo] = is_string($valor) ? trim($valor) : '';
                }
                if (!$citaId || $citaId < 1 || $version === false || $version === null || $version < 0) {
                    throw new InvalidArgumentException('La cita no es válida. Recarga el listado.');
                }
                if ($motivoCambio === '' || mb_strlen($motivoCambio) > 500) {
                    throw new InvalidArgumentException('Explica el motivo del cambio en un máximo de 500 caracteres.');
                }
                $disponibilidad = leerDisponibilidadCita($peticionCambio, $zonaCitas);
                $inicioPreferido = validarTurnoCita($disponibilidad['fecha_preferida'], $disponibilidad['hora_desde'], $zonaCitas);
                comprobarTurnoDisponibleCita($pdo, $inicioPreferido, $zonaCitas);
                // Conserva la fecha aprobada hasta que el administrador resuelva el cambio.
                $stmtCambio = $pdo->prepare(
                    "UPDATE citas
                     INNER JOIN mascotas ON mascotas.id = citas.mascota_id
                     INNER JOIN usuarios ON usuarios.id = citas.usuario_id
                     SET citas.estado = 'Cambio solicitado', citas.motivo_cambio = :motivo,
                         citas.estado_cambio = 'Pendiente', citas.version = citas.version + 1,
                         citas.fecha_preferida = :fecha_preferida, citas.hora_desde = :hora_desde, citas.hora_hasta = :hora_hasta
                     WHERE citas.id = :id AND citas.usuario_id = :usuario_id
                       AND citas.estado = 'Confirmada' AND citas.version = :version
                       AND citas.fecha_hora > :ahora AND mascotas.estado = 'Activo'
                       AND mascotas.usuario_id = citas.usuario_id AND usuarios.estado = 'Activo'"
                );
                $stmtCambio->execute([
                    ':motivo' => $motivoCambio,
                    ':fecha_preferida' => $disponibilidad['fecha_preferida'],
                    ':hora_desde' => $disponibilidad['hora_desde'],
                    ':hora_hasta' => $disponibilidad['hora_hasta'],
                    ':id' => $citaId,
                    ':usuario_id' => $usuarioId,
                    ':version' => $version,
                    ':ahora' => (new DateTimeImmutable('now', $zonaCitas))->format('Y-m-d H:i:s'),
                ]);
                if ($stmtCambio->rowCount() !== 1) {
                    throw new InvalidArgumentException('Solo puedes pedir cambios de una cita propia, confirmada y futura. Recarga para ver su estado actual.');
                }
                $_SESSION['aviso_citas'] = 'Petición de cambio enviada. Consulta aquí la respuesta del administrador.';
                header('Location: citas.php');
                exit;
            }

            if ($esAdministrador && in_array($_POST['accion'] ?? '', ['confirmar', 'rechazar', 'rechazar_cambio'], true)) {
                $citaId = filter_var($_POST['cita_id'] ?? null, FILTER_VALIDATE_INT);
                if (!$citaId || $citaId < 1) {
                    throw new InvalidArgumentException('Selecciona una cita válida.');
                }
                $version = filter_var($_POST['version'] ?? null, FILTER_VALIDATE_INT);
                if ($version === false || $version === null || $version < 0) {
                    throw new InvalidArgumentException('Recarga el listado antes de gestionar esta solicitud.');
                }
                $accion = $_POST['accion'];
                $nuevoEstado = $accion === 'rechazar' ? 'Rechazada' : 'Confirmada';
                $parametros = [':estado' => $nuevoEstado, ':id' => $citaId, ':version' => $version];
                $asignacionFecha = '';
                if ($accion === 'confirmar') {
                    $programacion['cita_id'] = $citaId;
                    foreach (['fecha', 'hora'] as $campo) {
                        $valor = $_POST[$campo] ?? '';
                        $programacion[$campo] = is_string($valor) ? trim($valor) : '';
                    }
                    $fechaCita = validarTurnoCita($programacion['fecha'], $programacion['hora'], $zonaCitas);

                    // Bloquea la agenda completa durante la confirmación para impedir reservas simultáneas.
                    $pdo->beginTransaction();
                    $candadoAgenda = $pdo->query('SELECT id FROM agenda_citas WHERE id = 1 FOR UPDATE')->fetchColumn();
                    if (!$candadoAgenda) {
                        throw new InvalidArgumentException('La agenda no está disponible. Inténtalo de nuevo.');
                    }
                    $stmtMascotaCita = $pdo->prepare(
                        'SELECT mascotas.id, citas.fecha_hora, citas.estado FROM mascotas
                         INNER JOIN citas ON citas.mascota_id = mascotas.id
                         WHERE citas.id = :id FOR UPDATE'
                    );
                    $stmtMascotaCita->execute([':id' => $citaId]);
                    $mascotaCita = $stmtMascotaCita->fetch();
                    if (!$mascotaCita) {
                        throw new InvalidArgumentException('La cita seleccionada no existe.');
                    }
                    $fechaSql = $fechaCita->format('Y-m-d H:i:s');
                    if ($mascotaCita['estado'] === 'Cambio solicitado' && $mascotaCita['fecha_hora'] === $fechaSql) {
                        throw new InvalidArgumentException('Elige un horario diferente para aprobar la petición de cambio.');
                    }
                    comprobarTurnoDisponibleCita($pdo, $fechaCita, $zonaCitas, $citaId);
                    $asignacionFecha = ', citas.fecha_hora = :fecha_hora';
                    $parametros[':fecha_hora'] = $fechaSql;
                }
                $sql = "UPDATE citas
                        INNER JOIN mascotas ON mascotas.id = citas.mascota_id
                        INNER JOIN usuarios ON usuarios.id = citas.usuario_id
                        SET citas.estado_cambio = CASE
                            WHEN :accion_aprobar = 'confirmar' AND citas.estado = 'Cambio solicitado' THEN 'Aprobado'
                            WHEN :accion_rechazar = 'rechazar_cambio' THEN 'Rechazado'
                            ELSE citas.estado_cambio END,
                            citas.estado = :estado, citas.version = citas.version + 1" . $asignacionFecha . "
                        WHERE citas.id = :id AND citas.version = :version";
                $parametros[':accion_aprobar'] = $accion;
                $parametros[':accion_rechazar'] = $accion;
                if ($accion === 'confirmar') {
                    $sql .= " AND citas.estado IN ('Pendiente', 'Cambio solicitado')";
                } elseif ($accion === 'rechazar_cambio') {
                    $sql .= " AND citas.estado = 'Cambio solicitado'";
                } else {
                    $sql .= " AND citas.estado = 'Pendiente'";
                }
                if ($accion === 'confirmar') {
                    $sql .= " AND mascotas.estado = 'Activo' AND usuarios.estado = 'Activo'
                              AND usuarios.rol = 'cliente' AND mascotas.usuario_id = citas.usuario_id";
                }
                $stmtEstado = $pdo->prepare($sql);
                $stmtEstado->execute($parametros);
                if ($stmtEstado->rowCount() !== 1) {
                    throw new InvalidArgumentException('La solicitud ya fue atendida o no puede confirmarse. Recarga el listado.');
                }
                $notificacionId = null;
                if ($accion === 'confirmar') {
                    $notificacionId = registrarConfirmacionCita(
                        $pdo, $citaId, (string) $_SESSION['usuario_correo'], $mascotaCita['estado'] === 'Cambio solicitado'
                    );
                }
                if ($pdo->inTransaction()) {
                    $pdo->commit();
                }
                $correoEnviado = false;
                if ($notificacionId !== null) {
                    try {
                        $correoEnviado = enviarConfirmacionCita($pdo, $notificacionId);
                    } catch (Throwable $e) {
                        error_log('Confirmación guardada, correo pendiente: ' . $e->getMessage());
                    }
                }
                $_SESSION['aviso_citas'] = $accion === 'rechazar_cambio'
                    ? 'Petición de cambio rechazada. Se mantiene el horario aprobado.'
                    : 'Cita ' . mb_strtolower($nuevoEstado) . ' correctamente.';
                if ($notificacionId !== null) {
                    $_SESSION['aviso_citas'] .= $correoEnviado
                        ? ($correoDemostracion ? ' El cliente tiene su aviso en la página y el correo de demostración está disponible para consultar.' : ' El cliente tiene su aviso en la página y el correo fue enviado.')
                        : ' El cliente tiene su aviso en la página. El correo quedó pendiente de envío.';
                }
                header('Location: citas.php');
                exit;
            }
            throw new InvalidArgumentException('La operación solicitada no está permitida.');
        }
    } catch (InvalidArgumentException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $mensajeTexto = $e->getMessage();
        $mensajeTipo = 'error';
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Error al procesar cita: ' . $e->getMessage());
        $mensajeTexto = 'No se pudo guardar la operación. Inténtalo de nuevo.';
        $mensajeTipo = 'error';
    }

    try {
        if (!$esAdministrador) {
            $stmtMascotas = $pdo->prepare("SELECT id, nombre FROM mascotas WHERE usuario_id = :usuario_id AND estado = 'Activo' ORDER BY nombre");
            $stmtMascotas->execute([':usuario_id' => $usuarioId]);
            $mascotas = $stmtMascotas->fetchAll();
            $servicios = $pdo->query('SELECT id, nombre FROM servicios_veterinarios WHERE activo = 1 ORDER BY id')->fetchAll();
        }
        $sql = 'SELECT citas.id, citas.fecha_hora, citas.motivo, citas.servicio_nombre, citas.estado,
                       citas.motivo_cambio, citas.estado_cambio, citas.version,
                       citas.fecha_preferida, citas.hora_desde, citas.hora_hasta,
                       (SELECT estado_correo FROM notificaciones_citas
                        WHERE cita_id = citas.id ORDER BY id DESC LIMIT 1) AS estado_correo,
                       (SELECT id FROM notificaciones_citas
                        WHERE cita_id = citas.id ORDER BY id DESC LIMIT 1) AS notificacion_correo_id,
                       mascotas.nombre AS mascota_nombre, usuarios.nombre AS cliente_nombre,
                       usuarios.correo AS cliente_correo
                FROM citas
                INNER JOIN mascotas ON mascotas.id = citas.mascota_id
                INNER JOIN usuarios ON usuarios.id = citas.usuario_id';
        $parametros = [];
        if (!$esAdministrador) {
            $sql .= ' WHERE citas.usuario_id = :usuario_id';
            $parametros[':usuario_id'] = $usuarioId;
        }
        $sql .= " ORDER BY (citas.estado IN ('Pendiente', 'Cambio solicitado')) DESC, citas.fecha_hora ASC, citas.id ASC";
        $stmtCitas = $pdo->prepare($sql);
        $stmtCitas->execute($parametros);
        $citas = $stmtCitas->fetchAll();
        if ($esAdministrador) {
            $correosPendientes = (int) $pdo->query("SELECT COUNT(*) FROM notificaciones_citas WHERE estado_correo IN ('Pendiente', 'Error')")->fetchColumn();
        }
    } catch (PDOException $e) {
        error_log('Error al consultar citas: ' . $e->getMessage());
        $mensajeTexto = 'No fue posible cargar las citas. Comprueba que la tabla de citas esté instalada.';
        $mensajeTipo = 'error';
    }
}

$menuCitas = $esAdministrador
    ? ['panel.php' => 'Inicio', 'mascotas.php' => 'Mascotas', 'clientes.php' => 'Clientes', 'citas.php' => 'Citas', 'servicios.php' => 'Servicios', 'mensajes.php' => 'Mensajes']
    : ['panel.php' => 'Inicio', 'mis-mascotas.php' => 'Mis mascotas', 'citas.php' => 'Citas', 'servicios.php' => 'Servicios', 'perfil.php' => 'Mi perfil'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citas | Cuidado de tus Mascotas</title>
    <!-- Carga el script para inicializar el tema -->
    <script src="../assets/js/tema-inicial.js?v=<?= filemtime(__DIR__ . '/../assets/js/tema-inicial.js') ?>"></script>
    <link rel="stylesheet" href="../assets/css/panel.css?v=<?= filemtime(__DIR__ . '/../assets/css/panel.css') ?>">
    <link rel="stylesheet" href="../assets/css/admin.css?v=<?= filemtime(__DIR__ . '/../assets/css/admin.css') ?>">
    <link rel="stylesheet" href="../assets/css/admin-formularios.css?v=<?= filemtime(__DIR__ . '/../assets/css/admin-formularios.css') ?>">
    <link rel="stylesheet" href="../assets/css/tema.css?v=<?= filemtime(__DIR__ . '/../assets/css/tema.css') ?>">
    <link rel="stylesheet" href="../assets/css/citas.css?v=<?= filemtime(__DIR__ . '/../assets/css/citas.css') ?>">
    <link rel="stylesheet" href="../assets/css/notificaciones-citas.css?v=<?= filemtime(__DIR__ . '/../assets/css/notificaciones-citas.css') ?>">
    <script src="../assets/js/interfaz.js?v=<?= filemtime(__DIR__ . '/../assets/js/interfaz.js') ?>" defer></script>
    <script src="../assets/js/horarios-citas.js?v=<?= filemtime(__DIR__ . '/../assets/js/horarios-citas.js') ?>" defer></script>
</head>
<body class="pagina-panel" data-modulo="citas">
    <div class="estructura-panel">
        <aside class="barra-lateral">
            <a href="../index.php" class="marca-panel">
                <span aria-hidden="true">🐾</span>
                <span>Cuidado de<br>tus Mascotas</span>
            </a>
            <p class="etiqueta-menu"><?= $esAdministrador ? 'ADMINISTRACIÓN' : 'MI ESPACIO' ?></p>
            <nav class="menu-panel" aria-label="Menú de navegación">
                <?php foreach ($menuCitas as $ruta => $etiqueta): ?>
                    <a href="<?= htmlspecialchars($ruta, ENT_QUOTES, 'UTF-8') ?>"<?= $ruta === 'citas.php' ? ' class="activo" aria-current="page"' : '' ?>>
                        <?= htmlspecialchars($etiqueta, ENT_QUOTES, 'UTF-8') ?>
                    </a>
                <?php endforeach; ?>
            </nav>
            <a href="../index.php" class="volver-inicio">← Volver al sitio</a>
            <a href="../logout.php" class="volver-inicio">Cerrar sesión</a>
        </aside>
        <main class="contenido-panel citas-contenido">
            <header class="cabecera-panel">
                <div>
                    <p class="etiqueta-seccion"><?= $esAdministrador ? 'ADMINISTRACIÓN' : 'MI ESPACIO' ?></p>
                    <h1><?= $esAdministrador ? 'Solicitudes de citas' : 'Mis citas' ?></h1>
                </div>
            </header>
            <?php if ($mensajeTexto !== ''): ?>
                <p class="mensaje-admin <?= htmlspecialchars($mensajeTipo, ENT_QUOTES, 'UTF-8') ?>" role="status">
                    <?= htmlspecialchars($mensajeTexto, ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php endif; ?>

            <?php if ($esAdministrador && $correosPendientes > 0): ?>
                <section class="formulario-admin" aria-label="Confirmaciones pendientes de correo">
                    <p>Confirmaciones pendientes de enviar por correo: <?= $correosPendientes ?>.</p>
                    <?php if (!$correoConfigurado): ?>
                        <p>El envío de correo está pendiente de configurar.</p>
                    <?php else: ?>
                        <form action="citas.php" method="post">
                            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                            <button type="submit" name="accion" value="reenviar_correos" class="boton-admin">Reintentar envíos pendientes</button>
                        </form>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
            <?php if (!$esAdministrador): ?>
                <?php require __DIR__ . '/avisos-citas.php'; ?>
                <section class="formulario-admin" aria-labelledby="titulo-solicitud">
                    <h2 id="titulo-solicitud">Solicitar una cita</h2>
                    <p class="ayuda-formulario">Selecciona tu mascota, el motivo, la fecha y un turno disponible de lunes a viernes, de 8:00 a. m. a 4:00 p. m. El administrador asignará la fecha y hora al confirmar tu solicitud.</p>
                    <?php if (!$mascotas): ?>
                        <p>Registra a tu mascota para solicitar su primera cita.</p>
                        <a class="boton-admin" href="mis-mascotas.php?desde=citas&amp;servicio=<?= (int) $valores['servicio_id'] ?>#registrar-mascota">Registrar mascota</a>
                    <?php elseif (!$servicios): ?>
                        <p>Por el momento no hay servicios disponibles para solicitar una cita.</p>
                    <?php else: ?>
                        <form action="citas.php" method="post" data-agenda-citas data-horarios-url="../api/horarios-citas.php">
                            <input type="hidden" name="accion" value="solicitar">
                            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="solicitud_token" value="<?= htmlspecialchars($solicitudToken, ENT_QUOTES, 'UTF-8') ?>">
                            <div class="rejilla-formulario">
                                <div class="campo-admin">
                                    <label for="mascota-cita">Mascota</label>
                                    <select id="mascota-cita" name="mascota_id" required>
                                        <option value="">Selecciona tu mascota</option>
                                        <?php foreach ($mascotas as $mascota): ?>
                                            <option value="<?= (int) $mascota['id'] ?>"<?= $valores['mascota_id'] === (string) $mascota['id'] ? ' selected' : '' ?>><?= htmlspecialchars($mascota['nombre'], ENT_QUOTES, 'UTF-8') ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="campo-admin">
                                    <label for="servicio-cita">Servicio veterinario</label>
                                    <select id="servicio-cita" name="servicio_id" required>
                                        <option value="">Selecciona un servicio</option>
                                        <?php foreach ($servicios as $servicio): ?>
                                            <option value="<?= (int) $servicio['id'] ?>"<?= $valores['servicio_id'] === (string) $servicio['id'] ? ' selected' : '' ?>><?= htmlspecialchars($servicio['nombre'], ENT_QUOTES, 'UTF-8') ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="campo-admin">
                                    <label for="fecha_preferida-solicitud">Fecha preferida</label>
                                    <input type="date" id="fecha_preferida-solicitud" name="fecha_preferida" data-fecha-cita min="<?= $ahora->format('Y-m-d') ?>"
                                           value="<?= htmlspecialchars($valores['fecha_preferida'], ENT_QUOTES, 'UTF-8') ?>" required>
                                </div>
                                <div class="campo-admin">
                                    <label for="hora-solicitud">Horario disponible</label>
                                    <select id="hora-solicitud" name="hora_preferida" data-hora-cita
                                            data-hora-seleccionada="<?= htmlspecialchars($valores['hora_preferida'], ENT_QUOTES, 'UTF-8') ?>" required disabled>
                                        <option value="">Selecciona primero una fecha</option>
                                    </select>
                                    <small data-estado-horarios role="status">Turnos de una hora, de lunes a viernes.</small>
                                </div>
                                <div class="campo-admin">
                                    <label for="motivo-cita">Motivo de consulta</label>
                                    <textarea id="motivo-cita" name="motivo" maxlength="500" required><?= htmlspecialchars($valores['motivo'], ENT_QUOTES, 'UTF-8') ?></textarea>
                                </div>
                            </div>
                            <div class="acciones-formulario">
                                <button type="submit" class="boton-admin">Solicitar cita</button>
                                <a href="mis-mascotas.php?desde=citas#registrar-mascota">Registrar otra mascota</a>
                            </div>
                        </form>
                    <?php endif; ?>
                </section>
            <?php endif; ?>

            <section aria-labelledby="titulo-listado-citas">
                <p class="descripcion-listado">Las fechas y horas asignadas se muestran en horario de República Dominicana.</p>
                <h2 id="titulo-listado-citas"><?= $esAdministrador ? 'Citas recibidas' : 'Estado de mis solicitudes' ?></h2>
                <div class="tabla-contenedor" role="region" aria-labelledby="titulo-listado-citas" tabindex="0">
                    <table class="tabla-admin citas-tabla">
                        <thead>
                            <tr>
                                <th scope="col">Mascota</th>
                                <?php if ($esAdministrador): ?>
                                    <th scope="col">Cliente</th>
                                <?php endif; ?>
                                <th scope="col">Horario aprobado</th>
                                <th scope="col">Disponibilidad del cliente</th>
                                <th scope="col">Servicio</th>
                                <th scope="col">Motivo</th>
                                <th scope="col">Estado</th>
                                <th scope="col">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($citas as $cita): ?>
                                <tr>
                                    <th scope="row"><?= htmlspecialchars($cita['mascota_nombre'], ENT_QUOTES, 'UTF-8') ?></th>
                                    <?php if ($esAdministrador): ?>
                                        <td>
                                            <?= htmlspecialchars($cita['cliente_nombre'], ENT_QUOTES, 'UTF-8') ?><br>
                                            <?= htmlspecialchars($cita['cliente_correo'], ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                    <?php endif; ?>
                                    <td>
                                        <?php if ($cita['fecha_hora'] !== null): ?>
                                            <?= (new DateTimeImmutable($cita['fecha_hora'], $zonaCitas))->format('d/m/Y H:i') ?>
                                        <?php else: ?>
                                            <?= $cita['estado'] === 'Pendiente' ? 'Por asignar' : 'Sin fecha asignada' ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($cita['fecha_preferida'] !== null && $cita['hora_desde'] !== null && $cita['hora_hasta'] !== null): ?>
                                            <strong><?= (new DateTimeImmutable($cita['fecha_preferida'], $zonaCitas))->format('d/m/Y') ?></strong><br>
                                            De <?= htmlspecialchars(substr($cita['hora_desde'], 0, 5), ENT_QUOTES, 'UTF-8') ?>
                                            a <?= htmlspecialchars(substr($cita['hora_hasta'], 0, 5), ENT_QUOTES, 'UTF-8') ?>
                                        <?php else: ?>
                                            Sin preferencia registrada.
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($cita['servicio_nombre'] ?? 'Sin servicio registrado', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="citas-motivo"><?= htmlspecialchars($cita['motivo'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <span class="citas-estado"><?= htmlspecialchars($cita['estado'], ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php if ($esAdministrador && $cita['estado_correo'] !== null): ?>
                                            <p>Correo: <?= htmlspecialchars($cita['estado_correo'], ENT_QUOTES, 'UTF-8') ?></p>
                                            <?php if ($cita['estado_correo'] === 'Simulado'): ?>
                                                <a href="correo-cita.php?id=<?= (int) $cita['notificacion_correo_id'] ?>" class="accion-tabla accion-editar">Ver correo simulado</a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <?php if ($cita['motivo_cambio'] !== null): ?>
                                            <p class="citas-motivo">Motivo del cambio: <?= htmlspecialchars($cita['motivo_cambio'], ENT_QUOTES, 'UTF-8') ?></p>
                                            <p>Cambio de horario: <?= htmlspecialchars($cita['estado_cambio'], ENT_QUOTES, 'UTF-8') ?></p>
                                        <?php endif; ?>
                                    </td>
                                    <?php if ($esAdministrador): ?>
                                        <td>
                                            <?php if (in_array($cita['estado'], ['Pendiente', 'Cambio solicitado'], true)): ?>
                                                <form action="citas.php" method="post" class="acciones-tabla citas-acciones" data-agenda-citas
                                                      data-horarios-url="../api/horarios-citas.php" data-excluir-cita="<?= (int) $cita['id'] ?>">
                                                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="cita_id" value="<?= (int) $cita['id'] ?>">
                                                    <input type="hidden" name="version" value="<?= (int) $cita['version'] ?>">
                                                    <div class="campo-admin">
                                                        <label for="fecha-cita-<?= (int) $cita['id'] ?>">Fecha</label>
                                                        <input type="date" id="fecha-cita-<?= (int) $cita['id'] ?>" name="fecha" data-fecha-cita
                                                               min="<?= $ahora->format('Y-m-d') ?>"
                                                               value="<?= $programacion['cita_id'] === (int) $cita['id'] ? htmlspecialchars($programacion['fecha'], ENT_QUOTES, 'UTF-8') : '' ?>" required>
                                                    </div>
                                                    <div class="campo-admin">
                                                        <label for="hora-cita-<?= (int) $cita['id'] ?>">Horario disponible</label>
                                                        <select id="hora-cita-<?= (int) $cita['id'] ?>" name="hora" data-hora-cita
                                                                data-hora-seleccionada="<?= $programacion['cita_id'] === (int) $cita['id'] ? htmlspecialchars($programacion['hora'], ENT_QUOTES, 'UTF-8') : '' ?>" required disabled>
                                                            <option value="">Selecciona primero una fecha</option>
                                                        </select>
                                                        <small data-estado-horarios role="status">Turnos de una hora, de lunes a viernes.</small>
                                                    </div>
                                                    <button class="accion-tabla accion-editar" type="submit" name="accion" value="confirmar"><?= $cita['estado'] === 'Cambio solicitado' ? 'Reprogramar y confirmar' : 'Asignar y confirmar' ?></button>
                                                    <?php if ($cita['estado'] === 'Cambio solicitado'): ?>
                                                        <button class="accion-tabla" type="submit" name="accion" value="rechazar_cambio" formnovalidate>Mantener horario aprobado</button>
                                                    <?php else: ?>
                                                        <button class="accion-tabla" type="submit" name="accion" value="rechazar" formnovalidate>Rechazar</button>
                                                    <?php endif; ?>
                                                </form>
                                            <?php else: ?>
                                                Solicitud resuelta
                                            <?php endif; ?>
                                        </td>
                                    <?php else: ?>
                                        <td>
                                            <?php if ($cita['estado'] === 'Confirmada' && $cita['fecha_hora'] !== null && new DateTimeImmutable($cita['fecha_hora'], $zonaCitas) > $ahora): ?>
                                                <form action="citas.php" method="post" class="citas-acciones" data-agenda-citas data-horarios-url="../api/horarios-citas.php">
                                                    <input type="hidden" name="accion" value="solicitar_cambio">
                                                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="cita_id" value="<?= (int) $cita['id'] ?>">
                                                    <input type="hidden" name="version" value="<?= (int) $cita['version'] ?>">
                                                    <div class="campo-admin">
                                                        <label for="fecha_preferida-cambio-<?= (int) $cita['id'] ?>">Fecha preferida</label>
                                                        <input type="date" id="fecha_preferida-cambio-<?= (int) $cita['id'] ?>" name="fecha_preferida" data-fecha-cita min="<?= $ahora->format('Y-m-d') ?>"
                                                               value="<?= $peticionCambio['cita_id'] === (int) $cita['id'] ? htmlspecialchars($peticionCambio['fecha_preferida'], ENT_QUOTES, 'UTF-8') : '' ?>" required>
                                                    </div>
                                                    <div class="campo-admin">
                                                        <label for="hora-cambio-<?= (int) $cita['id'] ?>">Horario disponible</label>
                                                        <select id="hora-cambio-<?= (int) $cita['id'] ?>" name="hora_preferida" data-hora-cita
                                                                data-hora-seleccionada="<?= $peticionCambio['cita_id'] === (int) $cita['id'] ? htmlspecialchars($peticionCambio['hora_preferida'], ENT_QUOTES, 'UTF-8') : '' ?>" required disabled>
                                                            <option value="">Selecciona primero una fecha</option>
                                                        </select>
                                                        <small data-estado-horarios role="status">Turnos de una hora, de lunes a viernes.</small>
                                                    </div>
                                                    <div class="campo-admin">
                                                        <label for="cambio-cita-<?= (int) $cita['id'] ?>">Motivo del cambio</label>
                                                        <textarea id="cambio-cita-<?= (int) $cita['id'] ?>" name="motivo_cambio" maxlength="500" required><?= $peticionCambio['cita_id'] === (int) $cita['id'] ? htmlspecialchars($peticionCambio['motivo'], ENT_QUOTES, 'UTF-8') : '' ?></textarea>
                                                    </div>
                                                    <button class="accion-tabla accion-editar" type="submit">Solicitar cambio de horario</button>
                                                </form>
                                            <?php elseif ($cita['estado'] === 'Cambio solicitado'): ?>
                                                El administrador está revisando tu disponibilidad. El horario anterior sigue visible hasta que resuelva el cambio.
                                            <?php else: ?>
                                                <?= $cita['estado'] === 'Pendiente' ? 'Esperando asignación de horario.' : 'Consulta el estado de tu cita.' ?>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (!$citas): ?>
                                <tr>
                                    <td colspan="<?= $esAdministrador ? 8 : 7 ?>">
                                        <?= $mensajeTipo === 'error' ? 'El listado no está disponible.' : 'Todavía no hay citas registradas.' ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
