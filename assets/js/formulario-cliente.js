/**
 * Validación en el navegador para el formulario administrativo de clientes.
 *
 * Comprueba que el nombre completo no contenga únicamente espacios en blanco
 * y que la contraseña coincida con su confirmación antes de enviarse por POST.
 */

const formularioCliente = document.getElementById(
    "formulario-cliente"
);

if (formularioCliente) {
    const nombre = document.getElementById("nombre");
    const contrasena = document.getElementById("contrasena");
    const confirmacion = document.getElementById(
        "confirmar-contrasena"
    );
    const mensaje = document.getElementById("mensaje-cliente");

    /**
     * Muestra un mensaje de estado o error en #mensaje-cliente y actualiza su
     * clase CSS para activar la notificación Toast correspondiente.
     *
     * @param {string} texto Descripción del error o confirmación.
     * @param {"error"|"informacion"} tipo Categoría visual del aviso.
     * @returns {void}
     */
    function mostrarMensajeCliente(texto, tipo) {
        mensaje.textContent = texto;
        mensaje.className = `mensaje-admin ${tipo}`;
    }

    formularioCliente.addEventListener("input", () => {
        mensaje.textContent = "";
        mensaje.className = "mensaje-admin";
    });

    /* Detiene el envío con preventDefault() únicamente si falla alguna validación */
    formularioCliente.addEventListener("submit", (evento) => {
        if (nombre.value.trim().length === 0) {
            evento.preventDefault();
            mostrarMensajeCliente(
                "Escribe el nombre del cliente; no puede contener solo espacios.",
                "error"
            );

            nombre.focus();
            return;
        }

        if (contrasena.value !== confirmacion.value) {
            evento.preventDefault();
            mostrarMensajeCliente(
                "Las contraseñas no coinciden.",
                "error"
            );

            confirmacion.focus();
            return;
        }

        /* Si todas las validaciones pasan, se permite el envío POST al servidor PHP. */
    });
}
