<?php
/**
 * Componente de alertas discretas y notificaciones Toast para novedades de citas del cliente.
 *
 * Muestra las actualizaciones recientes de estado directamente en la interfaz sin botones
 * redundantes de redirección y dispara un Toast flotante al cargar la vista.
 *
 * @package RegistroMascotas\Includes
 */

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
$cantidadAvisos = count($avisosCitas);
$textoToastAviso = '';
if ($cantidadAvisos === 1) {
    $textoToastAviso = 'Novedad en tus citas: ' . $avisosCitas[0]['titulo'];
} elseif ($cantidadAvisos > 1) {
    $textoToastAviso = 'Tienes ' . $cantidadAvisos . ' novedades recientes en el estado de tus citas.';
}
?>
<?php if ($avisosCitas): ?>
    <section
        class="avisos-citas"
        aria-label="Actualizaciones en el estado de tus citas"
        aria-live="polite"
        data-toast-aviso="<?= htmlspecialchars($textoToastAviso, ENT_QUOTES, 'UTF-8') ?>"
    >
        <?php foreach ($avisosCitas as $avisoCita): ?>
            <article class="aviso-cita">
                <div class="aviso-cita-cuerpo">
                    <span class="aviso-cita-etiqueta">NOVEDAD EN CITA</span>
                    <h2><?= htmlspecialchars($avisoCita['titulo'], ENT_QUOTES, 'UTF-8') ?></h2>
                    <p class="aviso-cita-mensaje"><?= htmlspecialchars($avisoCita['mensaje'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>

                <form action="notificaciones.php" method="post" class="acciones-tabla">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf_citas'], ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="notificacion_id" value="<?= (int) $avisoCita['id'] ?>">
                    <input type="hidden" name="volver" value="<?= htmlspecialchars($volverAvisos, ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="accion-tabla accion-editar">Entendido</button>
                </form>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>
