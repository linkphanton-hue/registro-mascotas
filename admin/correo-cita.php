<?php
require_once __DIR__ . '/../includes/sesion.php';
requerirRol('admin', '../login.php');
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id || $id < 1 || !$pdo) {
    http_response_code(404);
    exit('No se encontró el correo simulado.');
}
$stmt = $pdo->prepare("SELECT titulo, mensaje, destinatario, remitente FROM notificaciones_citas WHERE id = :id AND estado_correo = 'Simulado'");
$stmt->execute([':id' => $id]);
$correoSimulado = $stmt->fetch();
if (!$correoSimulado) {
    http_response_code(404);
    exit('No se encontró el correo simulado.');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Correo simulado | Cuidado de tus Mascotas</title>
    <!-- Carga el script para inicializar el tema -->
    <script src="../assets/js/tema-inicial.js?v=<?= filemtime(__DIR__ . '/../assets/js/tema-inicial.js') ?>"></script>
    <link rel="stylesheet" href="../assets/css/panel.css?v=<?= filemtime(__DIR__ . '/../assets/css/panel.css') ?>">
    <link rel="stylesheet" href="../assets/css/tema.css?v=<?= filemtime(__DIR__ . '/../assets/css/tema.css') ?>">
    <link rel="stylesheet" href="../assets/css/notificaciones-citas.css?v=<?= filemtime(__DIR__ . '/../assets/css/notificaciones-citas.css') ?>">
</head>
<body class="pagina-panel">
    <main class="contenido-panel">
        <a href="citas.php">← Volver a citas</a>
        <h1>Correo simulado</h1>
        <article class="aviso-cita">
            <p>Vista previa del correo generado para la demostración del proyecto.</p>
            <dl>
                <dt>De</dt>
                <dd><?= htmlspecialchars($correoSimulado['remitente'], ENT_QUOTES, 'UTF-8') ?></dd>
                <dt>Para</dt>
                <dd><?= htmlspecialchars($correoSimulado['destinatario'], ENT_QUOTES, 'UTF-8') ?></dd>
                <dt>Asunto</dt>
                <dd><?= htmlspecialchars($correoSimulado['titulo'], ENT_QUOTES, 'UTF-8') ?></dd>
            </dl>
            <p class="aviso-cita-mensaje"><?= htmlspecialchars($correoSimulado['mensaje'], ENT_QUOTES, 'UTF-8') ?></p>
        </article>
    </main>
</body>
</html>
