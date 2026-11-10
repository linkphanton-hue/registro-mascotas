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
