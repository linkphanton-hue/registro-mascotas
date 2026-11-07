const capaHuellas = document.getElementById("huellas");
const movimientoReducido = window.matchMedia(
    "(prefers-reduced-motion: reduce)"
);

/* Dibujo de una patita. Se reutiliza en cada paso. */
const dibujoHuella = `
    <svg viewBox="0 0 64 72" fill="currentColor"
         xmlns="http://www.w3.org/2000/svg">
        <ellipse cx="12" cy="28" rx="7" ry="10"
                 transform="rotate(-25 12 28)" />
        <ellipse cx="25" cy="15" rx="7" ry="11"
                 transform="rotate(-10 25 15)" />
        <ellipse cx="41" cy="15" rx="7" ry="11"
                 transform="rotate(10 41 15)" />
        <ellipse cx="54" cy="28" rx="7" ry="10"
                 transform="rotate(25 54 28)" />
        <path d="M32 34
                 C24 34 21 43 15 49
                 C6 59 12 69 23 65
                 C29 62 35 62 41 65
                 C52 69 58 59 49 49
                 C43 43 40 34 32 34Z" />
    </svg>
`;

let recorrido = null;

/* Comienza un recorrido desde un punto aleatorio de la pantalla. */
function iniciarRecorrido() {
    const angulo = Math.random() * Math.PI * 2;

    recorrido = {
        x: Math.random() * window.innerWidth,
        y: Math.random() * window.innerHeight,
        angulo: angulo,
        paso: 0,
        total: 10 + Math.floor(Math.random() * 9)
    };
}

/* Crea una pisada alternando el lado izquierdo y el derecho. */
function crearPaso() {
    if (
        !capaHuellas ||
        movimientoReducido.matches ||
        document.hidden
    ) {
        return;
    }

    if (!recorrido || recorrido.paso >= recorrido.total) {
        iniciarRecorrido();
    }

    const direccionX = Math.cos(recorrido.angulo);
    const direccionY = Math.sin(recorrido.angulo);

    const lado = recorrido.paso % 2 === 0 ? -1 : 1;
    const separacion = 15;

    const huella = document.createElement("span");
    huella.className = "huella";
    huella.innerHTML = dibujoHuella;

    /* Desplazamiento lateral para alternar las dos patitas. */
    huella.style.left = (
        recorrido.x - direccionY * separacion * lado
    ) + "px";

    huella.style.top = (
        recorrido.y + direccionX * separacion * lado
    ) + "px";

    /* El dibujo apunta hacia arriba: ajustamos su orientación. */
    const grados = recorrido.angulo * 180 / Math.PI + 90;
    huella.style.setProperty("--angulo", grados + "deg");

    capaHuellas.appendChild(huella);

    /* Elimina la huella al terminar para no acumular elementos. */
    huella.addEventListener("animationend", () => {
        huella.remove();
    }, { once: true });

    recorrido.x += direccionX * 48;
    recorrido.y += direccionY * 48;
    recorrido.paso++;

    /* Reinicia la ruta si sale de la pantalla. */
    if (
        recorrido.x < -40 ||
        recorrido.x > window.innerWidth + 40 ||
        recorrido.y < -40 ||
        recorrido.y > window.innerHeight + 40
    ) {
        recorrido = null;
    }
}

/* Aparece un paso cada 420 milisegundos. */
if (capaHuellas) {
    crearPaso();
    window.setInterval(crearPaso, 420);
}