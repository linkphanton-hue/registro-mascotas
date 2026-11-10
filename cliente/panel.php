<?php
require_once __DIR__ . '/../includes/sesion.php';
requerirRol('cliente', '../login.php');

require_once __DIR__ . '/../includes/conexion.php';
require_once __DIR__ . '/../includes/formato.php';

$clienteId     = (int) $_SESSION['usuario_id'];
$nombreCliente = trim((string) $_SESSION['usuario_nombre']);
$inicialAvatar = mb_strtoupper(mb_substr($nombreCliente, 0, 1));
$partesNombre  = preg_split('/\s+/', $nombreCliente);
$primerNombre  = $partesNombre[0] ?: $nombreCliente;
$mascotas      = [];

$etiquetasEspecie = [
    'perro'  => 'Perro',
    'gato'   => 'Gato',
    'ave'    => 'Ave',
    'conejo' => 'Conejo',
    'otro'   => 'Otra especie',
];

if ($pdo) {
    try {
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
        $partesNombre  = preg_split('/\s+/', $nombreCliente);
        $primerNombre  = $partesNombre[0] ?: $nombreCliente;

        $stmtMascotas = $pdo->prepare(
            "SELECT id, nombre, especie, raza, edad_anios, edad_meses FROM mascotas WHERE usuario_id = :usuario_id AND estado = :estado ORDER BY id ASC"
        );
        $stmtMascotas->execute([
            ':usuario_id' => $clienteId,
            ':estado'     => 'Activo',
        ]);
        $mascotas = $stmtMascotas->fetchAll();
    } catch (PDOException $e) {
        error_log('Error al cargar panel del cliente en cliente/panel.php: ' . $e->getMessage());
    }
}

$totalMascotas = count($mascotas);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mi panel | Cuidado de tus Mascotas</title>

    <script>
        try {
            if (localStorage.getItem('tema-preferido') === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        } catch (e) {}
    </script>

    <!-- Primero los estilos compartidos; después los exclusivos de esta pantalla.
         filemtime solicita una versión nueva al guardar cada CSS. -->
    <link rel="stylesheet" href="../assets/css/panel.css?v=<?= filemtime(__DIR__ . '/../assets/css/panel.css') ?>">
    <link rel="stylesheet" href="../assets/css/cliente-mascotas.css?v=<?= filemtime(__DIR__ . '/../assets/css/cliente-mascotas.css') ?>">
    <link rel="stylesheet" href="../assets/css/tema.css?v=<?= filemtime(__DIR__ . '/../assets/css/tema.css') ?>">
    <script src="../assets/js/interfaz.js?v=<?= filemtime(__DIR__ . '/../assets/js/interfaz.js') ?>" defer></script>
</head>

<body class="pagina-panel" data-modulo="panel">
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
                <!-- Inicio se resalta porque esta es la pagina principal del panel. -->
                <a href="panel.php" class="activo" aria-current="page">
                    <span aria-hidden="true">⌂</span>
                    Inicio
                </a>

                <a href="mis-mascotas.php">
                    <span aria-hidden="true">🐾</span>
                    Mis mascotas
                </a>

                <a href="perfil.php">
                    <span aria-hidden="true">👤</span>
                    Mi perfil
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
                    <p class="etiqueta-seccion">PANEL DEL CLIENTE</p>
                    <h1>Mi espacio</h1>
                </div>

                <div class="usuario-panel">
                    <span class="avatar" aria-hidden="true"><?= htmlspecialchars($inicialAvatar, ENT_QUOTES, 'UTF-8') ?></span>

                    <div>
                        <strong><?= htmlspecialchars($nombreCliente, ENT_QUOTES, 'UTF-8') ?></strong>
                        <span>Cliente</span>
                    </div>
                </div>
            </header>

            <!-- Presentación del panel. -->
            <section class="bienvenida" aria-labelledby="titulo-bienvenida">
                <div>
                    <h2 id="titulo-bienvenida">¡Hola, <?= htmlspecialchars($primerNombre, ENT_QUOTES, 'UTF-8') ?>!</h2>
                    <p>
                        Aquí encontrarás la información de tus
                        compañeros de cuatro patas.
                    </p>

                    <a href="mis-mascotas.php" class="boton-panel">
                        Ver mis mascotas
                    </a>
                </div>

                <span class="bienvenida-huella" aria-hidden="true">🐾</span>
            </section>

            <!-- Resumen conectado a la base de datos. -->
            <section class="resumen-panel" aria-label="Resumen de mi cuenta">
                <article class="tarjeta-resumen">
                    <span aria-hidden="true">🐶</span>

                    <div>
                        <p>Mascotas registradas</p>
                        <strong><?= $totalMascotas ?></strong>
                    </div>
                </article>

                <article class="tarjeta-resumen">
                    <span aria-hidden="true">📋</span>

                    <div>
                        <p>Información disponible</p>
                        <strong>Nombre, especie y edad</strong>
                    </div>
                </article>
            </section>

            <!-- Vista previa de las mascotas del cliente. -->
            <section aria-labelledby="titulo-mascotas">
                <div class="encabezado-seccion">
                    <h2 id="titulo-mascotas">Mis mascotas</h2>
                    <a href="mis-mascotas.php">Ver todas →</a>
                </div>

                <?php if ($totalMascotas === 0): ?>
                    <div class="estado-vacio-ilustrado">
                        <span class="estado-vacio-icono" aria-hidden="true">🐾</span>
                        <h3>Aún no tienes mascotas registradas</h3>
                        <p>Cuando la veterinaria asocie tus mascotas a tu cuenta, aparecerán aquí.</p>
                    </div>
                <?php else: ?>
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
                            <article class="tarjeta-mascota">
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
                                    </dl>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <p class="aviso-demostracion">Vista de práctica con datos de ejemplo.</p>
        </main>
    </div>
</body>
</html>
