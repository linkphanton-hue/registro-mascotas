<?php
/**
 * Bandeja administrativa de mensajes de contacto.
 *
 * Lista los mensajes enviados desde contacto.php y genera enlaces directos de
 * redacción en Gmail para responder al remitente sin consultar la base de datos.
 *
 * @package RegistroMascotas\Admin
 */

/* Bloquea el acceso anónimo o de clientes mediante verificación RBAC contra MySQL */
require_once __DIR__ . '/../includes/sesion.php';
requerirRol('admin', '../login.php');

require_once __DIR__ . '/../includes/conexion.php';

/**
 * Genera la URL oficial de redacción web de Gmail con destinatario, asunto y saludo
 * codificados mediante rawurlencode() (RFC 3986) para evitar rupturas en los parámetros.
 *
 * @param string $correo Dirección electrónica del remitente.
 * @param string $nombre Nombre del remitente para personalizar el saludo inicial.
 * @param string $asunto Asunto original de la consulta recibida.
 * @return string URL completa lista para abrir el compositor de Gmail en pestaña nueva.
 */
function construirUrlGmail(string $correo, string $nombre, string $asunto): string
{
    $asuntoRespuesta = 'Re: ' . trim($asunto);
    $cuerpoRespuesta = "Hola " . trim($nombre) . ",\n\n"
        . "Gracias por comunicarte con Cuidado de tus Mascotas respecto a \"" . trim($asunto) . "\".\n\n";

    return 'https://mail.google.com/mail/?view=cm&fs=1'
        . '&to=' . rawurlencode(trim($correo))
        . '&su=' . rawurlencode($asuntoRespuesta)
        . '&body=' . rawurlencode($cuerpoRespuesta);
}

/**
 * Convierte la marca temporal SQL (YYYY-MM-DD HH:MM:SS) al formato legible d/m/Y H:i,
 * devolviendo el valor original si la cadena no puede parsearse con DateTimeImmutable.
 *
 * @param string $fechaSql Fecha y hora almacenadas en la columna creado_en.
 * @return string Fecha formateada para su lectura en la tabla administrativa.
 */
function formatearFechaMensaje(string $fechaSql): string
{
    try {
        return (new DateTimeImmutable($fechaSql))->format('d/m/Y H:i');
    } catch (Exception $e) {
        return $fechaSql;
    }
}

$mensajes = [];
$errorListado = '';

if (!$pdo) {
    $errorListado = $errorConexion ?? 'No se pudo conectar con la base de datos para consultar los mensajes.';
} else {
    try {
        /* Ordena por fecha e ID descendentes para priorizar las consultas más recientes */
        $stmt = $pdo->prepare(
            "SELECT id, nombre, correo, asunto, mensaje, creado_en
             FROM mensajes_contacto
             ORDER BY creado_en DESC, id DESC"
        );
        $stmt->execute();
        $mensajes = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Error al consultar mensajes en admin/mensajes.php: ' . $e->getMessage());
        $errorListado = 'Ocurrió un error al obtener los mensajes de contacto.';
    }
}

$totalMensajes = count($mensajes);
$textoContadorInicial = 'Mostrando ' . $totalMensajes . ' ' . ($totalMensajes === 1 ? 'mensaje' : 'mensajes');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mensajes | Administración | Cuidado de tus Mascotas</title>

    <!-- Carga el script para inicializar el tema -->
    <script src="../assets/js/tema-inicial.js?v=<?= filemtime(__DIR__ . '/../assets/js/tema-inicial.js') ?>"></script>

    <!-- Hojas base compartidas seguidas por la hoja exclusiva del módulo y tema.css -->
    <link rel="stylesheet" href="../assets/css/panel.css?v=<?= filemtime(__DIR__ . '/../assets/css/panel.css') ?>">
    <link rel="stylesheet" href="../assets/css/admin.css?v=<?= filemtime(__DIR__ . '/../assets/css/admin.css') ?>">
    <link rel="stylesheet" href="../assets/css/admin-mensajes.css?v=<?= filemtime(__DIR__ . '/../assets/css/admin-mensajes.css') ?>">
    <link rel="stylesheet" href="../assets/css/tema.css?v=<?= filemtime(__DIR__ . '/../assets/css/tema.css') ?>">
    <script src="../assets/js/interfaz.js?v=<?= filemtime(__DIR__ . '/../assets/js/interfaz.js') ?>" defer></script>
    <script src="../assets/js/admin-mensajes.js?v=<?= filemtime(__DIR__ . '/../assets/js/admin-mensajes.js') ?>" defer></script>
</head>

<body class="pagina-panel" data-modulo="mensajes">
    <div class="estructura-panel">

        <aside class="barra-lateral">
            <a href="../index.php" class="marca-panel">
                <span aria-hidden="true">🐾</span>
                <span>Cuidado de<br>tus Mascotas</span>
            </a>

            <p class="etiqueta-menu">ADMINISTRACIÓN</p>

            <nav class="menu-panel" aria-label="Menú del administrador">
                <a href="panel.php">
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

                <a href="mensajes.php" class="activo" aria-current="page">
                    <span aria-hidden="true">✉️</span>
                    Mensajes
                </a>
                <a href="servicios.php">Servicios</a>
                <a href="citas.php">
                    <span aria-hidden="true">📅</span>
                    Citas
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
                    <h1>Mensajes recibidos</h1>
                </div>

                <!-- Acceso directo a la bandeja web de Gmail en pestaña nueva con aislamiento opener -->
                <a
                    href="https://mail.google.com/"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="boton-admin"
                >
                    Abrir Gmail ↗
                </a>
            </header>

            <p class="descripcion-listado">
                Consulta las dudas enviadas desde el formulario de contacto y respóndelas directamente desde Gmail.
            </p>

            <div class="barra-filtros">
                <div class="grupo-filtro">
                    <label for="buscar-mensaje">
                        Buscar por remitente, correo, asunto o mensaje
                    </label>

                    <input
                        type="search"
                        id="buscar-mensaje"
                        placeholder="Escribe un nombre, correo o palabra clave"
                        aria-controls="tabla-mensajes"
                    >
                </div>

                <p id="resultado-mensajes" role="status">
                    <?= htmlspecialchars($textoContadorInicial, ENT_QUOTES, 'UTF-8') ?>
                </p>
            </div>

            <section aria-labelledby="titulo-mensajes">
                <h2 id="titulo-mensajes" class="titulo-listado">
                    Bandeja de contacto
                </h2>

                <div
                    class="tabla-contenedor"
                    role="region"
                    aria-labelledby="titulo-mensajes"
                    tabindex="0"
                >
                    <table id="tabla-mensajes" class="tabla-admin">
                        <caption class="solo-lectores">
                            Mensajes enviados por los visitantes desde el apartado de contacto
                        </caption>

                        <thead>
                            <tr>
                                <th scope="col">Remitente</th>
                                <th scope="col">Correo electrónico</th>
                                <th scope="col">Asunto</th>
                                <th scope="col">Fecha</th>
                                <th scope="col">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($mensajes as $msg): ?>
                                <?php
                                $urlGmail = construirUrlGmail(
                                    (string) $msg['correo'],
                                    (string) $msg['nombre'],
                                    (string) $msg['asunto']
                                );
                                $fechaTexto = formatearFechaMensaje((string) $msg['creado_en']);
                                ?>
                                <tr data-mensaje>
                                    <th scope="row" class="mensaje-nombre">
                                        <?= htmlspecialchars($msg['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                    </th>

                                    <td class="mensaje-correo">
                                        <?= htmlspecialchars($msg['correo'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>

                                    <td class="mensaje-asunto-celda">
                                        <?= htmlspecialchars($msg['asunto'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>

                                    <td class="mensaje-fecha">
                                        <?= htmlspecialchars($fechaTexto, ENT_QUOTES, 'UTF-8') ?>
                                    </td>

                                    <td>
                                        <div class="acciones-tabla">
                                            <!-- Atributos data-* transfieren el registro al modal sin consultas AJAX adicionales -->
                                            <button
                                                type="button"
                                                class="accion-tabla accion-ver-mensaje"
                                                aria-label="Leer mensaje completo de <?= htmlspecialchars($msg['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-nombre="<?= htmlspecialchars($msg['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-correo="<?= htmlspecialchars($msg['correo'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-asunto="<?= htmlspecialchars($msg['asunto'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-mensaje="<?= htmlspecialchars($msg['mensaje'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-gmail="<?= htmlspecialchars($urlGmail, ENT_QUOTES, 'UTF-8') ?>"
                                            >
                                                👁 Leer mensaje
                                            </button>

                                            <!-- rel="noopener noreferrer" evita reverse tabnabbing al abrir Gmail -->
                                            <a
                                                href="<?= htmlspecialchars($urlGmail, ENT_QUOTES, 'UTF-8') ?>"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="accion-tabla accion-responder"
                                                aria-label="Responder en Gmail a <?= htmlspecialchars($msg['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                            >
                                                Responder en Gmail ↗
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <tr id="mensajes-vacio" <?= $totalMensajes > 0 ? 'hidden' : '' ?>>
                                <td colspan="5">
                                    <div class="estado-vacio-ilustrado">
                                        <span class="estado-vacio-icono" aria-hidden="true">✉️</span>
                                        <h3><?= $errorListado !== '' ? 'No se pudieron cargar los mensajes' : 'Sin mensajes para mostrar' ?></h3>
                                        <p>
                                            <?= $errorListado !== ''
                                                ? htmlspecialchars($errorListado, ENT_QUOTES, 'UTF-8')
                                                : 'Aún no hay mensajes registrados o ninguno coincide con tu búsqueda.' ?>
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <!-- Modal accesible de lectura detallada poblado dinámicamente desde data-attributes -->
    <div
        id="modal-mensaje"
        class="modal-overlay"
        role="dialog"
        aria-modal="true"
        aria-labelledby="modal-mensaje-titulo"
        hidden
    >
        <div class="modal-caja">
            <header class="modal-cabecera">
                <div>
                    <p class="etiqueta-seccion">MENSAJE DE CONTACTO</p>
                    <h2 id="modal-mensaje-titulo">Detalle de la consulta</h2>
                </div>

                <button
                    type="button"
                    id="modal-cerrar"
                    class="modal-boton-cerrar"
                    aria-label="Cerrar ventana"
                >
                    ✕
                </button>
            </header>

            <div class="modal-cuerpo">
                <dl class="modal-metadatos">
                    <div class="modal-dato">
                        <dt>Remitente</dt>
                        <dd id="modal-nombre"></dd>
                    </div>

                    <div class="modal-dato">
                        <dt>Correo electrónico</dt>
                        <dd id="modal-correo"></dd>
                    </div>

                    <div class="modal-dato modal-dato-completo">
                        <dt>Asunto</dt>
                        <dd id="modal-asunto"></dd>
                    </div>
                </dl>

                <div class="modal-bloque-mensaje">
                    <span class="modal-etiqueta-mensaje">Mensaje completo</span>
                    <p id="modal-mensaje-texto" class="modal-mensaje-texto"></p>
                </div>
            </div>

            <footer class="modal-pie">
                <a
                    id="modal-enlace-gmail"
                    href="https://mail.google.com/"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="accion-tabla accion-responder"
                >
                    Responder en Gmail ↗
                </a>

                <button type="button" id="modal-cerrar-pie" class="modal-boton-secundario">
                    Cerrar
                </button>
            </footer>
        </div>
    </div>
</body>
</html>
