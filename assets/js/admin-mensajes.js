/**
 * Gestión interactiva de la bandeja administrativa de mensajes de contacto.
 *
 * Implementa filtrado instantáneo insensible a tildes y lectura en ventana modal
 * alimentada dinámicamente mediante atributos HTML5 data-*.
 */

const buscarMensaje = document.getElementById("buscar-mensaje");

const filasMensajes = document.querySelectorAll(
    "#tabla-mensajes tbody tr[data-mensaje]"
);

const resultadoMensajes = document.getElementById("resultado-mensajes");
const mensajesVacio = document.getElementById("mensajes-vacio");

const tablaMensajes = document.getElementById("tabla-mensajes");
const modalMensaje = document.getElementById("modal-mensaje");
const modalNombre = document.getElementById("modal-nombre");
const modalCorreo = document.getElementById("modal-correo");
const modalAsunto = document.getElementById("modal-asunto");
const modalMensajeTexto = document.getElementById("modal-mensaje-texto");
const modalEnlaceGmail = document.getElementById("modal-enlace-gmail");
const botonCerrarModal = document.getElementById("modal-cerrar");
const botonCerrarPie = document.getElementById("modal-cerrar-pie");

let ultimoBotonActivo = null;

/**
 * Compara el término buscado contra remitente, correo, asunto y el cuerpo oculto
 * en `data-mensaje` del botón lector, alternando `fila.hidden` sin alterar el DOM.
 *
 * @returns {void}
 */
function filtrarMensajes() {
    const busqueda = normalizarTexto(buscarMensaje.value);
    let cantidad = 0;

    filasMensajes.forEach((fila) => {
        const nombre = fila.querySelector(".mensaje-nombre")?.textContent ?? "";
        const correo = fila.querySelector(".mensaje-correo")?.textContent ?? "";
        const asunto = fila.querySelector(".mensaje-asunto-celda")?.textContent ?? "";
        const cuerpo = fila.querySelector(".accion-ver-mensaje")?.dataset.mensaje ?? "";

        const textoMensaje = normalizarTexto(
            `${nombre} ${correo} ${asunto} ${cuerpo}`
        );

        const coincide = textoMensaje.includes(busqueda);
        fila.hidden = !coincide;

        if (coincide) {
            cantidad++;
        }
    });

    const palabra = cantidad === 1 ? "mensaje" : "mensajes";
    resultadoMensajes.textContent = `Mostrando ${cantidad} ${palabra}`;
    mensajesVacio.hidden = cantidad > 0;
}

/**
 * Extrae los `data-attributes` (`dataset.nombre`, `dataset.correo`, `dataset.asunto`,
 * `dataset.mensaje` y `dataset.gmail`) del botón pulsado, inyecta los valores mediante
 * `textContent` para prevenir XSS en el DOM y despliega la ventana modal accesible.
 *
 * @param {HTMLButtonElement} boton Botón disparador que almacena la carga del registro.
 * @returns {void}
 */
function abrirModalMensaje(boton) {
    if (!modalMensaje) return;

    ultimoBotonActivo = boton;

    modalNombre.textContent = boton.dataset.nombre ?? "";
    modalCorreo.textContent = boton.dataset.correo ?? "";
    modalAsunto.textContent = boton.dataset.asunto ?? "";
    modalMensajeTexto.textContent = boton.dataset.mensaje ?? "";

    if (modalEnlaceGmail && boton.dataset.gmail) {
        modalEnlaceGmail.href = boton.dataset.gmail;
        modalEnlaceGmail.setAttribute(
            "aria-label",
            `Responder en Gmail a ${boton.dataset.nombre ?? ""}`
        );
    }

    modalMensaje.hidden = false;
    document.body.classList.add("modal-abierto");
    botonCerrarModal?.focus();
}

/**
 * Oculta la ventana modal, libera el bloqueo de desplazamiento del documento y
 * devuelve el foco al botón de la fila que originó la lectura (accesibilidad WAI-ARIA).
 *
 * @returns {void}
 */
function cerrarModalMensaje() {
    if (!modalMensaje || modalMensaje.hidden) return;

    modalMensaje.hidden = true;
    document.body.classList.remove("modal-abierto");

    if (ultimoBotonActivo) {
        ultimoBotonActivo.focus();
        ultimoBotonActivo = null;
    }
}

/* Inicializa el filtro instantáneo cuando los controles están presentes */
if (buscarMensaje && resultadoMensajes && mensajesVacio) {
    buscarMensaje.addEventListener("input", filtrarMensajes);
    filtrarMensajes();
}

/* Delegación de eventos sobre la tabla para capturar clics en cualquier botón .accion-ver-mensaje */
if (tablaMensajes && modalMensaje) {
    tablaMensajes.addEventListener("click", (evento) => {
        const boton = evento.target.closest(".accion-ver-mensaje");
        if (boton) {
            abrirModalMensaje(boton);
        }
    });

    botonCerrarModal?.addEventListener("click", cerrarModalMensaje);
    botonCerrarPie?.addEventListener("click", cerrarModalMensaje);

    /* Cierra el modal únicamente cuando el clic impacta el fondo oscuro (.modal-overlay) */
    modalMensaje.addEventListener("click", (evento) => {
        if (evento.target === modalMensaje) {
            cerrarModalMensaje();
        }
    });

    document.addEventListener("keydown", (evento) => {
        if (evento.key === "Escape" && !modalMensaje.hidden) {
            cerrarModalMensaje();
        }
    });
}
