<?php
require_once __DIR__ . '/../includes/sesion.php';
requerirRol('admin', '../login.php');

require_once __DIR__ . '/../includes/conexion.php';
require_once __DIR__ . '/../includes/formato.php';

$totalMascotas = 0;
$totalClientes = 0;
$totalEspecies = 0;
$mascotasRecientes = [];

$etiquetasEspecie = [
    'perro'  => 'Perro',
    'gato'   => 'Gato',
    'ave'    => 'Ave',
    'conejo' => 'Conejo',
    'otro'   => 'Otra especie',
];

if ($pdo) {
    try {
        $stmtMetricas = $pdo->prepare(
            "SELECT
                (SELECT COUNT(*) FROM mascotas WHERE estado = :est_m1) AS total_mascotas,
                (SELECT COUNT(*) FROM usuarios WHERE rol = :rol_c AND estado = :est_u1) AS total_clientes,
                (SELECT COUNT(DISTINCT especie) FROM mascotas WHERE estado = :est_m2) AS total_especies"
        );
        $stmtMetricas->execute([
            ':est_m1' => 'Activo',
            ':rol_c'  => 'cliente',
            ':est_u1' => 'Activo',
            ':est_m2' => 'Activo',
        ]);
        $metricas = $stmtMetricas->fetch();

        if ($metricas) {
            $totalMascotas = (int) $metricas['total_mascotas'];
            $totalClientes = (int) $metricas['total_clientes'];
            $totalEspecies = (int) $metricas['total_especies'];
        }

        $stmtRecientes = $pdo->prepare(
            "SELECT
                mascotas.nombre,
                mascotas.especie,
                mascotas.edad_anios,
                mascotas.edad_meses,
                usuarios.nombre AS dueno_nombre
            FROM mascotas
            INNER JOIN usuarios ON mascotas.usuario_id = usuarios.id
            WHERE mascotas.estado = :estado_mascota
              AND usuarios.estado = :estado_usuario
            ORDER BY mascotas.id DESC
            LIMIT 10"
        );
        $stmtRecientes->execute([
            ':estado_mascota' => 'Activo',
            ':estado_usuario' => 'Activo',
        ]);
        $mascotasRecientes = $stmtRecientes->fetchAll();
    } catch (PDOException $e) {
        error_log('Error al consultar resumen en admin/panel.php: ' . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Panel de administración | Cuidado de tus Mascotas</title>

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
    <link rel="stylesheet" href="../assets/css/admin.css?v=<?= filemtime(__DIR__ . '/../assets/css/admin.css') ?>">
    <link rel="stylesheet" href="../assets/css/admin-panel.css?v=<?= filemtime(__DIR__ . '/../assets/css/admin-panel.css') ?>">
    <link rel="stylesheet" href="../assets/css/tema.css?v=<?= filemtime(__DIR__ . '/../assets/css/tema.css') ?>">
    <script src="../assets/js/interfaz.js?v=<?= filemtime(__DIR__ . '/../assets/js/interfaz.js') ?>" defer></script>
</head>

<body class="pagina-panel" data-modulo="panel">
    <div class="estructura-panel">

        <!--
            El menú permanece visible en escritorio mediante
            .barra-lateral, definida en panel.css.
        -->
        <aside class="barra-lateral">
            <a href="../index.php" class="marca-panel">
                <span aria-hidden="true">🐾</span>
                <span>Cuidado de<br>tus Mascotas</span>
            </a>

            <p class="etiqueta-menu">ADMINISTRACIÓN</p>

            <nav class="menu-panel" aria-label="Menú del administrador">
                <!-- Solo la página actual lleva la clase activo. -->
                <a href="panel.php" class="activo" aria-current="page">
                    <span aria-hidden="true">⌂</span>
                    Inicio
                </a>

                <a href="mascotas.php">
                    <span aria-hidden="true">🐾</span>
                    Mascotas
                </a>

                <a href="clientes.php">
                    <span aria-hidden="true">👥</span>
                    Clientes
                </a>
            </nav>

            <a href="../index.php" class="volver-inicio">
                ← Volver al sitio
            </a>

            <a href="../logout.php" class="volver-inicio">
                Cerrar sesión
            </a>
        </aside>

        <main class="contenido-panel">
            <header class="cabecera-panel">
                <div>
                    <p class="etiqueta-seccion">ADMINISTRACIÓN</p>
                    <h1>Panel principal</h1>
                </div>

                <div class="usuario-panel">
                    <span class="avatar" aria-hidden="true">A</span>

                    <div>
                        <strong><?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Administrador', ENT_QUOTES, 'UTF-8') ?></strong>
                        <span>Personal de la veterinaria</span>
                    </div>
                </div>
            </header>

            <section class="bienvenida" aria-labelledby="titulo-bienvenida">
                <div>
                    <h2 id="titulo-bienvenida">
                        Todo listo para un buen cuidado
                    </h2>

                    <p>
                        Organiza los registros de mascotas y consulta
                        la información de sus dueños.
                    </p>

                    <a href="formulario-mascota.php" class="boton-panel">
                        + Registrar mascota
                    </a>
                </div>

                <span class="bienvenida-huella" aria-hidden="true">
                    🐾
                </span>
            </section>

            <!-- Resumen dinámico conectado a la base de datos. -->
            <section class="resumen-panel resumen-admin" aria-label="Resumen">
                <article class="tarjeta-resumen">
                    <span aria-hidden="true">🐾</span>

                    <div>
                        <p>Mascotas registradas</p>
                        <strong><?= $totalMascotas ?></strong>
                    </div>
                </article>

                <article class="tarjeta-resumen">
                    <span aria-hidden="true">👥</span>

                    <div>
                        <p>Clientes registrados</p>
                        <strong><?= $totalClientes ?></strong>
                    </div>
                </article>

                <article class="tarjeta-resumen">
                    <span aria-hidden="true">📋</span>

                    <div>
                        <p>Especies registradas</p>
                        <strong><?= $totalEspecies ?></strong>
                    </div>
                </article>
            </section>

            <section aria-labelledby="titulo-accesos">
                <div class="encabezado-seccion">
                    <h2 id="titulo-accesos">Accesos rápidos</h2>
                </div>

                <div class="accesos-admin">
                    <a href="mascotas.php" class="acceso-admin">
                        <span class="acceso-icono" aria-hidden="true">🐶</span>
                        <h3>Gestionar mascotas</h3>
                        <p>Consultar los registros de las mascotas.</p>
                        <span class="acceso-enlace">Ver mascotas →</span>
                    </a>

                    <a href="clientes.php" class="acceso-admin">
                        <span class="acceso-icono" aria-hidden="true">👥</span>
                        <h3>Gestionar clientes</h3>
                        <p>Consultar los datos de los dueños.</p>
                        <span class="acceso-enlace">Ver clientes →</span>
                    </a>
                </div>
            </section>

            <section class="seccion-registros" aria-labelledby="titulo-registros">
                <div class="encabezado-seccion">
                    <h2 id="titulo-registros">Mascotas registradas</h2>
                    <a href="mascotas.php">Ver listado completo →</a>
                </div>

                <div
                    class="tabla-contenedor"
                    role="region"
                    aria-labelledby="titulo-registros"
                    tabindex="0"
                >
                    <table class="tabla-admin">
                        <caption class="solo-lectores">
                            Resumen de mascotas con datos de ejemplo
                        </caption>

                        <thead>
                            <tr>
                                <th scope="col">Mascota</th>
                                <th scope="col">Especie</th>
                                <th scope="col">Edad</th>
                                <th scope="col">Dueño</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($mascotasRecientes as $mascota): ?>
                                <?php
                                $especieClave = strtolower(trim((string) $mascota['especie']));
                                $especieNombre = $etiquetasEspecie[$especieClave] ?? ucfirst($especieClave);
                                $edadTexto = formatearEdad((int) $mascota['edad_anios'], (int) $mascota['edad_meses']);
                                ?>
                                <tr>
                                    <th scope="row"><?= htmlspecialchars($mascota['nombre'], ENT_QUOTES, 'UTF-8') ?></th>
                                    <td><?= htmlspecialchars($especieNombre, ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($edadTexto, ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($mascota['dueno_nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (empty($mascotasRecientes)): ?>
                                <tr>
                                    <td colspan="4">
                                        <div class="estado-vacio-ilustrado">
                                            <span class="estado-vacio-icono" aria-hidden="true">🐾</span>
                                            <h3>Aún no hay mascotas registradas</h3>
                                            <p>Registra la primera mascota desde el botón superior para verla aquí.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <p class="aviso-demostracion">
                Vista de práctica con datos de ejemplo.
            </p>
        </main>
    </div>
</body>
</html>
