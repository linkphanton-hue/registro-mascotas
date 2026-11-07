<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mis mascotas | Cuidado de tus Mascotas</title>

    <!-- Version automatica del CSS para cargar los estilos al guardar cambios. -->

    <!-- Primero los estilos compartidos; después los exclusivos de esta pantalla.
         filemtime solicita una versión nueva al guardar cada CSS. -->
    <link rel="stylesheet" href="../assets/css/panel.css?v=<?= filemtime(__DIR__ . '/../assets/css/panel.css') ?>">
    <link rel="stylesheet" href="../assets/css/cliente-mascotas.css?v=<?= filemtime(__DIR__ . '/../assets/css/cliente-mascotas.css') ?>">
    <!-- defer espera a que el HTML exista antes de conectar filtros y validaciones. -->
    <script src="../assets/js/mascotas.js" defer></script>
</head>

<body class="pagina-panel">
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
        </aside>

        <!-- Contenido principal. -->
        <main class="contenido-panel">
    <header class="cabecera-panel">
        <div>
            <p class="etiqueta-seccion">MI ESPACIO</p>
            <h1>Mis mascotas</h1>
        </div>

        <!-- Usuario de ejemplo. -->
        <div class="usuario-panel">
            <span class="avatar" aria-hidden="true">L</span>

            <div>
                <strong>Laura Pérez</strong>
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
            </select>
        </div>

        <p id="resultado-filtro" role="status">
            Mostrando 2 mascotas
        </p>
    </div>

    <section aria-labelledby="titulo-listado">
        <h2 id="titulo-listado" class="titulo-listado">
            Mascotas registradas
        </h2>

        <div class="rejilla-mascotas">
            <!-- data-especie identifica la especie para el filtro. -->
            <article class="tarjeta-mascota" data-especie="perro">
                <div class="mascota-ilustracion" aria-hidden="true">
                    🐶
                </div>

                <div class="mascota-informacion">
                    <h3>Max</h3>

                    <dl class="datos-mascota">
                        <div>
                            <dt>Especie</dt>
                            <dd>Perro</dd>
                        </div>
                        <div>
                            <dt>Raza</dt>
                            <dd>Golden Retriever</dd>
                        </div>

                        <div>
                            <dt>Edad</dt>
                            <dd>3 años</dd>
                        </div>

                        <div>
                            <dt>Dueño</dt>
                            <dd>Laura Pérez</dd>
                        </div>
                    </dl>
                </div>
            </article>

            <article class="tarjeta-mascota" data-especie="gato">
                <div
                    class="mascota-ilustracion mascota-ilustracion-gato"
                    aria-hidden="true"
                >
                    🐱
                </div>

                <div class="mascota-informacion">
                    <h3>Luna</h3>

                    <dl class="datos-mascota">
                        <div>
                            <dt>Especie</dt>
                            <dd>Gato</dd>
                        </div>
                        <div>
                            <dt>Raza</dt>
                            <dd>Siamés</dd>
                        </div>

                        <div>
                            <dt>Edad</dt>
                            <dd>2 años</dd>
                        </div>

                        <div>
                            <dt>Dueño</dt>
                            <dd>Laura Pérez</dd>
                        </div>
                    </dl>
                </div>
            </article>
        </div>

        <!-- Aparece cuando el filtro no encuentra coincidencias. -->
        <p id="listado-vacio" class="estado-vacio" hidden>
            No hay mascotas para la especie seleccionada.
        </p>
    </section>

    <p class="aviso-demostracion">
        Vista de práctica con datos de ejemplo.
    </p>

        </main>
    </div>
<!-- Conecta el filtro por especie; ../ sale de la carpeta cliente. -->
</body>
</html>