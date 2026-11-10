const formularioMascota = document.getElementById(
    "formulario-mascota"
);

if (formularioMascota) {
    const nombre = document.getElementById("nombre");
    const especie = document.getElementById("especie");
    const raza = document.getElementById("raza");
    const edadAnios = document.getElementById("edad-anios");
    const edadMeses = document.getElementById("edad-meses");
    const dueno = document.getElementById("dueno");
    const mensaje = document.getElementById("mensaje-mascota");

    function mostrarMensajeMascota(texto, tipo) {
        mensaje.textContent = texto;
        mensaje.className = `mensaje-admin ${tipo}`;
    }

    formularioMascota.addEventListener("input", () => {
        mensaje.textContent = "";
        mensaje.className = "mensaje-admin";
    });

    formularioMascota.addEventListener("submit", (evento) => {
        if (nombre.value.trim().length === 0) {
            evento.preventDefault();
            mostrarMensajeMascota(
                "Escribe el nombre de la mascota; no puede contener solo espacios.",
                "error"
            );
            nombre.focus();
            return;
        }

        if (!especie.value || especie.value === "") {
            evento.preventDefault();
            mostrarMensajeMascota(
                "Selecciona una especie válida para la mascota.",
                "error"
            );
            especie.focus();
            return;
        }

        if (raza && raza.value.trim().length === 0) {
            evento.preventDefault();
            mostrarMensajeMascota(
                "Escribe la raza de la mascota; no puede contener solo espacios.",
                "error"
            );
            raza.focus();
            return;
        }

        const aniosTexto = edadAnios ? edadAnios.value.trim() : "";
        const mesesTexto = edadMeses ? edadMeses.value.trim() : "";
        const aniosValor = Number(aniosTexto);
        const mesesValor = Number(mesesTexto);

        if (
            aniosTexto === "" ||
            !Number.isInteger(aniosValor) ||
            aniosValor < 0 ||
            aniosValor > 100
        ) {
            evento.preventDefault();
            mostrarMensajeMascota(
                "Ingresa los años de la mascota (número entero entre 0 y 100).",
                "error"
            );
            edadAnios.focus();
            return;
        }

        if (
            mesesTexto === "" ||
            !Number.isInteger(mesesValor) ||
            mesesValor < 0 ||
            mesesValor > 11
        ) {
            evento.preventDefault();
            mostrarMensajeMascota(
                "Ingresa los meses de la mascota (número entero entre 0 y 11).",
                "error"
            );
            edadMeses.focus();
            return;
        }

        if (!dueno.value || dueno.value === "") {
            evento.preventDefault();
            mostrarMensajeMascota(
                "Selecciona el dueño responsable de la mascota.",
                "error"
            );
            dueno.focus();
            return;
        }
        /* Si todas las validaciones pasan, se permite el envío POST al servidor PHP. */
    });
}
