<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Registrar cliente | Administración | Cuidado de tus Mascotas</title>

    <!-- Reutiliza el diseño general de los paneles. -->

    <!-- Estilos específicos del administrador. -->

    <!-- Primero los estilos compartidos; después los exclusivos de esta pantalla.
         filemtime solicita una versión nueva al guardar cada CSS. -->
    <link rel="stylesheet" href="../assets/css/panel.css?v=<?= filemtime(__DIR__ . '/../assets/css/panel.css') ?>">
    <link rel="stylesheet" href="../assets/css/admin.css?v=<?= filemtime(__DIR__ . '/../assets/css/admin.css') ?>">
    <link rel="stylesheet" href="../assets/css/admin-formularios.css?v=<?= filemtime(__DIR__ . '/../assets/css/admin-formularios.css') ?>">
    <!-- defer espera a que el HTML exista antes de conectar filtros y validaciones. -->
    <script src="../assets/js/formulario-cliente.js" defer></script>
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

                <a href="clientes.php" class="activo" >
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
            <p class="etiqueta-seccion">ADMINISTRACIÓN / CLIENTES</p>
            <h1>Registrar cliente</h1>
        </div>

        <a href="clientes.php" class="enlace-volver">
            ← Volver a clientes
        </a>
    </header>

    <p class="descripcion-listado">
        Completa los datos para preparar una cuenta de cliente.
    </p>

    <section class="formulario-admin" aria-labelledby="titulo-datos">
        <h2 id="titulo-datos">Datos del cliente</h2>

        <p class="ayuda-formulario">
            Todos los campos son obligatorios.
        </p>

        <!--
            El rol no se selecciona aquí.
            Cuando implementemos PHP, esta operación creará únicamente clientes.
        -->
        <form id="formulario-cliente" method="post">
            <div class="rejilla-formulario">
                <div class="campo-admin">
                    <label for="nombre">Nombre completo</label>

                    <input
                        type="text"
                        id="nombre"
                        name="nombre"
                        placeholder="Escribe el nombre completo"
                        autocomplete="name"
                        maxlength="100"
                        required
                    >
                </div>

                <div class="campo-admin">
                    <label for="correo">Correo electrónico</label>

                    <input
                        type="email"
                        id="correo"
                        name="correo"
                        placeholder="cliente@correo.com"
                        autocomplete="off"
                        maxlength="254"
                        required
                    >
                </div>

                <div class="campo-admin">
                    <label for="contrasena">Contraseña inicial</label>

                    <input
                        type="password"
                        id="contrasena"
                        name="contrasena"
                        placeholder="Mínimo 8 caracteres"
                        autocomplete="new-password"
                        minlength="8"
                        aria-describedby="ayuda-clave"
                        required
                    >

                    <small id="ayuda-clave">
                        Utiliza al menos 8 caracteres.
                    </small>
                </div>

                <div class="campo-admin">
                    <label for="confirmar-contrasena">
                        Confirmar contraseña
                    </label>

                    <input
                        type="password"
                        id="confirmar-contrasena"
                        name="confirmar_contrasena"
                        placeholder="Repite la contraseña"
                        autocomplete="new-password"
                        minlength="8"
                        required
                    >
                </div>
            </div>

            <p
                id="mensaje-cliente"
                class="mensaje-admin"
                role="status"
            ></p>

            <div class="acciones-formulario">
                <button type="submit" class="boton-admin">
                    Registrar cliente
                </button>

                <a href="clientes.php" class="boton-cancelar">
                    Cancelar
                </a>
            </div>
        </form>
    </section>

    <p class="aviso-demostracion">
        Formulario de práctica. Todavía no guarda cuentas.
    </p>
</main>
    </div>
</body>
</html>