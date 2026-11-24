<?php
/**
 * Gestión de notificaciones internas en plataforma para cambios de estado en citas.
 *
 * Registra avisos persistentes en `notificaciones_citas` para informar al cliente
 * dentro del sistema web sin depender de servidores de correo externo (SMTP).
 *
 * @package RegistroMascotas\Includes
 */

/**
 * Registra una notificación interna para el cliente cuando el administrador
 * confirma, edita, reprograma, rechaza o cancela una solicitud de cita.
 *
 * @param PDO    $pdo        Conexión activa a la base de datos.
 * @param int    $citaId     Identificador de la cita gestionada.
 * @param string $tipoEvento Evento aplicado: 'confirmada', 'editada', 'cambio_aprobado', 'cambio_rechazado', 'rechazada' o 'cancelada'.
 * @param string $remitente  Identificador o correo del administrador que procesó la acción.
 * @return int ID de la notificación insertada en MySQL.
 */
function registrarNotificacionCita(PDO $pdo, int $citaId, string $tipoEvento, string $remitente = 'admin@veterinaria.com'): int
{
    $stmt = $pdo->prepare(
        "SELECT citas.usuario_id, citas.version, citas.fecha_hora, citas.servicio_nombre,
                usuarios.nombre, usuarios.correo, mascotas.nombre AS mascota
         FROM citas
         INNER JOIN usuarios ON usuarios.id = citas.usuario_id
         INNER JOIN mascotas ON mascotas.id = citas.mascota_id
         WHERE citas.id = :id LIMIT 1"
    );
    $stmt->execute([':id' => $citaId]);
    $cita = $stmt->fetch();

    if (!$cita) {
        throw new InvalidArgumentException('No se encontró la cita para notificar al cliente.');
    }

    $zona = new DateTimeZone('America/Santo_Domingo');
    $horarioTexto = 'Por asignar';
    if (!empty($cita['fecha_hora'])) {
        $fecha = new DateTimeImmutable($cita['fecha_hora'], $zona);
        $horarioTexto = $fecha->format('d/m/Y') . ' de ' . $fecha->format('H:i') . ' a ' . $fecha->modify('+1 hour')->format('H:i');
    }

    $servicio = trim((string) ($cita['servicio_nombre'] ?? '')) ?: 'Consulta veterinaria';

    switch ($tipoEvento) {
        case 'editada':
            $titulo = 'Tu cita fue actualizada — Confirmada (Editada)';
            $resumen = 'El administrador modificó el horario de tu cita para ' . $cita['mascota'] . ' (' . $servicio . '). Nuevo horario: ' . $horarioTexto . '.';
            break;
        case 'cambio_aprobado':
            $titulo = 'Cambio de horario aprobado — Confirmada (Editada)';
            $resumen = 'El administrador aprobó el nuevo horario para ' . $cita['mascota'] . ' (' . $servicio . '): ' . $horarioTexto . '.';
            break;
        case 'cambio_rechazado':
            $titulo = 'Se mantiene el horario actual de tu cita';
            $resumen = 'No fue posible aplicar el cambio solicitado para ' . $cita['mascota'] . '. Se conserva el horario confirmado: ' . $horarioTexto . '.';
            break;
        case 'rechazada':
            $titulo = 'Solicitud de cita rechazada';
            $resumen = 'Tu solicitud de cita para ' . $cita['mascota'] . ' (' . $servicio . ') fue marcada como Rechazada.';
            break;
        case 'cancelada':
            $titulo = 'Cita cancelada por la administración';
            $resumen = 'La cita programada para ' . $cita['mascota'] . ' (' . $servicio . ') fue marcada como Cancelada.';
            break;
        case 'confirmada':
        default:
            $titulo = 'Tu cita ha sido confirmada';
            $resumen = 'El administrador confirmó la cita de ' . $cita['mascota'] . ' (' . $servicio . ') para el ' . $horarioTexto . '.';
            break;
    }

    $stmtInsertar = $pdo->prepare(
        "INSERT INTO notificaciones_citas
         (cita_id, usuario_id, version_cita, titulo, mensaje, destinatario, remitente, leida, estado_correo)
         VALUES (:cita, :usuario, :version, :titulo, :mensaje, :destinatario, :remitente, 0, 'Simulado')
         ON DUPLICATE KEY UPDATE
            titulo = VALUES(titulo),
            mensaje = VALUES(mensaje),
            leida = 0"
    );
    $stmtInsertar->execute([
        ':cita'         => $citaId,
        ':usuario'      => (int) $cita['usuario_id'],
        ':version'      => (int) $cita['version'],
        ':titulo'       => $titulo,
        ':mensaje'      => $resumen,
        ':destinatario' => (string) $cita['correo'],
        ':remitente'    => $remitente !== '' ? $remitente : 'admin@veterinaria.com',
    ]);

    return (int) $pdo->lastInsertId();
}
