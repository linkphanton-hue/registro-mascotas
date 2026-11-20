<?php
use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/../vendor/autoload.php';

function configuracionCorreoCitas(): array
{
    $ruta = __DIR__ . '/config-correo.php';
    return file_exists($ruta) ? require $ruta : ['habilitado' => false];
}

// Registra el aviso dentro de la transacción que confirma la cita.
function registrarConfirmacionCita(PDO $pdo, int $citaId, string $remitente, bool $reprogramada): int
{
    $stmt = $pdo->prepare(
        "SELECT citas.usuario_id, citas.version, citas.fecha_hora, usuarios.nombre, usuarios.correo,
                mascotas.nombre AS mascota
         FROM citas INNER JOIN usuarios ON usuarios.id = citas.usuario_id
         INNER JOIN mascotas ON mascotas.id = citas.mascota_id
         WHERE citas.id = :id AND citas.estado = 'Confirmada'"
    );
    $stmt->execute([':id' => $citaId]);
    $cita = $stmt->fetch();
    if (!$cita) {
        throw new InvalidArgumentException('No se encontró la cita aprobada para notificar al cliente.');
    }
    $fecha = new DateTimeImmutable($cita['fecha_hora'], new DateTimeZone('America/Santo_Domingo'));
    $titulo = $reprogramada ? 'Cambio de horario aprobado' : 'Tu cita ha sido confirmada';
    $mensaje = 'Hola ' . $cita['nombre'] . ",\n\n"
        . ($reprogramada ? 'El administrador aprobó el nuevo horario de tu cita.' : 'El administrador aprobó tu cita.')
        . "\nMascota: " . $cita['mascota']
        . "\nFecha: " . $fecha->format('d/m/Y')
        . "\nHorario: " . $fecha->format('g:i a') . ' a ' . $fecha->modify('+1 hour')->format('g:i a')
        . "\nZona horaria: República Dominicana.\n\nPuedes consultar los detalles en Mis citas de tu cuenta.";
    $stmt = $pdo->prepare(
        'INSERT INTO notificaciones_citas
         (cita_id, usuario_id, version_cita, titulo, mensaje, destinatario, remitente)
         VALUES (:cita, :usuario, :version, :titulo, :mensaje, :destinatario, :remitente)'
    );
    $stmt->execute([
        ':cita' => $citaId, ':usuario' => $cita['usuario_id'], ':version' => $cita['version'],
        ':titulo' => $titulo, ':mensaje' => $mensaje, ':destinatario' => $cita['correo'], ':remitente' => $remitente,
    ]);
    return (int) $pdo->lastInsertId();
}

function enviarConfirmacionCita(PDO $pdo, int $notificacionId, ?array $config = null): bool
{
    $config = $config ?? configuracionCorreoCitas();
    $simulado = ($config['modo'] ?? 'smtp') === 'demostracion';
    if (empty($config['habilitado']) || (!$simulado && empty($config['host']))) {
        return false;
    }
    $stmt = $pdo->prepare(
        "UPDATE notificaciones_citas SET estado_correo = 'Enviando'
         WHERE id = :id AND estado_correo IN ('Pendiente', 'Error')"
    );
    $stmt->execute([':id' => $notificacionId]);
    if ($stmt->rowCount() !== 1) {
        return false;
    }
    $enviado = false;
    try {
        $stmt = $pdo->prepare('SELECT * FROM notificaciones_citas WHERE id = :id');
        $stmt->execute([':id' => $notificacionId]);
        $aviso = $stmt->fetch();
        $correo = new PHPMailer(true);
        $correo->isSMTP();
        $correo->Host = $config['host'];
        $correo->Port = (int) $config['puerto'];
        $correo->SMTPAuth = (bool) $config['autenticar'];
        $correo->Username = $config['usuario'];
        $correo->Password = $config['clave'];
        $correo->SMTPSecure = $config['seguridad'];
        $correo->Timeout = 10;
        $correo->CharSet = 'UTF-8';
        $correo->setFrom($aviso['remitente'], $config['nombre_remitente']);
        $correo->addAddress($aviso['destinatario']);
        $correo->Subject = $aviso['titulo'];
        $correo->Body = $aviso['mensaje'];
        $correo->isHTML(false);
        if ($simulado) {
            $correo->preSend();
        } else {
            $correo->send();
        }
        $enviado = true;
    } catch (Throwable $e) {
        error_log('Error al enviar confirmación de cita ' . $notificacionId . ': ' . $e->getMessage());
    }
    // Si falla el registro posterior al envío se conserva Enviando para evitar un reenvío automático.
    try {
        $stmt = $pdo->prepare(
            'UPDATE notificaciones_citas SET estado_correo = :estado, enviado_en = :fecha WHERE id = :id'
        );
        $stmt->execute([
            ':estado' => $enviado ? ($simulado ? 'Simulado' : 'Enviado') : 'Error',
            ':fecha' => $enviado ? (new DateTimeImmutable('now', new DateTimeZone('America/Santo_Domingo')))->format('Y-m-d H:i:s') : null,
            ':id' => $notificacionId,
        ]);
    } catch (PDOException $e) {
        error_log('No se pudo registrar el resultado del correo de cita ' . $notificacionId . ': ' . $e->getMessage());
    }
    return $enviado;
}
