/* ============================================================
   interfaz.js
   Modo oscuro con localStorage, notificaciones flotantes (toasts)
   y confirmación en línea sin diálogos nativos.
   ============================================================ */

document.documentElement.classList.add("js-activo");

const CLAVE_TEMA = "tema-preferido";

/**
 * Consulta el atributo data-theme del elemento raíz <html> para identificar
 * si la interfaz se encuentra en modo oscuro ('dark') o modo claro ('light').
 *
 * @returns {"dark"|"light"} Identificador del tema activo.
 */
function obtenerTemaActual() {
    return document.documentElement.getAttribute("data-theme") === "dark"
        ? "dark"
        : "light";
}

/**
 * Sincroniza el estado de accesibilidad (aria-pressed) y el icono/texto
 * del botón conmutador para reflejar la acción disponible según el tema actual.
 *
 * @param {HTMLButtonElement|null} boton Referencia al botón de cambio de tema.
 * @returns {void}
 */
function actualizarBotonTema(boton) {
    if (!boton) return;
    const esOscuro = obtenerTemaActual() === "dark";
    boton.setAttribute("aria-pressed", esOscuro ? "true" : "false");
    boton.innerHTML = esOscuro
        ? '<span aria-hidden="true">☀️</span><span>Modo claro</span>'
        : '<span aria-hidden="true">🌙</span><span>Modo oscuro</span>';
}

/**
 * Activa o desactiva el atributo [data-theme="dark"] en <html>, guarda la
 * preferencia del usuario en localStorage y actualiza todos los botones de tema.
 *
 * @param {"dark"|"light"} tema Esquema de color que se desea aplicar.
 * @returns {void}
 */
function aplicarTema(tema) {
    if (tema === "dark") {
        document.documentElement.setAttribute("data-theme", "dark");
    } else {
        document.documentElement.removeAttribute("data-theme");
    }

    try {
        localStorage.setItem(CLAVE_TEMA, tema);
    } catch (e) {
        /* localStorage puede no estar disponible en navegación restringida */
    }

    document.querySelectorAll(".boton-tema").forEach(actualizarBotonTema);
}

/**
 * Recupera la preferencia guardada en localStorage al cargar el documento e
 * inyecta dinámicamente el botón de cambio de tema en la barra lateral o menú superior.
 *
 * @returns {void}
 */
function inicializarBotonTema() {
    const temaGuardado = (() => {
        try {
            return localStorage.getItem(CLAVE_TEMA);
        } catch (e) {
            return null;
        }
    })();

    if (temaGuardado === "dark" || temaGuardado === "light") {
        aplicarTema(temaGuardado);
    }

    let contenedor =
        document.querySelector(".barra-lateral") ||
        document.querySelector(".navegacion .menu") ||
        document.querySelector(".pagina-login");

    if (!contenedor || document.querySelector(".boton-tema")) return;

    const boton = document.createElement("button");
    boton.type = "button";
    boton.className = "boton-tema";
    boton.setAttribute("aria-label", "Cambiar entre modo claro y modo oscuro");
    actualizarBotonTema(boton);

    boton.addEventListener("click", () => {
        const siguiente = obtenerTemaActual() === "dark" ? "light" : "dark";
        aplicarTema(siguiente);
    });

    const enlaceVolver = contenedor.querySelector(".volver-inicio");
    if (enlaceVolver) {
        contenedor.insertBefore(boton, enlaceVolver);
    } else {
        contenedor.appendChild(boton);
    }
}

/* ============================================================
   SISTEMA DE TOASTS
   ============================================================ */

let contenedorToasts = null;

/**
 * Crea bajo demanda un único contenedor fijo (.contenedor-toasts) en el <body>
 * con atributos ARIA live para anunciar las notificaciones a lectores de pantalla.
 *
 * @returns {HTMLDivElement} Contenedor DOM donde se apilan las notificaciones Toast.
 */
function asegurarContenedorToasts() {
    if (!contenedorToasts) {
        contenedorToasts = document.createElement("div");
        contenedorToasts.className = "contenedor-toasts";
        contenedorToasts.setAttribute("aria-live", "polite");
        contenedorToasts.setAttribute("aria-atomic", "true");
        document.body.appendChild(contenedorToasts);
    }
    return contenedorToasts;
}

/**
 * Construye y muestra una notificación flotante (Toast) en la esquina inferior
 * derecha, aplicando una transición de entrada con requestAnimationFrame y
 * eliminándola del DOM tras 3000 ms.
 *
 * @param {string} texto Mensaje informativo o de validación a mostrar.
 * @param {"exito"|"error"} [tipo="exito"] Define el color del borde y el rol ARIA ('alert' o 'status').
 * @returns {void}
 */
function mostrarToast(texto, tipo = "exito") {
    const limpio = (texto || "").trim();
    if (!limpio) return;

    const esError = tipo === "error";
    const caja = asegurarContenedorToasts();

    const toast = document.createElement("div");
    toast.className = `toast ${esError ? "toast-error" : "toast-exito"}`;
    toast.setAttribute("role", esError ? "alert" : "status");

    const icono = document.createElement("span");
    icono.setAttribute("aria-hidden", "true");
    icono.textContent = esError ? "⚠️" : "✓";

    const cuerpo = document.createElement("span");
    cuerpo.textContent = limpio;

    toast.append(icono, cuerpo);
    caja.appendChild(toast);

    requestAnimationFrame(() => {
        toast.classList.add("visible");
    });

    window.setTimeout(() => {
        toast.classList.remove("visible");
        window.setTimeout(() => toast.remove(), 300);
    }, 3000);
}

window.mostrarToast = mostrarToast;

/**
 * Detecta mensajes emitidos desde PHP (vía data-toast-mensaje en <body> o en
 * párrafos .mensaje-admin / .mensaje-formulario) y registra un MutationObserver
 * para lanzar Toasts automáticamente cuando las validaciones JS escriben un aviso.
 *
 * @returns {void}
 */
function observarMensajesExistentes() {
    if (document.body && document.body.dataset.toastMensaje) {
        const tipoBody = document.body.dataset.toastTipo === "error" ? "error" : "exito";
        mostrarToast(document.body.dataset.toastMensaje, tipoBody);
    }

    const selectores = ".mensaje-admin, .mensaje-formulario, #aviso-edicion[data-toast]";
    document.querySelectorAll(selectores).forEach((elemento) => {
        const dispararSegunElemento = () => {
            const texto = elemento.textContent.trim();
            if (!texto) return;
            const tipo = elemento.classList.contains("error") ||
                elemento.dataset.toast === "error"
                ? "error"
                : "exito";
            mostrarToast(texto, tipo);
        };

        /* Si PHP ya renderizó un mensaje al cargar la página, lo mostramos */
        if (elemento.textContent.trim() !== "") {
            dispararSegunElemento();
        }

        /* Si un script de validación escribe en el elemento, también dispara toast */
        const observador = new MutationObserver(() => {
            if (elemento.textContent.trim() !== "") {
                dispararSegunElemento();
            }
        });

        observador.observe(elemento, {
            childList: true,
            characterData: true,
            subtree: true,
        });
    });
}

/**
 * Implementa una confirmación en dos pasos sobre los botones con [data-confirmar]:
 * el primer clic detiene el envío con preventDefault() y pide confirmación mediante
 * un Toast; el segundo clic dentro de los 3 segundos autoriza el envío POST.
 *
 * @returns {void}
 */
function inicializarConfirmacionesEnLinea() {
    document.querySelectorAll("button[data-confirmar]").forEach((boton) => {
        const textoOriginal = boton.textContent.trim();
        let temporizador = null;

        boton.addEventListener("click", (evento) => {
            if (boton.dataset.confirmado === "si") {
                boton.dataset.confirmado = "";
                return;
            }

            evento.preventDefault();
            boton.dataset.confirmado = "si";
            boton.classList.add("confirmando");
            boton.textContent = "Confirmar";
            mostrarToast(boton.dataset.confirmar, "error");

            window.clearTimeout(temporizador);
            temporizador = window.setTimeout(() => {
                boton.dataset.confirmado = "";
                boton.classList.remove("confirmando");
                boton.textContent = textoOriginal;
            }, 3000);
        });
    });
}

inicializarBotonTema();
observarMensajesExistentes();
inicializarConfirmacionesEnLinea();

/*
 * Si la URL contiene ?eliminado=1 tras una baja lógica en admin/mascotas.php,
 * muestra el Toast de éxito y limpia el parámetro con history.replaceState()
 * para que no vuelva a aparecer al refrescar la página.
 */
const parametrosUrl = new URLSearchParams(window.location.search);
if (parametrosUrl.get("eliminado") === "1") {
    mostrarToast("Mascota dada de baja correctamente.", "exito");
    parametrosUrl.delete("eliminado");
    const nuevaQuery = parametrosUrl.toString();
    const nuevaUrl = window.location.pathname + (nuevaQuery ? `?${nuevaQuery}` : "");
    window.history.replaceState({}, "", nuevaUrl);
}
