/**
 * Filtro interactivo por especie para la vista de tarjetas del cliente (mis-mascotas.php).
 *
 * Muestra u oculta las tarjetas (.tarjeta-mascota) según el valor de su atributo
 * data-especie y actualiza el contador de coincidencias.
 */

const filtroEspecie = document.getElementById("filtro-especie");
const tarjetasMascotas = document.querySelectorAll(
    ".tarjeta-mascota[data-especie]"
);
const resultadoFiltro = document.getElementById("resultado-filtro");
const listadoVacio = document.getElementById("listado-vacio");

/**
 * Compara la especie seleccionada en el <select> con el atributo data-especie
 * de cada tarjeta, actualiza el texto de estado y muestra #listado-vacio si no hay resultados.
 *
 * @returns {void}
 */
/* Muestra únicamente las tarjetas de la especie seleccionada. */
function filtrarMascotas() {
    const especieSeleccionada = filtroEspecie.value;
    let cantidadVisible = 0;

    tarjetasMascotas.forEach((tarjeta) => {
        const coincide =
            especieSeleccionada === "todas" ||
            tarjeta.dataset.especie === especieSeleccionada;

        tarjeta.hidden = !coincide;

        if (coincide) {
            cantidadVisible++;
        }
    });

    const nombreCantidad =
        cantidadVisible === 1 ? "mascota" : "mascotas";

    resultadoFiltro.textContent =
        `Mostrando ${cantidadVisible} ${nombreCantidad}`;

    listadoVacio.hidden = cantidadVisible > 0;
}

if (filtroEspecie && resultadoFiltro && listadoVacio) {
    filtroEspecie.addEventListener("change", filtrarMascotas);
    filtrarMascotas();
}