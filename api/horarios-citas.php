<?php
require_once __DIR__ . '/../includes/sesion.php';
requerirRol(null, '../login.php');
require_once __DIR__ . '/../includes/horarios-citas.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
try {
    if (!$pdo) {
        http_response_code(503);
        echo json_encode(['mensaje' => 'No se pudo consultar la agenda. Inténtalo de nuevo.']);
        exit;
    }
    $fecha = $_GET['fecha'] ?? '';
    if (!is_string($fecha)) {
        throw new InvalidArgumentException('Selecciona una fecha válida.');
    }
    // Permite excluir la propia cita que el administrador o el cliente está editando.
    $excluir = filter_var($_GET['excluir_cita'] ?? 0, FILTER_VALIDATE_INT);
    if ($excluir === false || $excluir < 0) {
        throw new InvalidArgumentException('La cita seleccionada no es válida.');
    }
    if ($excluir > 0 && ($_SESSION['usuario_rol'] ?? '') !== 'admin') {
        $stmtPropia = $pdo->prepare('SELECT id FROM citas WHERE id = :id AND usuario_id = :usuario LIMIT 1');
        $stmtPropia->execute([':id' => $excluir, ':usuario' => (int) $_SESSION['usuario_id']]);
        if (!$stmtPropia->fetchColumn()) {
            $excluir = 0;
        }
    }
    $zona = new DateTimeZone('America/Santo_Domingo');
    echo json_encode(['turnos' => horariosDisponiblesCitas($pdo, $fecha, $zona, $excluir)], JSON_UNESCAPED_UNICODE);
} catch (InvalidArgumentException $e) {
    http_response_code(422);
    echo json_encode(['mensaje' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    error_log('Error al consultar horarios de citas: ' . $e->getMessage());
    http_response_code(503);
    echo json_encode(['mensaje' => 'No se pudo consultar la agenda. Inténtalo de nuevo.']);
}
