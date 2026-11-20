/**
 * Aplica el tema guardado antes de cargar los estilos para evitar destellos.
 * Se carga en el head sin defer porque solo necesita el elemento html.
 */
try {
    if (localStorage.getItem('tema-preferido') === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
    }
} catch (e) {
    // El navegador puede bloquear localStorage.
}
