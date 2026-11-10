/* ============================================================
   interfaz.js
   Modo oscuro con localStorage, notificaciones flotantes (toasts)
   y confirmación en línea sin diálogos nativos.
   ============================================================ */

document.documentElement.classList.add("js-activo");

const CLAVE_TEMA = "tema-preferido";

function obtenerTemaActual() {
    return document.documentElement.getAttribute("data-theme") === "dark"
        ? "dark"
        : "light";
}

function actualizarBotonTema(boton) {
    if (!boton) return;
    const esOscuro = obtenerTemaActual() === "dark";
    boton.setAttribute("aria-pressed", esOscuro ? "true" : "false");
    boton.innerHTML = esOscuro
        ? '<span aria-hidden="true">☀️</span><span>Modo claro</span>'
        : '<span aria-hidden="true">🌙</span><span>Modo oscuro</span>';
}

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

/* Confirmación en dos clics para formularios de baja sin usar confirm() nativo */
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

const parametrosUrl = new URLSearchParams(window.location.search);
if (parametrosUrl.get("eliminado") === "1") {
    mostrarToast("Mascota dada de baja correctamente.", "exito");
    parametrosUrl.delete("eliminado");
    const nuevaQuery = parametrosUrl.toString();
    const nuevaUrl = window.location.pathname + (nuevaQuery ? `?${nuevaQuery}` : "");
    window.history.replaceState({}, "", nuevaUrl);
}
