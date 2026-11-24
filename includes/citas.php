<?php
/**
 * Controlador y vista unificada del módulo de solicitudes de citas (Administrador y Cliente).
 *
 * Permite solicitar, confirmar, editar libremente (servicio, mascota, fecha, horario en 24h y motivo),
 * rechazar, cancelar y eliminar citas tanto para el administrador como para el cliente desde el modal.
 *
 * @package RegistroMascotas\Includes
 */

/* Las entradas admin/citas.php y cliente/citas.php definen $rolCitas antes de cargar este módulo */
if (!isset($rolCitas) || !in_array($rolCitas, ['admin', 'cliente'], true)) {
    http_response_code(403);
    exit;
}

require_once __DIR__ . '/sesion.php';
requerirRol($rolCitas, '../login.php');
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/notificaciones-citas.php';
require_once __DIR__ . '/horarios-citas.php';

/**
 * Valida la fecha y hora preferidas en formato de 24 horas y devuelve el rango de 1 hora.
 *
 * @param array        $datos Arreglo con fecha_preferida y hora_preferida.
 * @param DateTimeZone $zona  Zona horaria operativa de la clínica.
 * @return array{fecha_preferida:string,hora_desde:string,hora_hasta:string} Rango normalizado.
 */
function leerDisponibilidadCita(array $datos, DateTimeZone $zona): array
{
    $fecha = $datos['fecha_preferida'] ?? '';
    $hora = $datos['hora_preferida'] ?? '';
    $inicio = validarTurnoCita($fecha, $hora, $zona);
    return [
        'fecha_preferida' => $inicio->format('Y-m-d'),
        'hora_desde'      => $inicio->format('H:i'),
        'hora_hasta'      => $inicio->modify('+1 hour')->format('H:i'),
    ];
}

/**
 * Devuelve la clase CSS semántica asociada al estado actual de la cita para su insignia visual.
 *
 * @param string $estado Estado almacenado en la columna citas.estado.
 * @return string Clase CSS modificadora para .citas-estado.
 */
function claseEstadoCita(string $estado): string
{
    switch ($estado) {
        case 'Confirmada':
            return 'estado-confirmada';
        case 'Confirmada (Editada)':
            return 'estado-editada';
        case 'Cambio solicitado':
            return 'estado-cambio';
        case 'Rechazada':
            return 'estado-rechazada';
        case 'Cancelada':
            return 'estado-cancelada';
        case 'Pendiente':
        default:
            return 'estado-pendiente';
    }
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

$valores = [
    'mascota_id'      => (string) $mascotaNueva,
    'servicio_id'     => (string) filter_var($_GET['servicio'] ?? null, FILTER_VALIDATE_INT),
    'motivo'          => '',
    'fecha_preferida' => '',
    'hora_preferida'  => '',
];

/* Token CSRF en sesión para autenticar todas las peticiones POST del módulo */
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

            $accionPost = (string) ($_POST['accion'] ?? '');

            /*
             * Eliminación física de la cita (disponible tanto para Administrador como para Cliente dueño):
             * Permite retirar de la lista cualquier cita pasada, cancelada o que ya no se efectuará.
             */
            if ($accionPost === 'eliminar') {
                $citaId = filter_var($_POST['cita_id'] ?? null, FILTER_VALIDATE_INT);
                if (!$citaId || $citaId < 1) {
                    throw new InvalidArgumentException('Selecciona una cita válida para eliminar.');
                }

                if ($esAdministrador) {
                    $stmtEliminar = $pdo->prepare('DELETE FROM citas WHERE id = :id');
                    $stmtEliminar->execute([':id' => $citaId]);
                } else {
                    $stmtEliminar = $pdo->prepare('DELETE FROM citas WHERE id = :id AND usuario_id = :usuario_id');
                    $stmtEliminar->execute([':id' => $citaId, ':usuario_id' => $usuarioId]);
                }

                if ($stmtEliminar->rowCount() !== 1) {
                    throw new InvalidArgumentException('La cita ya fue eliminada o no tienes permiso para quitarla.');
                }

                $_SESSION['aviso_citas'] = 'La cita fue eliminada de la lista correctamente.';
                header('Location: citas.php');
                exit;
            }

            /* Alta de nueva solicitud de cita por parte del cliente */
            if (!$esAdministrador && $accionPost === 'solicitar') {
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

                /* Bloquea el registro de la mascota durante la validación e inserción para evitar duplicados */
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
                        ':mascota_id'      => $mascotaId,
                        ':usuario_id'      => $usuarioId,
                        ':motivo'          => $valores['motivo'],
                        ':servicio_nombre' => $servicioNombre,
                        ':solicitud_token' => $tokenSolicitud,
                        ':fecha_preferida' => $disponibilidad['fecha_preferida'],
                        ':hora_desde'      => $disponibilidad['hora_desde'],
                        ':hora_hasta'      => $disponibilidad['hora_hasta'],
                    ]);
                }
                $pdo->commit();
                $_SESSION['aviso_citas'] = 'Solicitud registrada correctamente. El administrador revisará y confirmará tu horario.';
                header('Location: citas.php');
                exit;
            }

            /*
             * Edición libre de la cita por parte del cliente desde la ventana modal:
             * Permite modificar mascota, servicio, fecha, horario (24h) y motivo en cualquier momento.
             */
            if (!$esAdministrador && in_array($accionPost, ['editar_cliente', 'solicitar_cambio'], true)) {
                $citaId = filter_var($_POST['cita_id'] ?? null, FILTER_VALIDATE_INT);
                $version = filter_var($_POST['version'] ?? null, FILTER_VALIDATE_INT);
                $mascotaId = filter_var($_POST['mascota_id'] ?? null, FILTER_VALIDATE_INT);
                $servicioNombreInput = is_string($_POST['servicio_nombre'] ?? null) ? trim($_POST['servicio_nombre']) : '';
                $motivoInput = is_string($_POST['motivo'] ?? null) ? trim($_POST['motivo']) : '';
                $motivoCambio = is_string($_POST['motivo_cambio'] ?? null) ? trim($_POST['motivo_cambio']) : '';

                $datosHorario = [
                    'fecha_preferida' => is_string($_POST['fecha_preferida'] ?? null) ? trim($_POST['fecha_preferida']) : '',
                    'hora_preferida'  => is_string($_POST['hora_preferida'] ?? null) ? trim($_POST['hora_preferida']) : '',
                ];

                if (!$citaId || $citaId < 1 || $version === false || $version === null || $version < 0) {
                    throw new InvalidArgumentException('La cita seleccionada no es válida. Recarga el listado.');
                }
                if (!$mascotaId || $mascotaId < 1) {
                    throw new InvalidArgumentException('Selecciona una de tus mascotas.');
                }
                if ($motivoInput === '' || mb_strlen($motivoInput) > 500) {
                    throw new InvalidArgumentException('Escribe el motivo de la consulta (máximo 500 caracteres).');
                }
                if (mb_strlen($motivoCambio) > 500) {
                    throw new InvalidArgumentException('La nota adicional no puede exceder 500 caracteres.');
                }

                $disponibilidad = leerDisponibilidadCita($datosHorario, $zonaCitas);
                $inicioPreferido = validarTurnoCita($disponibilidad['fecha_preferida'], $disponibilidad['hora_desde'], $zonaCitas);

                $pdo->beginTransaction();

                $stmtCitaPropia = $pdo->prepare(
                    'SELECT id, estado, version, servicio_nombre FROM citas WHERE id = :id AND usuario_id = :usuario_id FOR UPDATE'
                );
                $stmtCitaPropia->execute([':id' => $citaId, ':usuario_id' => $usuarioId]);
                $citaPropia = $stmtCitaPropia->fetch();
                if (!$citaPropia) {
                    throw new InvalidArgumentException('No se encontró la cita en tu cuenta.');
                }
                if ((int) $citaPropia['version'] !== (int) $version) {
                    throw new InvalidArgumentException('La cita fue actualizada recientemente. Recarga la página e inténtalo de nuevo.');
                }

                $stmtMascotaPropia = $pdo->prepare(
                    "SELECT id FROM mascotas WHERE id = :id AND usuario_id = :usuario_id AND estado = 'Activo' LIMIT 1"
                );
                $stmtMascotaPropia->execute([':id' => $mascotaId, ':usuario_id' => $usuarioId]);
                if (!$stmtMascotaPropia->fetch()) {
                    throw new InvalidArgumentException('La mascota seleccionada no pertenece a tu cuenta o está inactiva.');
                }

                $servicioFinal = $citaPropia['servicio_nombre'] ?? 'Consulta general';
                if ($servicioNombreInput !== '') {
                    $stmtServ = $pdo->prepare('SELECT nombre FROM servicios_veterinarios WHERE nombre = :nombre AND activo = 1 LIMIT 1');
                    $stmtServ->execute([':nombre' => $servicioNombreInput]);
                    $servEncontrado = $stmtServ->fetchColumn();
                    if ($servEncontrado !== false) {
                        $servicioFinal = $servEncontrado;
                    }
                }

                comprobarTurnoDisponibleCita($pdo, $inicioPreferido, $zonaCitas, $citaId);

                $estabaConfirmada = in_array($citaPropia['estado'], ['Confirmada', 'Confirmada (Editada)', 'Cambio solicitado'], true);
                $nuevoEstadoCliente = $estabaConfirmada ? 'Cambio solicitado' : 'Pendiente';
                $nuevoEstadoCambio = $estabaConfirmada ? 'Pendiente' : null;
                $notaCambioFinal = $estabaConfirmada
                    ? ($motivoCambio !== '' ? $motivoCambio : 'El cliente actualizó los datos o el horario preferido de la cita.')
                    : ($motivoCambio !== '' ? $motivoCambio : null);

                $stmtEditarCliente = $pdo->prepare(
                    'UPDATE citas
                     SET mascota_id = :mascota_id,
                         servicio_nombre = :servicio_nombre,
                         motivo = :motivo,
                         fecha_preferida = :fecha_preferida,
                         hora_desde = :hora_desde,
                         hora_hasta = :hora_hasta,
                         estado = :estado,
                         motivo_cambio = :motivo_cambio,
                         estado_cambio = :estado_cambio,
                         version = version + 1
                     WHERE id = :id AND usuario_id = :usuario_id AND version = :version'
                );
                $stmtEditarCliente->execute([
                    ':mascota_id'      => $mascotaId,
                    ':servicio_nombre' => $servicioFinal,
                    ':motivo'          => $motivoInput,
                    ':fecha_preferida' => $disponibilidad['fecha_preferida'],
                    ':hora_desde'      => $disponibilidad['hora_desde'],
                    ':hora_hasta'      => $disponibilidad['hora_hasta'],
                    ':estado'          => $nuevoEstadoCliente,
                    ':motivo_cambio'   => $notaCambioFinal,
                    ':estado_cambio'   => $nuevoEstadoCambio,
                    ':id'              => $citaId,
                    ':usuario_id'      => $usuarioId,
                    ':version'         => $version,
                ]);

                $pdo->commit();
                $_SESSION['aviso_citas'] = $estabaConfirmada
                    ? 'Cambios guardados. El administrador revisará tu nuevo horario solicitado.'
                    : 'Datos de tu solicitud de cita actualizados correctamente.';
                header('Location: citas.php');
                exit;
            }

            /* Gestión administrativa desde la ventana modal: confirmar, editar, rechazar, rechazar_cambio o cancelar */
            if ($esAdministrador && in_array($accionPost, ['confirmar', 'editar', 'rechazar', 'rechazar_cambio', 'cancelar'], true)) {
                $citaId = filter_var($_POST['cita_id'] ?? null, FILTER_VALIDATE_INT);
                if (!$citaId || $citaId < 1) {
                    throw new InvalidArgumentException('Selecciona una cita válida.');
                }
                $version = filter_var($_POST['version'] ?? null, FILTER_VALIDATE_INT);
                if ($version === false || $version === null || $version < 0) {
                    throw new InvalidArgumentException('Recarga el listado antes de gestionar esta solicitud.');
                }

                $accion = $accionPost;
                $correoAdmin = (string) ($_SESSION['usuario_correo'] ?? 'admin@veterinaria.com');

                $pdo->beginTransaction();
                $candadoAgenda = $pdo->query('SELECT id FROM agenda_citas WHERE id = 1 FOR UPDATE')->fetchColumn();
                if (!$candadoAgenda) {
                    throw new InvalidArgumentException('La agenda no está disponible en este momento.');
                }

                $stmtCitaActual = $pdo->prepare(
                    'SELECT citas.id, citas.fecha_hora, citas.estado, citas.version,
                            citas.servicio_nombre, citas.motivo,
                            mascotas.estado AS mascota_estado, usuarios.estado AS usuario_estado
                     FROM citas
                     INNER JOIN mascotas ON mascotas.id = citas.mascota_id
                     INNER JOIN usuarios ON usuarios.id = citas.usuario_id
                     WHERE citas.id = :id FOR UPDATE'
                );
                $stmtCitaActual->execute([':id' => $citaId]);
                $citaActual = $stmtCitaActual->fetch();

                if (!$citaActual) {
                    throw new InvalidArgumentException('La cita seleccionada no existe.');
                }
                if ((int) $citaActual['version'] !== (int) $version) {
                    throw new InvalidArgumentException('La cita fue modificada recientemente. Recarga el listado e inténtalo de nuevo.');
                }

                $estadoAnterior = (string) $citaActual['estado'];
                $tipoEventoNotificacion = 'confirmada';
                $avisoFinal = '';

                if ($accion === 'confirmar' || $accion === 'editar') {
                    if ($citaActual['mascota_estado'] !== 'Activo' || $citaActual['usuario_estado'] !== 'Activo') {
                        throw new InvalidArgumentException('No se puede programar una cita para una mascota o cuenta inactiva.');
                    }

                    $fechaInput = is_string($_POST['fecha'] ?? null) ? trim($_POST['fecha']) : '';
                    $horaInput  = is_string($_POST['hora'] ?? null) ? trim($_POST['hora']) : '';
                    $servicioAdminInput = is_string($_POST['servicio_nombre'] ?? null) ? trim($_POST['servicio_nombre']) : '';
                    $motivoAdminInput   = is_string($_POST['motivo'] ?? null) ? trim($_POST['motivo']) : '';

                    $fechaCita = validarTurnoCita($fechaInput, $horaInput, $zonaCitas);
                    $fechaSql  = $fechaCita->format('Y-m-d H:i:s');

                    comprobarTurnoDisponibleCita($pdo, $fechaCita, $zonaCitas, $citaId);

                    $servicioFinalAdmin = $servicioAdminInput !== ''
                        ? $servicioAdminInput
                        : ((string) ($citaActual['servicio_nombre'] ?? 'Consulta general'));
                    $motivoFinalAdmin = $motivoAdminInput !== ''
                        ? $motivoAdminInput
                        : ((string) $citaActual['motivo']);

                    if (mb_strlen($motivoFinalAdmin) > 500) {
                        throw new InvalidArgumentException('El motivo de la consulta no puede exceder 500 caracteres.');
                    }

                    $esEdicion = ($accion === 'editar' || $estadoAnterior !== 'Pendiente');
                    $nuevoEstado = $esEdicion ? 'Confirmada (Editada)' : 'Confirmada';
                    $nuevoEstadoCambio = ($estadoAnterior === 'Cambio solicitado') ? 'Aprobado' : null;

                    $sqlUpdate = "UPDATE citas
                                  SET estado = :estado,
                                      fecha_hora = :fecha_hora,
                                      servicio_nombre = :servicio_nombre,
                                      motivo = :motivo,
                                      estado_cambio = COALESCE(:estado_cambio, estado_cambio),
                                      version = version + 1
                                  WHERE id = :id AND version = :version";
                    $stmtUpdate = $pdo->prepare($sqlUpdate);
                    $stmtUpdate->execute([
                        ':estado'          => $nuevoEstado,
                        ':fecha_hora'      => $fechaSql,
                        ':servicio_nombre' => $servicioFinalAdmin,
                        ':motivo'          => $motivoFinalAdmin,
                        ':estado_cambio'   => $nuevoEstadoCambio,
                        ':id'              => $citaId,
                        ':version'         => $version,
                    ]);

                    if ($estadoAnterior === 'Cambio solicitado') {
                        $tipoEventoNotificacion = 'cambio_aprobado';
                        $avisoFinal = 'Cambio de horario aprobado. Estado actualizado a Confirmada (Editada).';
                    } elseif ($esEdicion) {
                        $tipoEventoNotificacion = 'editada';
                        $avisoFinal = 'Cita editada correctamente. Estado actualizado a Confirmada (Editada).';
                    } else {
                        $tipoEventoNotificacion = 'confirmada';
                        $avisoFinal = 'Cita confirmada correctamente.';
                    }
                } elseif ($accion === 'rechazar_cambio') {
                    if ($estadoAnterior !== 'Cambio solicitado') {
                        throw new InvalidArgumentException('Esta cita no tiene un cambio de horario pendiente.');
                    }
                    $stmtUpdate = $pdo->prepare(
                        "UPDATE citas
                         SET estado = 'Confirmada',
                             estado_cambio = 'Rechazado',
                             version = version + 1
                         WHERE id = :id AND version = :version"
                    );
                    $stmtUpdate->execute([':id' => $citaId, ':version' => $version]);
                    $tipoEventoNotificacion = 'cambio_rechazado';
                    $avisoFinal = 'Se mantuvo el horario confirmado anteriormente.';
                } elseif ($accion === 'rechazar') {
                    $stmtUpdate = $pdo->prepare(
                        "UPDATE citas
                         SET estado = 'Rechazada',
                             fecha_hora = NULL,
                             version = version + 1
                         WHERE id = :id AND version = :version"
                    );
                    $stmtUpdate->execute([':id' => $citaId, ':version' => $version]);
                    $tipoEventoNotificacion = 'rechazada';
                    $avisoFinal = 'Solicitud de cita marcada como Rechazada.';
                } elseif ($accion === 'cancelar') {
                    $stmtUpdate = $pdo->prepare(
                        "UPDATE citas
                         SET estado = 'Cancelada',
                             version = version + 1
                         WHERE id = :id AND version = :version"
                    );
                    $stmtUpdate->execute([':id' => $citaId, ':version' => $version]);
                    $tipoEventoNotificacion = 'cancelada';
                    $avisoFinal = 'Cita marcada como Cancelada.';
                }

                registrarNotificacionCita($pdo, $citaId, $tipoEventoNotificacion, $correoAdmin);
                $pdo->commit();

                $_SESSION['aviso_citas'] = $avisoFinal;
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
        error_log('Error al procesar cita en includes/citas.php: ' . $e->getMessage());
        $mensajeTexto = 'No se pudo guardar la operación. Inténtalo de nuevo.';
        $mensajeTipo = 'error';
    }

    try {
        $servicios = $pdo->query('SELECT id, nombre FROM servicios_veterinarios WHERE activo = 1 ORDER BY id')->fetchAll();
        if (!$esAdministrador) {
            $stmtMascotas = $pdo->prepare("SELECT id, nombre FROM mascotas WHERE usuario_id = :usuario_id AND estado = 'Activo' ORDER BY nombre");
            $stmtMascotas->execute([':usuario_id' => $usuarioId]);
            $mascotas = $stmtMascotas->fetchAll();
        }

        /* Consulta resumida incluyendo mascota_id para permitir la edición completa en el modal */
        $sql = 'SELECT citas.id, citas.mascota_id, citas.fecha_hora, citas.motivo, citas.servicio_nombre, citas.estado,
                       citas.motivo_cambio, citas.estado_cambio, citas.version,
                       citas.fecha_preferida, citas.hora_desde, citas.hora_hasta,
                       mascotas.nombre AS mascota_nombre,
                       usuarios.nombre AS cliente_nombre,
                       usuarios.correo AS cliente_correo
                FROM citas
                INNER JOIN mascotas ON mascotas.id = citas.mascota_id
                INNER JOIN usuarios ON usuarios.id = citas.usuario_id';
        $parametros = [];
        if (!$esAdministrador) {
            $sql .= ' WHERE citas.usuario_id = :usuario_id';
            $parametros[':usuario_id'] = $usuarioId;
        }
        $sql .= " ORDER BY (citas.estado IN ('Pendiente', 'Cambio solicitado')) DESC, citas.id DESC";
        $stmtCitas = $pdo->prepare($sql);
        $stmtCitas->execute($parametros);
        $citas = $stmtCitas->fetchAll();
    } catch (PDOException $e) {
        error_log('Error al consultar citas en includes/citas.php: ' . $e->getMessage());
        $mensajeTexto = 'No fue posible cargar las citas.';
        $mensajeTipo = 'error';
    }
}

$totalCitas = count($citas);
$textoContadorInicial = 'Mostrando ' . $totalCitas . ' ' . ($totalCitas === 1 ? 'cita' : 'citas');

$menuCitas = $esAdministrador
    ? ['panel.php' => 'Inicio', 'mascotas.php' => 'Mascotas', 'clientes.php' => 'Clientes', 'citas.php' => 'Citas', 'servicios.php' => 'Servicios', 'mensajes.php' => 'Mensajes']
    : ['panel.php' => 'Inicio', 'mis-mascotas.php' => 'Mis mascotas', 'citas.php' => 'Citas', 'servicios.php' => 'Servicios', 'perfil.php' => 'Mi perfil'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $esAdministrador ? 'Solicitudes de citas | Administración' : 'Mis citas' ?> | Cuidado de tus Mascotas</title>
    <script src="../assets/js/tema-inicial.js?v=<?= filemtime(__DIR__ . '/../assets/js/tema-inicial.js') ?>"></script>
    <link rel="stylesheet" href="../assets/css/panel.css?v=<?= filemtime(__DIR__ . '/../assets/css/panel.css') ?>">
    <link rel="stylesheet" href="../assets/css/admin.css?v=<?= filemtime(__DIR__ . '/../assets/css/admin.css') ?>">
    <link rel="stylesheet" href="../assets/css/admin-formularios.css?v=<?= filemtime(__DIR__ . '/../assets/css/admin-formularios.css') ?>">
    <link rel="stylesheet" href="../assets/css/citas.css?v=<?= filemtime(__DIR__ . '/../assets/css/citas.css') ?>">
    <link rel="stylesheet" href="../assets/css/notificaciones-citas.css?v=<?= filemtime(__DIR__ . '/../assets/css/notificaciones-citas.css') ?>">
    <link rel="stylesheet" href="../assets/css/tema.css?v=<?= filemtime(__DIR__ . '/../assets/css/tema.css') ?>">
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
                    <h1><?= $esAdministrador ? 'Solicitudes de citas' : 'Mis solicitudes de citas' ?></h1>
                </div>
            </header>

            <?php if ($mensajeTexto !== ''): ?>
                <p class="mensaje-admin <?= htmlspecialchars($mensajeTipo, ENT_QUOTES, 'UTF-8') ?>" role="status">
                    <?= htmlspecialchars($mensajeTexto, ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php endif; ?>

            <?php if (!$esAdministrador): ?>
                <?php require __DIR__ . '/avisos-citas.php'; ?>

                <section class="formulario-admin" aria-labelledby="titulo-solicitud">
                    <h2 id="titulo-solicitud">Solicitar una cita</h2>
                    <p class="ayuda-formulario">Selecciona tu mascota, el servicio, la fecha y un turno disponible de lunes a viernes (08:00 a 16:00).</p>
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
                                <div class="campo-admin campo-completo">
                                    <label for="motivo-cita">Motivo de consulta</label>
                                    <textarea id="motivo-cita" name="motivo" maxlength="500" required><?= htmlspecialchars($valores['motivo'], ENT_QUOTES, 'UTF-8') ?></textarea>
                                </div>
                            </div>
                            <div class="acciones-formulario">
                                <button type="submit" class="boton-admin">Solicitar cita</button>
                                <a href="mis-mascotas.php?desde=citas#registrar-mascota" class="boton-cancelar">Registrar otra mascota</a>
                            </div>
                        </form>
                    <?php endif; ?>
                </section>
            <?php endif; ?>

            <p class="descripcion-listado">
                <?= $esAdministrador
                    ? 'Consulta las solicitudes recibidas y abre la ventana de gestión para confirmar, editar cualquier dato, cancelar o eliminar una cita.'
                    : 'Consulta el estado de tus solicitudes y abre los detalles para editar tu cita o eliminarla de la lista si ya no se efectuará.' ?>
            </p>

            <div class="barra-filtros">
                <div class="grupo-filtro">
                    <label for="buscar-cita">
                        <?= $esAdministrador
                            ? 'Buscar por mascota, cliente, servicio, fecha o estado'
                            : 'Buscar por mascota, servicio, fecha o estado' ?>
                    </label>
                    <input
                        type="search"
                        id="buscar-cita"
                        placeholder="Escribe una mascota, servicio, estado o palabra clave"
                        aria-controls="tabla-citas"
                    >
                </div>

                <p id="resultado-citas" role="status">
                    <?= htmlspecialchars($textoContadorInicial, ENT_QUOTES, 'UTF-8') ?>
                </p>
            </div>

            <section aria-labelledby="titulo-listado-citas">
                <h2 id="titulo-listado-citas" class="titulo-listado">
                    <?= $esAdministrador ? 'Bandeja de solicitudes de citas' : 'Listado de mis citas' ?>
                </h2>

                <div class="tabla-contenedor" role="region" aria-labelledby="titulo-listado-citas" tabindex="0">
                    <table id="tabla-citas" class="tabla-admin citas-tabla">
                        <caption class="solo-lectores">
                            Listado resumido de citas veterinarias
                        </caption>
                        <thead>
                            <tr>
                                <th scope="col">Mascota</th>
                                <?php if ($esAdministrador): ?>
                                    <th scope="col">Cliente</th>
                                <?php endif; ?>
                                <th scope="col">Servicio</th>
                                <th scope="col">Fecha / Horario</th>
                                <th scope="col">Estado</th>
                                <th scope="col">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($citas as $cita): ?>
                                <?php
                                $estadoActual = (string) $cita['estado'];
                                $claseEstado = claseEstadoCita($estadoActual);
                                $servicioTexto = trim((string) ($cita['servicio_nombre'] ?? '')) ?: 'Consulta general';

                                $horarioAprobadoTexto = 'Por asignar';
                                $fechaAprobadaIso = '';
                                $horaAprobadaIso = '';

                                if ($cita['fecha_hora'] !== null) {
                                    $dtAprobada = new DateTimeImmutable($cita['fecha_hora'], $zonaCitas);
                                    $horarioAprobadoTexto = $dtAprobada->format('d/m/Y') . ' · ' . $dtAprobada->format('H:i') . ' - ' . $dtAprobada->modify('+1 hour')->format('H:i');
                                    $fechaAprobadaIso = $dtAprobada->format('Y-m-d');
                                    $horaAprobadaIso = $dtAprobada->format('H:i');
                                }

                                $disponibilidadTexto = 'Sin preferencia registrada';
                                $fechaPreferidaIso = '';
                                $horaPreferidaIso = '';
                                if ($cita['fecha_preferida'] !== null && $cita['hora_desde'] !== null && $cita['hora_hasta'] !== null) {
                                    $dtPref = new DateTimeImmutable($cita['fecha_preferida'], $zonaCitas);
                                    $horaDesdeCorta = substr((string) $cita['hora_desde'], 0, 5);
                                    $horaHastaCorta = substr((string) $cita['hora_hasta'], 0, 5);
                                    $disponibilidadTexto = $dtPref->format('d/m/Y') . ' (' . $horaDesdeCorta . ' - ' . $horaHastaCorta . ')';
                                    $fechaPreferidaIso = $dtPref->format('Y-m-d');
                                    $horaPreferidaIso = $horaDesdeCorta;
                                }

                                if ($cita['fecha_hora'] !== null && !in_array($estadoActual, ['Rechazada', 'Cancelada'], true)) {
                                    $resumenHorarioTabla = $horarioAprobadoTexto;
                                } elseif ($fechaPreferidaIso !== '') {
                                    $resumenHorarioTabla = 'Pref: ' . $disponibilidadTexto;
                                } else {
                                    $resumenHorarioTabla = 'Sin horario asignado';
                                }
                                ?>
                                <tr data-cita>
                                    <th scope="row" class="cita-mascota">
                                        <?= htmlspecialchars($cita['mascota_nombre'], ENT_QUOTES, 'UTF-8') ?>
                                    </th>

                                    <?php if ($esAdministrador): ?>
                                        <td class="cita-cliente">
                                            <?= htmlspecialchars($cita['cliente_nombre'], ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                    <?php endif; ?>

                                    <td class="cita-servicio">
                                        <?= htmlspecialchars($servicioTexto, ENT_QUOTES, 'UTF-8') ?>
                                    </td>

                                    <td class="cita-horario">
                                        <?= htmlspecialchars($resumenHorarioTabla, ENT_QUOTES, 'UTF-8') ?>
                                    </td>

                                    <td class="cita-estado-celda">
                                        <span class="citas-estado <?= htmlspecialchars($claseEstado, ENT_QUOTES, 'UTF-8') ?>">
                                            <?= htmlspecialchars($estadoActual, ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>

                                    <td>
                                        <div class="acciones-tabla">
                                            <!-- Atributos data-* alimentan la ventana modal para lectura y edición completa -->
                                            <button
                                                type="button"
                                                class="accion-tabla accion-ver-cita"
                                                aria-label="<?= $esAdministrador ? 'Gestionar cita de ' : 'Ver o editar cita de ' ?><?= htmlspecialchars($cita['mascota_nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-id="<?= (int) $cita['id'] ?>"
                                                data-version="<?= (int) $cita['version'] ?>"
                                                data-mascota-id="<?= (int) $cita['mascota_id'] ?>"
                                                data-mascota="<?= htmlspecialchars($cita['mascota_nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-cliente="<?= htmlspecialchars($cita['cliente_nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-correo="<?= htmlspecialchars($cita['cliente_correo'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-servicio="<?= htmlspecialchars($servicioTexto, ENT_QUOTES, 'UTF-8') ?>"
                                                data-estado="<?= htmlspecialchars($estadoActual, ENT_QUOTES, 'UTF-8') ?>"
                                                data-estado-clase="<?= htmlspecialchars($claseEstado, ENT_QUOTES, 'UTF-8') ?>"
                                                data-horario-aprobado="<?= htmlspecialchars($horarioAprobadoTexto, ENT_QUOTES, 'UTF-8') ?>"
                                                data-fecha-aprobada="<?= htmlspecialchars($fechaAprobadaIso, ENT_QUOTES, 'UTF-8') ?>"
                                                data-hora-aprobada="<?= htmlspecialchars($horaAprobadaIso, ENT_QUOTES, 'UTF-8') ?>"
                                                data-disponibilidad="<?= htmlspecialchars($disponibilidadTexto, ENT_QUOTES, 'UTF-8') ?>"
                                                data-fecha-preferida="<?= htmlspecialchars($fechaPreferidaIso, ENT_QUOTES, 'UTF-8') ?>"
                                                data-hora-preferida="<?= htmlspecialchars($horaPreferidaIso, ENT_QUOTES, 'UTF-8') ?>"
                                                data-motivo="<?= htmlspecialchars((string) $cita['motivo'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-motivo-cambio="<?= htmlspecialchars((string) ($cita['motivo_cambio'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                                data-estado-cambio="<?= htmlspecialchars((string) ($cita['estado_cambio'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                            >
                                                <?= $esAdministrador ? '⚙ Gestionar' : '👁 Ver / Editar' ?>
                                            </button>

                                            <!-- Eliminación directa con confirmación en dos pasos en línea -->
                                            <form action="citas.php" method="post" class="form-eliminar-cita-inline">
                                                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="cita_id" value="<?= (int) $cita['id'] ?>">
                                                <input type="hidden" name="accion" value="eliminar">
                                                <button
                                                    type="submit"
                                                    class="accion-tabla accion-eliminar"
                                                    data-confirmar="Haz clic de nuevo en Confirmar para eliminar esta cita de la lista"
                                                    aria-label="Eliminar cita de <?= htmlspecialchars($cita['mascota_nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                                >
                                                    Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <tr id="citas-vacio" <?= $totalCitas > 0 ? 'hidden' : '' ?>>
                                <td colspan="<?= $esAdministrador ? 6 : 5 ?>">
                                    <div class="estado-vacio-ilustrado">
                                        <span class="estado-vacio-icono" aria-hidden="true">📅</span>
                                        <h3><?= $mensajeTipo === 'error' ? 'No se pudieron cargar las citas' : 'Sin citas para mostrar' ?></h3>
                                        <p>
                                            <?= $mensajeTipo === 'error'
                                                ? htmlspecialchars($mensajeTexto, ENT_QUOTES, 'UTF-8')
                                                : 'Todavía no hay solicitudes registradas o ninguna coincide con tu búsqueda.' ?>
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

    <!-- Ventana modal dinámica para lectura detallada, edición libre y eliminación de la cita -->
    <div
        id="modal-cita"
        class="modal-overlay"
        role="dialog"
        aria-modal="true"
        aria-labelledby="modal-cita-titulo"
        hidden
    >
        <div class="modal-caja modal-caja-cita">
            <header class="modal-cabecera">
                <div>
                    <p class="etiqueta-seccion"><?= $esAdministrador ? 'GESTIÓN ADMINISTRATIVA DE CITA' : 'DETALLE Y EDICIÓN DE MI CITA' ?></p>
                    <h2 id="modal-cita-titulo"><?= $esAdministrador ? 'Gestionar o editar solicitud de cita' : 'Detalles y edición de la cita' ?></h2>
                </div>

                <button
                    type="button"
                    id="modal-cita-cerrar"
                    class="modal-boton-cerrar"
                    aria-label="Cerrar ventana"
                >
                    ✕
                </button>
            </header>

            <div class="modal-cuerpo">
                <dl class="modal-metadatos">
                    <div class="modal-dato">
                        <dt>Mascota</dt>
                        <dd id="modal-cita-mascota"></dd>
                    </div>

                    <div class="modal-dato">
                        <dt>Estado actual</dt>
                        <dd><span id="modal-cita-estado" class="citas-estado"></span></dd>
                    </div>

                    <?php if ($esAdministrador): ?>
                        <div class="modal-dato">
                            <dt>Cliente</dt>
                            <dd id="modal-cita-cliente"></dd>
                        </div>

                        <div class="modal-dato">
                            <dt>Correo del cliente</dt>
                            <dd id="modal-cita-correo"></dd>
                        </div>
                    <?php endif; ?>

                    <div class="modal-dato modal-dato-completo">
                        <dt>Servicio veterinario</dt>
                        <dd id="modal-cita-servicio"></dd>
                    </div>

                    <div class="modal-dato">
                        <dt>Horario confirmado (24h)</dt>
                        <dd id="modal-cita-horario-aprobado"></dd>
                    </div>

                    <div class="modal-dato">
                        <dt>Disponibilidad preferida del cliente</dt>
                        <dd id="modal-cita-disponibilidad"></dd>
                    </div>
                </dl>

                <div class="modal-bloque-mensaje">
                    <span class="modal-etiqueta-mensaje">Motivo de la consulta</span>
                    <p id="modal-cita-motivo" class="modal-mensaje-texto"></p>
                </div>

                <div id="modal-bloque-cambio" class="modal-bloque-mensaje modal-bloque-cambio" hidden>
                    <span class="modal-etiqueta-mensaje">Nota / Solicitud de cambio (<span id="modal-cita-estado-cambio"></span>)</span>
                    <p id="modal-cita-motivo-cambio" class="modal-mensaje-texto"></p>
                </div>

                <?php if ($esAdministrador): ?>
                    <form
                        id="form-modal-admin"
                        action="citas.php"
                        method="post"
                        class="modal-formulario-cita"
                        data-agenda-citas
                        data-horarios-url="../api/horarios-citas.php"
                    >
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="cita_id" id="modal-admin-cita-id" value="">
                        <input type="hidden" name="version" id="modal-admin-version" value="">

                        <div id="modal-admin-programacion" class="modal-programacion">
                            <h3 id="modal-admin-subtitulo">Editar datos y asignar horario</h3>
                            <div class="rejilla-formulario">
                                <div class="campo-admin campo-completo">
                                    <label for="modal-admin-servicio">Servicio veterinario</label>
                                    <select id="modal-admin-servicio" name="servicio_nombre" required>
                                        <?php foreach ($servicios as $servicio): ?>
                                            <option value="<?= htmlspecialchars($servicio['nombre'], ENT_QUOTES, 'UTF-8') ?>">
                                                <?= htmlspecialchars($servicio['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="campo-admin">
                                    <label for="modal-admin-fecha">Fecha de atención</label>
                                    <input
                                        type="date"
                                        id="modal-admin-fecha"
                                        name="fecha"
                                        data-fecha-cita
                                        min="<?= $ahora->format('Y-m-d') ?>"
                                        required
                                    >
                                </div>

                                <div class="campo-admin">
                                    <label for="modal-admin-hora">Horario disponible (24h)</label>
                                    <select
                                        id="modal-admin-hora"
                                        name="hora"
                                        data-hora-cita
                                        required
                                        disabled
                                    >
                                        <option value="">Selecciona primero una fecha</option>
                                    </select>
                                    <small data-estado-horarios role="status">Turnos de lunes a viernes, de 08:00 a 16:00.</small>
                                </div>

                                <div class="campo-admin campo-completo">
                                    <label for="modal-admin-motivo">Motivo de la consulta</label>
                                    <textarea
                                        id="modal-admin-motivo"
                                        name="motivo"
                                        maxlength="500"
                                        required
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="modal-acciones-admin">
                            <button
                                type="submit"
                                id="btn-modal-confirmar"
                                name="accion"
                                value="confirmar"
                                class="boton-admin"
                            >
                                Asignar y confirmar
                            </button>

                            <button
                                type="submit"
                                id="btn-modal-editar"
                                name="accion"
                                value="editar"
                                class="boton-admin"
                            >
                                Guardar cambios (Editar)
                            </button>

                            <button
                                type="submit"
                                id="btn-modal-mantener"
                                name="accion"
                                value="rechazar_cambio"
                                class="accion-tabla accion-editar"
                                formnovalidate
                                hidden
                            >
                                Mantener horario actual
                            </button>

                            <button
                                type="submit"
                                id="btn-modal-rechazar"
                                name="accion"
                                value="rechazar"
                                class="accion-tabla accion-eliminar"
                                formnovalidate
                            >
                                Rechazar
                            </button>

                            <button
                                type="submit"
                                id="btn-modal-cancelar"
                                name="accion"
                                value="cancelar"
                                class="accion-tabla accion-eliminar"
                                formnovalidate
                            >
                                Cancelar cita
                            </button>

                            <button
                                type="submit"
                                id="btn-modal-eliminar-admin"
                                name="accion"
                                value="eliminar"
                                class="accion-tabla accion-eliminar"
                                data-confirmar="Haz clic de nuevo en Confirmar para eliminar esta cita de la lista"
                                formnovalidate
                            >
                                🗑 Eliminar de la lista
                            </button>
                        </div>
                    </form>
                <?php else: ?>
                    <p id="modal-cliente-aviso-estado" class="modal-aviso-estado"></p>

                    <form
                        id="form-modal-cliente"
                        action="citas.php"
                        method="post"
                        class="modal-formulario-cita"
                        data-agenda-citas
                        data-horarios-url="../api/horarios-citas.php"
                    >
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="cita_id" id="modal-cliente-cita-id" value="">
                        <input type="hidden" name="version" id="modal-cliente-version" value="">

                        <div class="modal-programacion">
                            <h3>Editar mi cita o cambiar horario</h3>
                            <div class="rejilla-formulario">
                                <div class="campo-admin">
                                    <label for="modal-cliente-mascota">Mascota</label>
                                    <select id="modal-cliente-mascota" name="mascota_id" required>
                                        <?php foreach ($mascotas as $mascota): ?>
                                            <option value="<?= (int) $mascota['id'] ?>">
                                                <?= htmlspecialchars($mascota['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="campo-admin">
                                    <label for="modal-cliente-servicio">Servicio veterinario</label>
                                    <select id="modal-cliente-servicio" name="servicio_nombre" required>
                                        <?php foreach ($servicios as $servicio): ?>
                                            <option value="<?= htmlspecialchars($servicio['nombre'], ENT_QUOTES, 'UTF-8') ?>">
                                                <?= htmlspecialchars($servicio['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="campo-admin">
                                    <label for="modal-cliente-fecha">Fecha preferida</label>
                                    <input
                                        type="date"
                                        id="modal-cliente-fecha"
                                        name="fecha_preferida"
                                        data-fecha-cita
                                        min="<?= $ahora->format('Y-m-d') ?>"
                                        required
                                    >
                                </div>

                                <div class="campo-admin">
                                    <label for="modal-cliente-hora">Horario disponible (24h)</label>
                                    <select
                                        id="modal-cliente-hora"
                                        name="hora_preferida"
                                        data-hora-cita
                                        required
                                        disabled
                                    >
                                        <option value="">Selecciona primero una fecha</option>
                                    </select>
                                    <small data-estado-horarios role="status">Turnos de una hora, de lunes a viernes (08:00 a 16:00).</small>
                                </div>

                                <div class="campo-admin campo-completo">
                                    <label for="modal-cliente-motivo-consulta">Motivo de la consulta</label>
                                    <textarea
                                        id="modal-cliente-motivo-consulta"
                                        name="motivo"
                                        maxlength="500"
                                        required
                                    ></textarea>
                                </div>

                                <div class="campo-admin campo-completo">
                                    <label for="modal-cliente-motivo">Nota o motivo del cambio (opcional)</label>
                                    <input
                                        type="text"
                                        id="modal-cliente-motivo"
                                        name="motivo_cambio"
                                        maxlength="500"
                                        placeholder="Ej. Prefiero asistir en la tarde o actualicé el servicio solicitado"
                                    >
                                </div>
                            </div>

                            <div class="modal-acciones-admin">
                                <button
                                    type="submit"
                                    name="accion"
                                    value="editar_cliente"
                                    class="boton-admin"
                                >
                                    Guardar cambios en mi cita
                                </button>

                                <button
                                    type="submit"
                                    name="accion"
                                    value="eliminar"
                                    class="accion-tabla accion-eliminar"
                                    data-confirmar="Haz clic de nuevo en Confirmar para eliminar esta cita de tu lista"
                                    formnovalidate
                                >
                                    🗑 Eliminar cita
                                </button>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
            </div>

            <footer class="modal-pie">
                <button type="button" id="modal-cita-cerrar-pie" class="modal-boton-secundario">
                    Cerrar
                </button>
            </footer>
        </div>
    </div>
</body>
</html>
