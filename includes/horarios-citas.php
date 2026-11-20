<?php
// La agenda recibe citas de una hora, de lunes a viernes, con último inicio a las 16:00.
function horasAtencionCitas(): array
{
    $horas = [];
    for ($hora = 8; $hora <= 16; $hora++) {
        $horas[] = sprintf('%02d:00', $hora);
    }
    return $horas;
}

function fechaAtencionCitas(string $texto, DateTimeZone $zona): DateTimeImmutable
{
    if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D', $texto)) {
        throw new InvalidArgumentException('Selecciona una fecha válida.');
    }
    $fecha = DateTimeImmutable::createFromFormat('!Y-m-d', $texto, $zona);
    if (!$fecha || $fecha->format('Y-m-d') !== $texto) {
        throw new InvalidArgumentException('Selecciona una fecha válida.');
    }
    if ((int) $fecha->format('N') > 5) {
        throw new InvalidArgumentException('Las citas se atienden de lunes a viernes.');
    }
    if ($texto < (new DateTimeImmutable('now', $zona))->format('Y-m-d')) {
        throw new InvalidArgumentException('Selecciona una fecha de hoy en adelante.');
    }
    return $fecha;
}

function validarTurnoCita(string $fecha, string $hora, DateTimeZone $zona): DateTimeImmutable
{
    $dia = fechaAtencionCitas($fecha, $zona);
    if (!in_array($hora, horasAtencionCitas(), true)) {
        throw new InvalidArgumentException('Selecciona un turno de una hora entre las 08:00 y las 16:00.');
    }
    [$horas, $minutos] = explode(':', $hora);
    $inicio = $dia->setTime((int) $horas, (int) $minutos);
    if ($inicio <= new DateTimeImmutable('now', $zona)) {
        throw new InvalidArgumentException('El horario seleccionado ya pasó. Elige otro turno.');
    }
    return $inicio;
}

function horariosDisponiblesCitas(PDO $pdo, string $fecha, DateTimeZone $zona, int $excluirCita = 0): array
{
    $dia = fechaAtencionCitas($fecha, $zona);
    $stmt = $pdo->prepare(
        "SELECT fecha_hora FROM citas
         WHERE estado IN ('Confirmada', 'Cambio solicitado')
           AND fecha_hora >= :inicio AND fecha_hora < :fin AND id != :excluir"
    );
    $stmt->execute([
        ':inicio' => $dia->format('Y-m-d H:i:s'),
        ':fin' => $dia->modify('+1 day')->format('Y-m-d H:i:s'),
        ':excluir' => $excluirCita,
    ]);
    $ocupados = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $ahora = new DateTimeImmutable('now', $zona);
    $turnos = [];
    foreach (horasAtencionCitas() as $hora) {
        $inicio = $dia->setTime((int) substr($hora, 0, 2), 0);
        $fin = $inicio->modify('+1 hour');
        $disponible = $inicio > $ahora;
        foreach ($ocupados as $textoOcupado) {
            $ocupado = new DateTimeImmutable($textoOcupado, $zona);
            if ($ocupado < $fin && $ocupado->modify('+1 hour') > $inicio) {
                $disponible = false;
                break;
            }
        }
        $turnos[] = [
            'hora' => $hora,
            'etiqueta' => $inicio->format('g:i a') . ' - ' . $fin->format('g:i a'),
            'disponible' => $disponible,
        ];
    }
    return $turnos;
}

function comprobarTurnoDisponibleCita(PDO $pdo, DateTimeImmutable $inicio, DateTimeZone $zona, int $excluirCita = 0): void
{
    foreach (horariosDisponiblesCitas($pdo, $inicio->format('Y-m-d'), $zona, $excluirCita) as $turno) {
        if ($turno['hora'] === $inicio->format('H:i') && $turno['disponible']) {
            return;
        }
    }
    throw new InvalidArgumentException('Ese horario ya está ocupado. Selecciona otro turno disponible.');
}
