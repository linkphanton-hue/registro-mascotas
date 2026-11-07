<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mi perfil | Cuidado de tus Mascotas</title>

    <!-- Version automatica del CSS para cargar los estilos al guardar cambios. -->

    <!-- Primero los estilos compartidos; después los exclusivos de esta pantalla.
         filemtime solicita una versión nueva al guardar cada CSS. -->
    <link rel="stylesheet" href="../assets/css/panel.css?v=<?= filemtime(__DIR__ . '/../assets/css/panel.css') ?>">
    <link rel="stylesheet" href="../assets/css/cliente-perfil.css?v=<?= filemtime(__DIR__ . '/../assets/css/cliente-perfil.css') ?>">
</head>

<body class="pagina-panel">
    <div class="estructura-panel">

        <!-- MENU LATERAL: se mantiene visible al desplazar la pagina en escritorio.
             La regla .barra-lateral de panel.css usa position: sticky y top: 0.
             En celulares pasa arriba del contenido para dejar espacio a las tarjetas. -->
        <aside class="barra-lateral">
            <!-- Marca del sistema: vuelve a la pagina publica. -->
            <a href="../index.php" class="marca-panel">
                <span aria-hidden="true">🐾</span>
                <span>Cuidado de<br>tus Mascotas</span>
            </a>

            <p class="etiqueta-menu">MI ESPACIO</p>

            <nav class="menu-panel" aria-label="Menú del cliente">
                <!-- El enlace activo identifica la pagina actual; no necesita JavaScript. -->
                <a href="panel.php">
                    <span aria-hidden="true">⌂</span>
                    Inicio
                </a>

                <a href="mis-mascotas.php">
                    <span aria-hidden="true">🐾</span>
                    Mis mascotas
                </a>

                <a href="perfil.php" class="activo" aria-current="page">
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
            <h1>Mi perfil</h1>
        </div>

        <!-- Datos de ejemplo; más adelante vendrán de la sesión. -->
        <div class="usuario-panel">
            <span class="avatar" aria-hidden="true">L</span>

            <div>
                <strong>Laura Pérez</strong>
                <span>Cliente</span>
            </div>
        </div>
    </header>

    <p class="descripcion-listado">
        Consulta la información de tu cuenta.
    </p>

    <section class="perfil-tarjeta" aria-labelledby="titulo-perfil">
        <div class="perfil-presentacion">
            <span class="perfil-avatar" aria-hidden="true">L</span>

            <div>
                <h2 id="titulo-perfil">Laura Pérez</h2>
                <p>Cuenta de cliente</p>
            </div>
        </div>

        <!--
            Datos de demostración en modo consulta.
            readonly permite leer y copiar el contenido sin modificarlo.
        -->
        <div class="perfil-datos">
            <div class="perfil-campo">
                <label for="perfil-nombre">Nombre completo</label>

                <input
                    type="text"
                    id="perfil-nombre"
                    value="Laura Pérez"
                    readonly
                >
            </div>

            <div class="perfil-campo">
                <label for="perfil-correo">Correo electrónico</label>

                <input
                    type="email"
                    id="perfil-correo"
                    value="laura@example.com"
                    readonly
                >
            </div>
        </div>

        <!-- La edición se habilitará al implementar el guardado. -->
        <div class="perfil-acciones">
            <button
                type="button"
                class="boton-editar-perfil"
                aria-describedby="aviso-edicion"
                disabled
            >
                Editar mis datos
            </button>

            <p id="aviso-edicion" class="perfil-ayuda">
                La edición estará disponible cuando conectemos
                el sistema a la base de datos.
            </p>
        </div>
    </section>

    <section class="perfil-seguridad" aria-labelledby="titulo-seguridad">
        <div>
            <h2 id="titulo-seguridad">Seguridad de la cuenta</h2>
            <p>Accede al formulario para cambiar tu contraseña.</p>
        </div>

        <!-- Abre el formulario visual; todavía no actualiza contraseñas. -->
        <a href="../cambiar-contrasena.php" class="enlace-contrasena">
            Cambiar contraseña →
        </a>
    </section>

    <p class="aviso-demostracion">
        Vista de práctica con datos de ejemplo.
    </p>
</main>
    </div>
</body>
</html>