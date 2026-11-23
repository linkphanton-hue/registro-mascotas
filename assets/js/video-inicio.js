const videoInicio = document.getElementById("video-inicio");

if (videoInicio && "IntersectionObserver" in window) {
    const movimientoReducidoVideo = window.matchMedia("(prefers-reduced-motion: reduce)");
    const umbralVisible = 0.55;
    let visible = false;
    let reproducirAlVolver = true;
    let pausaPorVisibilidad = false;

    function actualizarReproduccion() {
        if (!visible || document.hidden) {
            if (!videoInicio.paused) {
                pausaPorVisibilidad = true;
                videoInicio.pause();
            }
            return;
        }

        if (reproducirAlVolver && videoInicio.paused && !videoInicio.ended
            && videoInicio.muted && !movimientoReducidoVideo.matches) {
            videoInicio.play().catch(() => {
                // Los controles nativos siguen disponibles si el navegador bloquea el inicio.
            });
        }
    }

    videoInicio.addEventListener("play", () => {
        reproducirAlVolver = true;
        actualizarReproduccion();
    });

    videoInicio.addEventListener("pause", () => {
        reproducirAlVolver = pausaPorVisibilidad;
        pausaPorVisibilidad = false;
    });

    const observadorVideo = new IntersectionObserver(([entrada]) => {
        visible = entrada.intersectionRatio >= umbralVisible;
        actualizarReproduccion();
    }, { threshold: umbralVisible });

    observadorVideo.observe(videoInicio);
    document.addEventListener("visibilitychange", actualizarReproduccion);
    movimientoReducidoVideo.addEventListener("change", actualizarReproduccion);
}
