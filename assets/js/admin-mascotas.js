/**
 * Filtro interactivo por especie para la tabla administrativa de mascotas.
 *
 * Evalúa el atributo data-especie de cada fila renderizada por PHP para mostrar
 * u ocultar registros en tiempo real sin recargar la página.
 */

const filtroAdmin = document.getElementById("filtro-especie");
const filasMascotas = document.querySelectorAll(
    ".tabla-admin tbody tr[data-especie]"
);

const resultadoAdmin = document.getElementById("resultado-filtro");
const filaVacia = document.getElementById("listado-vacio");

/**
 * Recorre las filas de la tabla, oculta aquellas que no coinciden con la especie
 * seleccionada, actualiza el contador superior y muestra el bloque vacío si el
 * resultado es 0.
 *
 * @returns {void}
 */
/* Filtra las filas sin modificar ni eliminar sus datos. */
function filtrarMascotasAdmin() {
    const especie = filtroAdmin.value;
    let cantidad = 0;

    filasMascotas.forEach((fila) => {
        const coincide =
            especie === "todas" ||
            fila.dataset.especie === especie;

        fila.hidden = !coincide;

        if (coincide) {
            cantidad++;
        }
    });

    const palabra = cantidad === 1 ? "mascota" : "mascotas";

    resultadoAdmin.textContent =
        `Mostrando ${cantidad} ${palabra}`;

    filaVacia.hidden = cantidad > 0;
}

if (filtroAdmin && resultadoAdmin && filaVacia) {
    filtroAdmin.addEventListener("change", filtrarMascotasAdmin);
    filtrarMascotasAdmin();
}