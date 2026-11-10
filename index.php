<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Inicio | Cuidado de tus Mascotas</title>

    <script>
        try {
            if (localStorage.getItem('tema-preferido') === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        } catch (e) {}
    </script>

    <!-- Primero los estilos compartidos; después los exclusivos de esta pantalla.
         filemtime solicita una versión nueva al guardar cada CSS. -->
    <link rel="stylesheet" href="assets/css/inicio.css?v=<?= filemtime(__DIR__ . '/assets/css/inicio.css') ?>">
    <link rel="stylesheet" href="assets/css/tema.css?v=<?= filemtime(__DIR__ . '/assets/css/tema.css') ?>">
    <script src="assets/js/interfaz.js?v=<?= filemtime(__DIR__ . '/assets/js/interfaz.js') ?>" defer></script>
</head>

<body>
    <a class="saltar-contenido" href="#contenido">
        Ir al contenido
    </a>

    <header class="cabecera">
        <nav class="navegacion contenedor" aria-label="Menú principal">
            <a class="marca" href="index.php">
                <span aria-hidden="true">🐾</span>
                Cuidado de tus Mascotas
            </a>

            <div class="menu">
                <a href="#servicios">Servicios</a>
                <a href="#nosotros">Nosotros</a>
                <a href="contacto.php">Contacto</a>
                <a href="login.php" class="boton boton-pequeno">
                    Iniciar sesión
                </a>
            </div>
        </nav>
    </header>

    <main id="contenido">
        <!-- Presentación principal. -->
        <section class="presentacion">
            <div class="contenedor presentacion-contenido">
                <div class="presentacion-texto">
                    <p class="etiqueta">BIENVENIDO A NUESTRA VETERINARIA</p>

                    <h1>Su bienestar comienza con un buen cuidado.</h1>

                    <p class="descripcion">
                        Un espacio para conocer nuestros servicios
                        y consultar la información de tus mascotas.
                        Porque ellos también son parte de la familia.
                    </p>

                    <div class="acciones">
                        <a href="registro.php" class="boton">
                            Crear mi cuenta
                        </a>

                        <a href="#servicios" class="boton boton-secundario">
                            Conocer servicios
                        </a>
                    </div>
                </div>

                <!-- Fondo: assets/img/su cuidado.jpeg; estilos en inicio.css. -->
               <div class="presentacion-visual">

                    <p>Pequeñas patas.<br>Grandes compañeros.</p>
                </div>
            </div>
        </section>

        <!-- Accesos a las áreas del sistema. -->
        <section class="seccion contenedor" aria-labelledby="titulo-accesos">
            <p class="etiqueta">TU ESPACIO</p>
            <h2 id="titulo-accesos">Todo más cerca de ti</h2>

            <div class="rejilla">
                <!-- Fondo: assets/img/mis mascotas.jpeg; clase tarjeta-mascotas. -->
                <article class="tarjeta tarjeta-imagen tarjeta-mascotas">
                    <h3>Mis mascotas</h3>
                    <p>
                        Inicia sesión para consultar las mascotas
                        registradas a tu nombre.
                    </p>
                    <a href="login.php">Acceder como cliente →</a>
                </article>

                <!-- Fondo: assets/img/unete a nuestra comunidad.jpeg; clase tarjeta-comunidad. -->
                <article class="tarjeta tarjeta-imagen tarjeta-comunidad">
                    <h3>Únete a nuestra comunidad</h3>
                    <p>
                        Crea tu cuenta para comenzar a utilizar
                        tu espacio de cliente.
                    </p>
                    <a href="registro.php">Crear una cuenta →</a>
                </article>

                <!-- Fondo: assets/img/area del personal.jpeg; clase tarjeta-personal. -->
                <article class="tarjeta tarjeta-imagen tarjeta-personal">
                    <h3>Área del personal</h3>
                    <p>
                        Acceso para el personal encargado de
                        administrar los registros de la veterinaria.
                    </p>
                    <a href="login.php">Acceder como personal →</a>
                </article>
            </div>
        </section>

        <!-- Información sobre los servicios. -->
        <section id="servicios" class="seccion seccion-suave">
            <div class="contenedor">
                <p class="etiqueta">NUESTROS SERVICIOS</p>
                <h2>Cuidado en cada etapa</h2>
                <p class="introduccion">
                    Conoce las áreas de atención de nuestra veterinaria.
                </p>

                <div class="rejilla">
                    <!-- Fondo: assets/img/consulta veterinaria.jpeg; clase tarjeta-consulta. -->
                <article class="tarjeta tarjeta-imagen tarjeta-consulta">
                        <h3>Consulta veterinaria</h3>
                        <p>
                            Evaluación y orientación para acompañar
                            el bienestar de tu mascota.
                        </p>
                    </article>

                    <!-- Fondo: assets/img/atencion preventiva.jpeg; clase tarjeta-preventiva. -->
                    <article class="tarjeta tarjeta-imagen tarjeta-preventiva">
                        <h3>Atención preventiva</h3>
                        <p>
                            Orientación sobre vacunación, controles
                            y hábitos de cuidado.
                        </p>
                    </article>

                    <!-- Fondo: assets/img/registro de mascotas.jpeg; clase tarjeta-registro. -->
                <article class="tarjeta tarjeta-imagen tarjeta-registro">
                        <h3>Registro de mascotas</h3>
                        <p>
                            Organización de los datos básicos
                            de las mascotas y sus dueños.
                        </p>
                    </article>
                </div>
            </div>
        </section>

        <!-- Información institucional. -->
        <section id="nosotros" class="seccion contenedor">
            <p class="etiqueta">SOBRE NOSOTROS</p>
            <h2>Comprometidos con quienes más quieres</h2>

            <div class="rejilla">
                <article class="tarjeta">
                    <h3>Misión</h3>
                    <p>
                        Promover el cuidado responsable de las mascotas
                        mediante una atención cercana y una gestión
                        organizada de su información.
                    </p>
                </article>

                <article class="tarjeta">
                    <h3>Visión</h3>
                    <p>
                        Ser un espacio de confianza para las familias,
                        reconocido por el trato respetuoso y el compromiso
                        con el bienestar animal.
                    </p>
                </article>

                <article class="tarjeta">
                    <h3>Objetivos</h3>
                    <ul class="lista-objetivos">
                        <li>Organizar el registro de mascotas.</li>
                        <li>Facilitar la consulta a sus dueños.</li>
                        <li>Fomentar el cuidado responsable.</li>
                    </ul>
                </article>
            </div>
        </section>

        <section class="invitacion">
            <div class="contenedor">
                <h2>Un espacio para ti y tus mascotas</h2>
                <p>Accede a tu cuenta y consulta su información.</p>
                <a href="login.php" class="boton">Ir a mi cuenta</a>
            </div>
        </section>
    </main>

    <footer id="contacto" class="pie">
        <div class="contenedor pie-contenido">
            <div>
                <p class="marca">🐾 Cuidado de tus Mascotas</p>
                <p>Unidos por su bienestar.</p>
            </div>

            <nav aria-label="Enlaces del pie">
                <a href="contacto.php">Contacto</a>
                <a href="login.php">Iniciar sesión</a>
                <a href="registro.php">Registrarse</a>
                <a href="recuperar-contrasena.php">
                    Recuperar contraseña
                </a>
            </nav>
        </div>
    </footer>
</body>
</html>