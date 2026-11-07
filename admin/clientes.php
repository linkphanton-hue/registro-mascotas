<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Clientes | Administración | Cuidado de tus Mascotas</title>

    <!-- Reutiliza el diseño general de los paneles. -->

    <!-- Estilos específicos del administrador. -->

    <!-- Primero los estilos compartidos; después los exclusivos de esta pantalla.
         filemtime solicita una versión nueva al guardar cada CSS. -->
    <link rel="stylesheet" href="../assets/css/panel.css?v=<?= filemtime(__DIR__ . '/../assets/css/panel.css') ?>">
    <link rel="stylesheet" href="../assets/css/admin.css?v=<?= filemtime(__DIR__ . '/../assets/css/admin.css') ?>">
    <link rel="stylesheet" href="../assets/css/admin-clientes.css?v=<?= filemtime(__DIR__ . '/../assets/css/admin-clientes.css') ?>">
    <!-- defer espera a que el HTML exista antes de conectar filtros y validaciones. -->
    <script src="../assets/js/admin-clientes.js" defer></script>
</head>

<body class="pagina-panel">
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
                <a href="panel.php" >
                    <span aria-hidden="true">⌂</span>
                    Inicio
                </a>

                <a href="mascotas.php">
                    <span aria-hidden="true">🐾</span>
                    Mascotas
                </a>

                <a href="clientes.php" class="activo" aria-current="page">
                    <span aria-hidden="true">👥</span>
                    Clientes
                </a>
            </nav>

            <a href="../index.php" class="volver-inicio">
                ← Volver al sitio
            </a>
        </aside>

        <main class="contenido-panel">
    <header class="cabecera-panel">
        <div>
            <p class="etiqueta-seccion">ADMINISTRACIÓN</p>
            <h1>Clientes</h1>
        </div>

        <!-- Enlaza al formulario que construiremos después. -->
        <a href="formulario-cliente.php" class="boton-admin">
            + Registrar cliente
        </a>
    </header>

    <p class="descripcion-listado">
        Consulta los datos de los dueños de las mascotas.
    </p>

    <!-- JavaScript utiliza este campo para filtrar la tabla. -->
    <div class="barra-filtros">
        <div class="grupo-filtro">
            <label for="buscar-cliente">
                Buscar por nombre o correo
            </label>

            <input
                type="search"
                id="buscar-cliente"
                placeholder="Escribe un nombre o correo"
                aria-controls="tabla-clientes"
            >
        </div>

        <p id="resultado-clientes" role="status">
            Mostrando 1 cliente
        </p>
    </div>

    <section aria-labelledby="titulo-clientes">
        <h2 id="titulo-clientes" class="titulo-listado">
            Clientes registrados
        </h2>

        <div
            class="tabla-contenedor"
            role="region"
            aria-labelledby="titulo-clientes"
            tabindex="0"
        >
            <table id="tabla-clientes" class="tabla-admin">
                <caption class="solo-lectores">
                    Clientes registrados con datos de ejemplo
                </caption>

                <thead>
                    <tr>
                        <th scope="col">Nombre completo</th>
                        <th scope="col">Correo electrónico</th>
                        <th scope="col">Mascotas</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <!--
                        data-cliente identifica las filas que se pueden buscar.
                        Los datos son de ejemplo.
                    -->
                    <tr data-cliente>
                        <th scope="row" class="cliente-nombre">
                            Laura Pérez
                        </th>

                        <td class="cliente-correo">
                            laura@example.com
                        </td>

                        <td>2</td>

                        <td>
                            <div class="acciones-tabla">
                                <!-- Se habilitará cuando implementemos la edición. -->
                                <button
                                    type="button"
                                    class="accion-tabla accion-editar"
                                    aria-label="Editar a Laura Pérez"
                                    disabled
                                >
                                    Editar
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr id="clientes-vacio" hidden>
                        <td colspan="4">
                            No se encontraron clientes con esa búsqueda.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <p class="aviso-demostracion">
        Datos de ejemplo. La edición todavía no está habilitada.
    </p>
</main>
    </div>
</body>
</html>