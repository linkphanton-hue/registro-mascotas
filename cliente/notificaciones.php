<?php
require_once __DIR__ . '/../includes/sesion.php';
requerirRol('cliente', '../login.php');

$token = $_POST['csrf'] ?? '';
$id = filter_var($_POST['notificacion_id'] ?? null, FILTER_VALIDATE_INT);
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !is_string($token)
    || !isset($_SESSION['csrf_citas']) || !hash_equals($_SESSION['csrf_citas'], $token)
    || !$id || $id < 1 || !$pdo) {
    http_response_code(403);
    exit('Solicitud no válida.');
}
$stmt = $pdo->prepare('UPDATE notificaciones_citas SET leida = 1 WHERE id = :id AND usuario_id = :usuario');
$stmt->execute([':id' => $id, ':usuario' => (int) $_SESSION['usuario_id']]);
$volver = in_array($_POST['volver'] ?? '', ['panel.php', 'citas.php'], true) ? $_POST['volver'] : 'panel.php';
header('Location: ' . $volver);
exit;
