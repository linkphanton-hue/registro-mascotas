/**
 * Control interactivo y validación en el navegador para cambiar-contrasena.php.
 *
 * Permite alternar la visibilidad de los campos de contraseña y valida que la
 * clave nueva coincida con su confirmación y sea distinta de la clave actual.
 */

const formularioCambio = document.getElementById("formulario-cambio-contrasena");

if (formularioCambio) {
    const actual = document.getElementById("contrasena-actual");
    const nueva = document.getElementById("nueva-contrasena");
    const confirmar = document.getElementById("confirmar-contrasena");
    const mostrar = document.getElementById("mostrar-contrasenas");
    const mensaje = document.getElementById("mensaje-contrasena");

    /**
     * Escribe el mensaje de validación en #mensaje-contrasena y actualiza su
     * clase CSS para activar la notificación Toast correspondiente.
     *
     * @param {string} texto Mensaje descriptivo del estado o error.
     * @param {"error"|"informacion"} tipo Clase semántica del aviso.
     * @returns {void}
     */
    function informarCambio(texto, tipo) {
        mensaje.textContent = texto;
        mensaje.className = `mensaje-formulario ${tipo}`;
    }

    mostrar.addEventListener("change", () => {
        [actual, nueva, confirmar].forEach((campo) => {
            campo.type = mostrar.checked ? "text" : "password";
        });
    });

    formularioCambio.addEventListener("input", () => {
        mensaje.textContent = "";
        mensaje.className = "mensaje-formulario";
    });

    /* Verifica las reglas en el cliente antes de permitir que el formulario se envíe por POST */
    formularioCambio.addEventListener("submit", (evento) => {
        if (!formularioCambio.reportValidity()) {
            evento.preventDefault();
            return;
        }
        if (nueva.value !== confirmar.value) {
            evento.preventDefault();
            informarCambio("La nueva contraseña y su confirmación no coinciden.", "error");
            confirmar.focus();
            return;
        }
        if (nueva.value === actual.value) {
            evento.preventDefault();
            informarCambio("La nueva contraseña debe ser diferente de la actual.", "error");
            nueva.focus();
            return;
        }
        /* Si las validaciones en navegador pasan, se envía al servidor PHP. */
    });
}
