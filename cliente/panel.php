<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mi panel | Cuidado de tus Mascotas</title>

    <!-- Version automatica del CSS para cargar los estilos al guardar cambios. -->

    <!-- Primero los estilos compartidos; después los exclusivos de esta pantalla.
         filemtime solicita una versión nueva al guardar cada CSS. -->
    <link rel="stylesheet" href="../assets/css/panel.css?v=<?= filemtime(__DIR__ . '/../assets/css/panel.css') ?>">
    <link rel="stylesheet" href="../assets/css/cliente-mascotas.css?v=<?= filemtime(__DIR__ . '/../assets/css/cliente-mascotas.css') ?>">
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
        </aside>

        <!-- Contenido principal. -->
        <main class="contenido-panel">
            <header class="cabecera-panel">
                <div>
                    <p class="etiqueta-seccion">PANEL DEL CLIENTE</p>
                    <h1>Mi espacio</h1>
                </div>

                <!-- Nombre de ejemplo: todavía no viene de una sesión. -->
                <div class="usuario-panel">
                    <span class="avatar" aria-hidden="true">L</span>

                    <div>
                        <strong>Laura Pérez</strong>
                        <span>Cliente</span>
                    </div>
                </div>
            </header>

            <!-- Presentación del panel. -->
            <section class="bienvenida" aria-labelledby="titulo-bienvenida">
                <div>
                    <h2 id="titulo-bienvenida">¡Hola, Laura!</h2>
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

            <!-- Resumen con datos de demostración. -->
            <section class="resumen-panel" aria-label="Resumen de mi cuenta">
                <article class="tarjeta-resumen">
                    <span aria-hidden="true">🐶</span>

                    <div>
                        <p>Mascotas registradas</p>
                        <strong>2</strong>
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

                <div class="rejilla-mascotas">

                    <!-- Mascota de ejemplo 1. -->
                    <article class="tarjeta-mascota">
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
                            </dl>
                        </div>
                    </article>

                    <!-- Mascota de ejemplo 2. -->
                    <article class="tarjeta-mascota">
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
                            </dl>
                        </div>
                    </article>
                </div>
            </section>

            <p class="aviso-demostracion">Vista de práctica con datos de ejemplo.</p>
        </main>
    </div>
</body>
</html>