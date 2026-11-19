/**
 * Búsqueda instantánea por nombre o correo en la tabla administrativa de clientes.
 *
 * Permite localizar clientes sin importar mayúsculas, minúsculas o tildes.
 */

const buscarCliente = document.getElementById("buscar-cliente");

const filasClientes = document.querySelectorAll(
    "#tabla-clientes tbody tr[data-cliente]"
);

const resultadoClientes = document.getElementById(
    "resultado-clientes"
);

const clientesVacio = document.getElementById("clientes-vacio");

/**
 * Normaliza un texto descomponiendo los caracteres acentuados en su forma
 * canónica Unicode (NFD), eliminando las marcas diacríticas (rango U+0300–U+036F)
 * y convirtiendo el resultado a minúsculas.
 *
 * @param {string} texto Cadena original con posibles tildes o mayúsculas.
 * @returns {string} Cadena limpia lista para comparación insensible a acentos.
 */
/*
    Convierte el texto a minúsculas y elimina acentos.
    Así, escribir "perez" también encuentra "Pérez".
*/
function normalizarTexto(texto) {
    return texto
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .toLowerCase()
        .trim();
}

/**
 * Compara el término buscado contra el nombre y correo normalizados de cada fila,
 * alterna la visibilidad (fila.hidden) y actualiza el contador y el estado vacío.
 *
 * @returns {void}
 */
/* Busca únicamente en el nombre y el correo de cada cliente. */
function filtrarClientes() {
    const busqueda = normalizarTexto(buscarCliente.value);
    let cantidad = 0;

    filasClientes.forEach((fila) => {
        const nombre =
            fila.querySelector(".cliente-nombre")?.textContent ?? "";

        const correo =
            fila.querySelector(".cliente-correo")?.textContent ?? "";

        const textoCliente = normalizarTexto(
            `${nombre} ${correo}`
        );

        const coincide = textoCliente.includes(busqueda);

        fila.hidden = !coincide;

        if (coincide) {
            cantidad++;
        }
    });

    const palabra = cantidad === 1 ? "cliente" : "clientes";

    resultadoClientes.textContent =
        `Mostrando ${cantidad} ${palabra}`;

    clientesVacio.hidden = cantidad > 0;
}

if (buscarCliente && resultadoClientes && clientesVacio) {
    buscarCliente.addEventListener("input", filtrarClientes);
    filtrarClientes();
}
