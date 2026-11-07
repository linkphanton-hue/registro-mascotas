<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mascotas | Administración | Cuidado de tus Mascotas</title>

    <!-- Reutiliza el diseño general de los paneles. -->

    <!-- Estilos específicos del administrador. -->

    <!-- Primero los estilos compartidos; después los exclusivos de esta pantalla.
         filemtime solicita una versión nueva al guardar cada CSS. -->
    <link rel="stylesheet" href="../assets/css/panel.css?v=<?= filemtime(__DIR__ . '/../assets/css/panel.css') ?>">
    <link rel="stylesheet" href="../assets/css/admin.css?v=<?= filemtime(__DIR__ . '/../assets/css/admin.css') ?>">
    <link rel="stylesheet" href="../assets/css/admin-mascotas.css?v=<?= filemtime(__DIR__ . '/../assets/css/admin-mascotas.css') ?>">
    <!-- defer espera a que el HTML exista antes de conectar filtros y validaciones. -->
    <script src="../assets/js/admin-mascotas.js" defer></script>
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
                <a href="panel.php">
                    <span aria-hidden="true">⌂</span>
                    Inicio
                </a>

                    <!-- Solo la página actual lleva activo y aria-current. -->
                    <a href="mascotas.php" class="activo" aria-current="page">
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
        </aside>

        <main class="contenido-panel">
    <header class="cabecera-panel">
        <div>
            <p class="etiqueta-seccion">ADMINISTRACIÓN</p>
            <h1>Mascotas</h1>
        </div>

        <a href="formulario-mascota.php" class="boton-admin">
            + Registrar mascota
        </a>
    </header>

    <p class="descripcion-listado">
        Consulta y organiza las mascotas registradas en la veterinaria.
    </p>

    <!-- El filtro funciona sobre las filas de ejemplo. -->
    <div class="barra-filtros">
        <div class="grupo-filtro">
            <label for="filtro-especie">Filtrar por especie</label>

            <select id="filtro-especie">
                <option value="todas">Todas las especies</option>
                <option value="perro">Perros</option>
                <option value="gato">Gatos</option>
            </select>
        </div>

        <p id="resultado-filtro" role="status">
            Mostrando 2 mascotas
        </p>
    </div>

    <section aria-labelledby="titulo-listado">
        <h2 id="titulo-listado" class="titulo-listado">
            Listado de mascotas
        </h2>

        <div
            class="tabla-contenedor"
            role="region"
            aria-labelledby="titulo-listado"
            tabindex="0"
        >
            <table class="tabla-admin">
                <caption class="solo-lectores">
                    Mascotas registradas con datos de ejemplo
                </caption>

                <thead>
                    <tr>
                        <th scope="col">Mascota</th>
                        <th scope="col">Especie</th>
                        <th scope="col">Edad</th>
                        <th scope="col">Dueño</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <!-- data-especie permite identificar cada fila. -->
                    <tr data-especie="perro">
                        <th scope="row">Max</th>
                        <td>Perro</td>
                        <td>3 años</td>
                        <td>Laura Pérez</td>

                        <td>
                            <div class="acciones-tabla">
                                <!-- Se habilitarán al implementar las operaciones. -->
                                <button
                                    type="button"
                                    class="accion-tabla accion-editar"
                                    aria-label="Editar a Max"
                                    disabled
                                >
                                    Editar
                                </button>

                                <button
                                    type="button"
                                    class="accion-tabla accion-eliminar"
                                    aria-label="Eliminar a Max"
                                    disabled
                                >
                                    Eliminar
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr data-especie="gato">
                        <th scope="row">Luna</th>
                        <td>Gato</td>
                        <td>2 años</td>
                        <td>Laura Pérez</td>

                        <td>
                            <div class="acciones-tabla">
                                <button
                                    type="button"
                                    class="accion-tabla accion-editar"
                                    aria-label="Editar a Luna"
                                    disabled
                                >
                                    Editar
                                </button>

                                <button
                                    type="button"
                                    class="accion-tabla accion-eliminar"
                                    aria-label="Eliminar a Luna"
                                    disabled
                                >
                                    Eliminar
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Esta fila aparece si no existen coincidencias. -->
                    <tr id="listado-vacio" hidden>
                        <td colspan="5">
                            No hay mascotas para la especie seleccionada.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <p class="aviso-demostracion">
        Datos de ejemplo. Las acciones de editar y eliminar
        todavía no están habilitadas.
    </p>
</main>
    </div>
</body>
</html>