<?php
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
                    ? 'Cuando el personal veterinario registre a tus mascotas, podrás consultarlas aquí.'
                    : 'No hay mascotas registradas que coincidan con la especie seleccionada.' ?>
            </p>
        </div>
    </section>

    <p class="aviso-demostracion">
        Vista de práctica con datos de ejemplo.
    </p>

        </main>
    </div>
</body>
</html>
