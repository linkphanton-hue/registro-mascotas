<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Panel de administración | Cuidado de tus Mascotas</title>

    <!-- Reutiliza el diseño general de los paneles. -->

    <!-- Estilos específicos del administrador. -->

    <!-- Primero los estilos compartidos; después los exclusivos de esta pantalla.
         filemtime solicita una versión nueva al guardar cada CSS. -->
    <link rel="stylesheet" href="../assets/css/panel.css?v=<?= filemtime(__DIR__ . '/../assets/css/panel.css') ?>">
    <link rel="stylesheet" href="../assets/css/admin.css?v=<?= filemtime(__DIR__ . '/../assets/css/admin.css') ?>">
    <link rel="stylesheet" href="../assets/css/admin-panel.css?v=<?= filemtime(__DIR__ . '/../assets/css/admin-panel.css') ?>">
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
        </aside>

        <main class="contenido-panel">
            <header class="cabecera-panel">
                <div>
                    <p class="etiqueta-seccion">ADMINISTRACIÓN</p>
                    <h1>Panel principal</h1>
                </div>

                <!-- Identidad de ejemplo; todavía no existe autenticación. -->
                <div class="usuario-panel">
                    <span class="avatar" aria-hidden="true">A</span>

                    <div>
                        <strong>Administrador</strong>
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

            <!-- Resumen de demostración, coherente con la tabla inferior. -->
            <section class="resumen-panel resumen-admin" aria-label="Resumen">
                <article class="tarjeta-resumen">
                    <span aria-hidden="true">🐾</span>

                    <div>
                        <p>Mascotas registradas</p>
                        <strong>2</strong>
                    </div>
                </article>

                <article class="tarjeta-resumen">
                    <span aria-hidden="true">👥</span>

                    <div>
                        <p>Clientes registrados</p>
                        <strong>1</strong>
                    </div>
                </article>

                <article class="tarjeta-resumen">
                    <span aria-hidden="true">📋</span>

                    <div>
                        <p>Especies registradas</p>
                        <strong>2</strong>
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

                <!--
                    El contenedor permite desplazar la tabla horizontalmente
                    en pantallas pequeñas sin desbordar toda la página.
                -->
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
                            <tr>
                                <th scope="row">Max</th>
                                <td>Perro</td>
                                <td>3 años</td>
                                <td>Laura Pérez</td>
                            </tr>

                            <tr>
                                <th scope="row">Luna</th>
                                <td>Gato</td>
                                <td>2 años</td>
                                <td>Laura Pérez</td>
                            </tr>
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