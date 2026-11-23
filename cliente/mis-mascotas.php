<?php
/**
 * Catálogo detallado de mascotas del cliente autenticado.
 *
 * Lista todas las mascotas activas pertenecientes al titular de la sesión
 * e incorpora atributos data-especie en cada tarjeta para el filtrado con JS.
 *
 * @package RegistroMascotas\Cliente
 */

/* Exige sesión activa con rol 'cliente' antes de ejecutar la consulta */
require_once __DIR__ . '/../includes/sesion.php';
requerirRol('cliente', '../login.php');

require_once __DIR__ . '/../includes/conexion.php';
require_once __DIR__ . '/../includes/formato.php';

$clienteId     = (int) $_SESSION['usuario_id'];
$nombreCliente = trim((string) $_SESSION['usuario_nombre']);
$inicialAvatar = mb_strtoupper(mb_substr($nombreCliente, 0, 1));
$mascotas      = [];

$etiquetasEspecie = [
    'perro'  => 'Perro',
    'gato'   => 'Gato',
    'ave'    => 'Ave',
    'conejo' => 'Conejo',
    'otro'   => 'Otra especie',
];

$desdeCitas = ($_GET['desde'] ?? '') === 'citas';
$servicioCita = (int) filter_var($_GET['servicio'] ?? null, FILTER_VALIDATE_INT);
$rutaRegistro = 'mis-mascotas.php' . ($desdeCitas ? '?desde=citas&servicio=' . $servicioCita : '');
$csrfMascota = $_SESSION['csrf_registro_mascota'] ??= bin2hex(random_bytes(32));
$avisoMascota = $_SESSION['aviso_mascota'] ?? '';
unset($_SESSION['aviso_mascota']);
$errorMascota = '';
$datosMascota = ['nombre' => '', 'especie' => '', 'raza' => '', 'edad_anios' => '0', 'edad_meses' => '0'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($datosMascota) as $campo) {
        $valor = $_POST[$campo] ?? '';
        $datosMascota[$campo] = is_string($valor) ? trim($valor) : '';
    }

    $token = $_POST['csrf'] ?? '';
    $anios = filter_var($datosMascota['edad_anios'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 100]]);
    $meses = filter_var($datosMascota['edad_meses'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 11]]);

    if (!is_string($token) || !hash_equals($csrfMascota, $token)) {
        $errorMascota = 'El formulario venció. Vuelve a intentarlo.';
    } elseif ($datosMascota['nombre'] === '' || mb_strlen($datosMascota['nombre']) > 100) {
        $errorMascota = 'Escribe un nombre de hasta 100 caracteres.';
    } elseif (!isset($etiquetasEspecie[$datosMascota['especie']])) {
        $errorMascota = 'Selecciona una especie válida.';
    } elseif (mb_strlen($datosMascota['raza']) > 100) {
        $errorMascota = 'La raza admite hasta 100 caracteres.';
    } elseif ($anios === false || $meses === false) {
        $errorMascota = 'Indica una edad entre 0 y 100 años y entre 0 y 11 meses.';
    } elseif (!$pdo) {
        $errorMascota = 'No se pudo conectar con la base de datos.';
    } else {
        try {
            $registro = $pdo->prepare(
                "INSERT INTO mascotas (nombre, especie, raza, edad_anios, edad_meses, usuario_id)
                 SELECT :nombre, :especie, :raza, :anios, :meses, id
                 FROM usuarios WHERE id = :id AND rol = 'cliente' AND estado = 'Activo'"
            );
            $registro->execute([
                ':nombre' => $datosMascota['nombre'],
                ':especie' => $datosMascota['especie'],
                ':raza' => $datosMascota['raza'] === '' ? 'No especificada' : $datosMascota['raza'],
                ':anios' => $anios,
                ':meses' => $meses,
                ':id' => $clienteId,
            ]);
            if ($registro->rowCount() !== 1) {
                destruirSesionYRedirigir('../login.php');
            }
            $mascotaId = (int) $pdo->lastInsertId();
            unset($_SESSION['csrf_registro_mascota']);
            if ($desdeCitas) {
                $_SESSION['aviso_citas'] = 'Mascota registrada. Ya puedes elegir su servicio y solicitar la cita.';
                header('Location: citas.php?mascota=' . $mascotaId . '&servicio=' . $servicioCita, true, 303);
            } else {
                $_SESSION['aviso_mascota'] = 'Tu mascota se registró correctamente.';
                header('Location: mis-mascotas.php', true, 303);
            }
            exit;
        } catch (PDOException $e) {
            error_log('Error al registrar mascota del cliente: ' . $e->getMessage());
            $errorMascota = 'No se pudo registrar tu mascota. Inténtalo de nuevo.';
        }
    }
}

if ($pdo) {
    try {
        /*
         * Confirma que el usuario de la sesión exista y permanezca 'Activo' en MySQL;
         * en caso contrario revoca la sesión inmediatamente.
         */
        $stmtCliente = $pdo->prepare(
            "SELECT id, nombre FROM usuarios WHERE id = :id AND rol = :rol AND estado = :estado LIMIT 1"
        );
        $stmtCliente->execute([
            ':id'     => $clienteId,
            ':rol'    => 'cliente',
            ':estado' => 'Activo',
        ]);
        $clienteActual = $stmtCliente->fetch();

        if (!$clienteActual) {
            destruirSesionYRedirigir('../login.php');
        }

        $clienteId     = (int) $clienteActual['id'];
        $nombreCliente = trim((string) $clienteActual['nombre']);
        $inicialAvatar = mb_strtoupper(mb_substr($nombreCliente, 0, 1));

        /*
         * Consulta preparada acotada por :usuario_id (tomado de $_SESSION) y estado 'Activo'
         * para impedir que un cliente consulte registros ajenos.
         */
        $stmtMascotas = $pdo->prepare(
            "SELECT id, nombre, especie, raza, edad_anios, edad_meses FROM mascotas WHERE usuario_id = :usuario_id AND estado = :estado ORDER BY id ASC"
        );
        $stmtMascotas->execute([
            ':usuario_id' => $clienteId,
            ':estado'     => 'Activo',
        ]);
        $mascotas = $stmtMascotas->fetchAll();
    } catch (PDOException $e) {
        error_log('Error al cargar mascotas del cliente en cliente/mis-mascotas.php: ' . $e->getMessage());
    }
}

$totalMascotas = count($mascotas);
$textoContadorInicial = 'Mostrando ' . $totalMascotas . ' ' . ($totalMascotas === 1 ? 'mascota' : 'mascotas');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mis mascotas | Cuidado de tus Mascotas</title>

    <!-- Carga el script para inicializar el tema -->
    <script src="../assets/js/tema-inicial.js?v=<?= filemtime(__DIR__ . '/../assets/js/tema-inicial.js') ?>"></script>

    <!-- Primero los estilos compartidos; después los exclusivos de esta pantalla.
         filemtime solicita una versión nueva al guardar cada CSS. -->
    <link rel="stylesheet" href="../assets/css/panel.css?v=<?= filemtime(__DIR__ . '/../assets/css/panel.css') ?>">
    <link rel="stylesheet" href="../assets/css/cliente-mascotas.css?v=<?= filemtime(__DIR__ . '/../assets/css/cliente-mascotas.css') ?>">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/admin-formularios.css">
    <link rel="stylesheet" href="../assets/css/tema.css?v=<?= filemtime(__DIR__ . '/../assets/css/tema.css') ?>">
    <!-- defer espera a que el HTML exista antes de conectar filtros y validaciones. -->
    <script src="../assets/js/interfaz.js?v=<?= filemtime(__DIR__ . '/../assets/js/interfaz.js') ?>" defer></script>
    <script src="../assets/js/mascotas.js?v=<?= filemtime(__DIR__ . '/../assets/js/mascotas.js') ?>" defer></script>
</head>

<body class="pagina-panel" data-modulo="mascotas">
    <div class="estructura-panel">

        <!-- MENU LATERAL: se mantiene visible al desplazar la pagina en escritorio.
             La regla .barra-lateral de panel.css usa position: sticky y top: 0.
             En celulares pasa arriba del contenido para dejar espacio a las tarjetas. -->
        <aside class="barra-lateral">
            <a href="../index.php" class="marca-panel">
                <span aria-hidden="true">🐾</span>
                <span>Cuidado de<br>tus Mascotas</span>
            </a>

            <p class="etiqueta-menu">MI ESPACIO</p>

            <nav class="menu-panel" aria-label="Menú del cliente">
                <!-- Solo el enlace de esta pagina lleva activo y aria-current. -->
                <a href="panel.php">
                    <span aria-hidden="true">⌂</span>
                    Inicio
                </a>

                <a href="mis-mascotas.php" class="activo" aria-current="page">
                    <span aria-hidden="true">🐾</span>
                    Mis mascotas
                </a>

                <a href="perfil.php">
                    <span aria-hidden="true">👤</span>
                    Mi perfil
                </a>
                <a href="servicios.php">Servicios</a>
                <a href="citas.php">
                    <span aria-hidden="true">📅</span>
                    Citas
                </a>
            </nav>

            <a href="../index.php" class="volver-inicio">
                ← Volver al sitio
            </a>

            <a href="../logout.php" class="volver-inicio">
                Cerrar sesión
            </a>
        </aside>

        <!-- Contenido principal. -->
        <main class="contenido-panel">
    <header class="cabecera-panel">
        <div>
            <p class="etiqueta-seccion">MI ESPACIO</p>
            <h1>Mis mascotas</h1>
        </div>

        <div class="usuario-panel">
            <span class="avatar" aria-hidden="true"><?= htmlspecialchars($inicialAvatar, ENT_QUOTES, 'UTF-8') ?></span>

            <div>
                <strong><?= htmlspecialchars($nombreCliente, ENT_QUOTES, 'UTF-8') ?></strong>
                <span>Cliente</span>
            </div>
        </div>
    </header>

    <p class="descripcion-listado">
        Consulta los datos de las mascotas registradas a tu nombre.
    </p>

    <section class="formulario-admin" id="registrar-mascota" aria-labelledby="titulo-registro-mascota">
        <h2 id="titulo-registro-mascota">Registrar mascota</h2>
        <?php if ($avisoMascota !== ''): ?>
            <p role="status"><?= htmlspecialchars($avisoMascota, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <?php if ($errorMascota !== ''): ?>
            <p role="alert"><?= htmlspecialchars($errorMascota, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <form method="post" action="<?= htmlspecialchars($rutaRegistro, ENT_QUOTES, 'UTF-8') ?>#registrar-mascota">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrfMascota, ENT_QUOTES, 'UTF-8') ?>">
            <div class="rejilla-formulario">
                <div class="campo-admin">
                    <label for="nombre-mascota">Nombre</label>
                    <input id="nombre-mascota" name="nombre" maxlength="100" required
                           value="<?= htmlspecialchars($datosMascota['nombre'], ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="campo-admin">
                    <label for="especie-mascota">Especie</label>
                    <select id="especie-mascota" name="especie" required>
                        <option value="">Selecciona una especie</option>
                        <?php foreach ($etiquetasEspecie as $clave => $etiqueta): ?>
                            <option value="<?= $clave ?>"<?= $datosMascota['especie'] === $clave ? ' selected' : '' ?>><?= $etiqueta ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="campo-admin">
                    <label for="raza-mascota">Raza (opcional)</label>
                    <input id="raza-mascota" name="raza" maxlength="100"
                           value="<?= htmlspecialchars($datosMascota['raza'], ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="campo-admin">
                    <label for="edad-anios-mascota">Edad en años</label>
                    <input type="number" id="edad-anios-mascota" name="edad_anios" min="0" max="100" required
                           value="<?= htmlspecialchars($datosMascota['edad_anios'], ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="campo-admin">
                    <label for="edad-meses-mascota">Meses adicionales</label>
                    <input type="number" id="edad-meses-mascota" name="edad_meses" min="0" max="11" required
                           value="<?= htmlspecialchars($datosMascota['edad_meses'], ENT_QUOTES, 'UTF-8') ?>">
                </div>
            </div>
            <div class="acciones-formulario">
                <button class="boton-admin" type="submit"><?= $desdeCitas ? 'Registrar y continuar a citas' : 'Registrar mascota' ?></button>
            </div>
        </form>
    </section>

    <!-- Filtro visual del listado. -->
    <div class="barra-filtros">
        <div class="grupo-filtro">
            <label for="filtro-especie">Filtrar por especie</label>

            <select id="filtro-especie">
                <option value="todas">Todas las especies</option>
                <option value="perro">Perros</option>
                <option value="gato">Gatos</option>
                <option value="ave">Aves</option>
                <option value="conejo">Conejos</option>
                <option value="otro">Otras especies</option>
            </select>
        </div>

        <p id="resultado-filtro" role="status">
            <?= htmlspecialchars($textoContadorInicial, ENT_QUOTES, 'UTF-8') ?>
        </p>
    </div>

    <section aria-labelledby="titulo-listado">
        <h2 id="titulo-listado" class="titulo-listado">
            Mascotas registradas
        </h2>

        <div class="rejilla-mascotas">
            <?php foreach ($mascotas as $mascota): ?>
                <?php
                $especieClave  = strtolower(trim((string) $mascota['especie']));
                $especieNombre = $etiquetasEspecie[$especieClave] ?? ucfirst($especieClave);
                $razaTexto     = trim((string) ($mascota['raza'] ?? '')) ?: 'No especificada';
                $edadTexto     = formatearEdad((int) $mascota['edad_anios'], (int) $mascota['edad_meses']);
                $esGato        = ($especieClave === 'gato');
                $iconoMascota  = $esGato ? '🐱' : '🐶';
                $claseIlust    = $esGato ? 'mascota-ilustracion mascota-ilustracion-gato' : 'mascota-ilustracion';
                ?>
                <!-- data-especie identifica la especie para el filtro. -->
                <article class="tarjeta-mascota" data-especie="<?= htmlspecialchars($especieClave, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="<?= $claseIlust ?>" aria-hidden="true">
                        <?= $iconoMascota ?>
                    </div>

                    <div class="mascota-informacion">
                        <h3><?= htmlspecialchars($mascota['nombre'], ENT_QUOTES, 'UTF-8') ?></h3>

                        <dl class="datos-mascota">
                            <div>
                                <dt>Especie</dt>
                                <dd><?= htmlspecialchars($especieNombre, ENT_QUOTES, 'UTF-8') ?></dd>
                            </div>
                            <div>
                                <dt>Raza</dt>
                                <dd><?= htmlspecialchars($razaTexto, ENT_QUOTES, 'UTF-8') ?></dd>
                            </div>

                            <div>
                                <dt>Edad</dt>
                                <dd><?= htmlspecialchars($edadTexto, ENT_QUOTES, 'UTF-8') ?></dd>
                            </div>

                            <div>
                                <dt>Dueño</dt>
                                <dd><?= htmlspecialchars($nombreCliente, ENT_QUOTES, 'UTF-8') ?></dd>
                            </div>
                        </dl>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <!-- Aparece cuando no hay mascotas o el filtro no encuentra coincidencias. -->
        <div id="listado-vacio" class="estado-vacio-ilustrado" <?= $totalMascotas > 0 ? 'hidden' : '' ?>>
            <span class="estado-vacio-icono" aria-hidden="true">🐾</span>
            <h3><?= $totalMascotas === 0 ? 'Aún no tienes mascotas registradas' : 'Sin coincidencias para esta especie' ?></h3>
            <p>
                <?= $totalMascotas === 0
                    ? 'Usa el formulario para registrar a tu primera mascota.'
                    : 'No hay mascotas registradas que coincidan con la especie seleccionada.' ?>
            </p>
        </div>
    </section>


        </main>
    </div>
</body>
</html>
