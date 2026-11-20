<?php
// Este bloque se incluye después de comprobar el rol del cliente en la página que lo muestra.
if (!isset($pdo) || !$pdo || ($_SESSION['usuario_rol'] ?? '') !== 'cliente') {
    return;
}
if (!isset($_SESSION['csrf_citas'])) {
    $_SESSION['csrf_citas'] = bin2hex(random_bytes(32));
}
$avisosCitas = [];
try {
    $stmtAvisos = $pdo->prepare(
        'SELECT id, titulo, mensaje FROM notificaciones_citas
         WHERE usuario_id = :usuario AND leida = 0 ORDER BY id DESC LIMIT 5'
    );
    $stmtAvisos->execute([':usuario' => (int) $_SESSION['usuario_id']]);
    $avisosCitas = $stmtAvisos->fetchAll();
} catch (PDOException $e) {
    error_log('Error al leer avisos de citas: ' . $e->getMessage());
}
$volverAvisos = basename($_SERVER['SCRIPT_NAME'] ?? '') === 'citas.php' ? 'citas.php' : 'panel.php';
?>
<?php if ($avisosCitas): ?>
    <section class="avisos-citas" aria-label="Confirmaciones de citas" aria-live="polite">
        <?php foreach ($avisosCitas as $avisoCita): ?>
            <article class="aviso-cita">
                <h2><?= htmlspecialchars($avisoCita['titulo'], ENT_QUOTES, 'UTF-8') ?></h2>
                <p class="aviso-cita-mensaje"><?= htmlspecialchars($avisoCita['mensaje'], ENT_QUOTES, 'UTF-8') ?></p>
                <form action="notificaciones.php" method="post" class="acciones-tabla">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf_citas'], ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="notificacion_id" value="<?= (int) $avisoCita['id'] ?>">
                    <input type="hidden" name="volver" value="<?= $volverAvisos ?>">
                    <a href="citas.php" class="accion-tabla accion-editar">Ver mis citas</a>
                    <button type="submit" class="accion-tabla">Entendido</button>
                </form>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>
