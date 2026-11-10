const formularioCambio = document.getElementById("formulario-cambio-contrasena");

if (formularioCambio) {
    const actual = document.getElementById("contrasena-actual");
    const nueva = document.getElementById("nueva-contrasena");
    const confirmar = document.getElementById("confirmar-contrasena");
    const mostrar = document.getElementById("mostrar-contrasenas");
    const mensaje = document.getElementById("mensaje-contrasena");

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
