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

    function mostrarMensajeCliente(texto, tipo) {
        mensaje.textContent = texto;
        mensaje.className = `mensaje-admin ${tipo}`;
    }

    formularioCliente.addEventListener("input", () => {
        mensaje.textContent = "";
        mensaje.className = "mensaje-admin";
    });

    formularioCliente.addEventListener("submit", (evento) => {
        /* No envía datos mientras no exista el procesamiento PHP. */
        evento.preventDefault();

        if (nombre.value.trim().length === 0) {
            mostrarMensajeCliente(
                "Escribe el nombre del cliente; no puede contener solo espacios.",
                "error"
            );

            nombre.focus();
            return;
        }

        if (contrasena.value !== confirmacion.value) {
            mostrarMensajeCliente(
                "Las contraseñas no coinciden.",
                "error"
            );

            confirmacion.focus();
            return;
        }

        mostrarMensajeCliente(
            "Validación completada. El cliente todavía no se ha guardado.",
            "informacion"
        );

        /* Limpia las contraseñas después de la demostración. */
        contrasena.value = "";
        confirmacion.value = "";
    });
}