/**
 * Gestión interactiva del módulo de citas: consulta asíncrona de horarios en formato 24h,
 * filtrado instantáneo insensible a tildes y ventana modal de edición/eliminación mediante data-attributes.
 */

/**
 * Conecta un formulario con [data-agenda-citas] al endpoint de disponibilidad de turnos
 * y expone `formulario.recargarHorarios()` para invocación al abrir la ventana modal.
 *
 * @param {HTMLFormElement} formulario Formulario que contiene [data-fecha-cita] y [data-hora-cita].
 * @returns {void}
 */
function inicializarFormularioAgenda(formulario) {
    const fecha = formulario.querySelector("[data-fecha-cita]");
    const horario = formulario.querySelector("[data-hora-cita]");
    const mensaje = formulario.querySelector("[data-estado-horarios]");
    let consulta = null;

    if (!fecha || !horario || !mensaje) return;

    async function actualizarHorarios() {
        if (consulta) consulta.abort();
        const control = new AbortController();
        consulta = control;
        const seleccionado = horario.dataset.horaSeleccionada || horario.value || "";
        delete horario.dataset.horaSeleccionada;
        horario.replaceChildren(new Option("Selecciona un horario", ""));
        horario.disabled = true;
        fecha.setCustomValidity("");
        mensaje.textContent = "";

        if (!fecha.value) {
            mensaje.textContent = "Selecciona una fecha para ver los horarios disponibles (08:00 a 16:00).";
            return;
        }

        mensaje.textContent = "Consultando disponibilidad…";
        try {
            const url = new URL(formulario.dataset.horariosUrl, window.location.href);
            url.searchParams.set("fecha", fecha.value);
            if (formulario.dataset.excluirCita) {
                url.searchParams.set("excluir_cita", formulario.dataset.excluirCita);
            }
            const respuesta = await fetch(url, { signal: control.signal, cache: "no-store" });
            const datos = await respuesta.json();
            if (control.signal.aborted) return;

            if (!respuesta.ok) {
                const error = datos.mensaje || "No se pudo consultar la agenda.";
                mensaje.textContent = error;
                if (respuesta.status === 422) fecha.setCustomValidity(error);
                return;
            }

            let disponibles = 0;
            for (const turno of datos.turnos) {
                const opcion = document.createElement("option");
                opcion.value = turno.hora;
                opcion.textContent = turno.etiqueta + (turno.disponible ? "" : " — Ocupado");
                opcion.disabled = !turno.disponible;
                opcion.selected = turno.disponible && turno.hora === seleccionado;
                horario.appendChild(opcion);
                if (turno.disponible) disponibles++;
            }

            horario.disabled = disponibles === 0;
            mensaje.textContent = disponibles > 0
                ? "Selecciona un turno disponible de una hora (formato 24h)."
                : "No quedan turnos disponibles para esa fecha. Elige otro día.";
        } catch (error) {
            if (!control.signal.aborted && error.name !== "AbortError") {
                mensaje.textContent = "No se pudo consultar la agenda. Inténtalo de nuevo.";
            }
        }
    }

    fecha.addEventListener("change", actualizarHorarios);
    formulario.recargarHorarios = actualizarHorarios;
    actualizarHorarios();
}

document.querySelectorAll("form[data-agenda-citas]").forEach(inicializarFormularioAgenda);

/* ============================================================
   FILTRO INSTANTÁNEO DE LA TABLA DE CITAS
   ============================================================ */

const buscarCita = document.getElementById("buscar-cita");
const filasCitas = document.querySelectorAll("#tabla-citas tbody tr[data-cita]");
const resultadoCitas = document.getElementById("resultado-citas");
const citasVacio = document.getElementById("citas-vacio");

/**
 * Normaliza una cadena con descomposición canónica Unicode NFD eliminando marcas
 * diacríticas (U+0300–U+036F) para búsquedas insensibles a tildes y mayúsculas.
 *
 * @param {string} texto Cadena original a normalizar.
 * @returns {string} Cadena en minúsculas y sin acentos.
 */
function normalizarTexto(texto) {
    return (texto || "")
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .toLowerCase()
        .trim();
}

/**
 * Filtra las filas de `#tabla-citas` comparando mascota, cliente, servicio, horario,
 * estado y motivo almacenado en `data-motivo`, actualizando el contador y estado vacío.
 *
 * @returns {void}
 */
function filtrarCitas() {
    const busqueda = normalizarTexto(buscarCita.value);
    let cantidad = 0;

    filasCitas.forEach((fila) => {
        const boton = fila.querySelector(".accion-ver-cita");
        const textoFila = normalizarTexto(
            `${fila.textContent} ${boton?.dataset.motivo ?? ""} ${boton?.dataset.correo ?? ""}`
        );

        const coincide = textoFila.includes(busqueda);
        fila.hidden = !coincide;

        if (coincide) {
            cantidad++;
        }
    });

    const palabra = cantidad === 1 ? "cita" : "citas";
    resultadoCitas.textContent = `Mostrando ${cantidad} ${palabra}`;
    citasVacio.hidden = cantidad > 0;
}

if (buscarCita && resultadoCitas && citasVacio) {
    buscarCita.addEventListener("input", filtrarCitas);
    filtrarCitas();
}

/* ============================================================
   VENTANA MODAL DINÁMICA DE CITAS (ADMIN Y CLIENTE)
   ============================================================ */

const tablaCitas = document.getElementById("tabla-citas");
const modalCita = document.getElementById("modal-cita");
const botonCerrarModal = document.getElementById("modal-cita-cerrar");
const botonCerrarPie = document.getElementById("modal-cita-cerrar-pie");

const modalMascota = document.getElementById("modal-cita-mascota");
const modalEstado = document.getElementById("modal-cita-estado");
const modalCliente = document.getElementById("modal-cita-cliente");
const modalCorreo = document.getElementById("modal-cita-correo");
const modalServicio = document.getElementById("modal-cita-servicio");
const modalHorarioAprobado = document.getElementById("modal-cita-horario-aprobado");
const modalDisponibilidad = document.getElementById("modal-cita-disponibilidad");
const modalMotivo = document.getElementById("modal-cita-motivo");
const modalBloqueCambio = document.getElementById("modal-bloque-cambio");
const modalMotivoCambio = document.getElementById("modal-cita-motivo-cambio");
const modalEstadoCambio = document.getElementById("modal-cita-estado-cambio");

/* Controles exclusivos del modal de Administrador */
const formModalAdmin = document.getElementById("form-modal-admin");
const inputAdminCitaId = document.getElementById("modal-admin-cita-id");
const inputAdminVersion = document.getElementById("modal-admin-version");
const subtituloAdminProgramacion = document.getElementById("modal-admin-subtitulo");
const selectAdminServicio = document.getElementById("modal-admin-servicio");
const inputAdminFecha = document.getElementById("modal-admin-fecha");
const selectAdminHora = document.getElementById("modal-admin-hora");
const inputAdminMotivo = document.getElementById("modal-admin-motivo");
const btnConfirmar = document.getElementById("btn-modal-confirmar");
const btnEditar = document.getElementById("btn-modal-editar");
const btnMantener = document.getElementById("btn-modal-mantener");
const btnRechazar = document.getElementById("btn-modal-rechazar");
const btnCancelar = document.getElementById("btn-modal-cancelar");

/* Controles exclusivos del modal de Cliente */
const avisoEstadoCliente = document.getElementById("modal-cliente-aviso-estado");
const formModalCliente = document.getElementById("form-modal-cliente");
const inputClienteCitaId = document.getElementById("modal-cliente-cita-id");
const inputClienteVersion = document.getElementById("modal-cliente-version");
const selectClienteMascota = document.getElementById("modal-cliente-mascota");
const selectClienteServicio = document.getElementById("modal-cliente-servicio");
const inputClienteFecha = document.getElementById("modal-cliente-fecha");
const selectClienteHora = document.getElementById("modal-cliente-hora");
const inputClienteMotivoConsulta = document.getElementById("modal-cliente-motivo-consulta");
const inputClienteMotivo = document.getElementById("modal-cliente-motivo");

let ultimoBotonCitaActivo = null;

/**
 * Selecciona una opción en un `<select>` por su valor; si no existe aún en la lista
 * (por ejemplo un servicio desactivado), la inserta temporalmente para no perder el dato.
 *
 * @param {HTMLSelectElement|null} selector Elemento select a sincronizar.
 * @param {string} valor Valor que debe quedar seleccionado.
 * @returns {void}
 */
function seleccionarValorSelect(selector, valor) {
    if (!selector || !valor) return;
    const existe = Array.from(selector.options).some((op) => op.value === valor);
    if (!existe) {
        selector.appendChild(new Option(valor, valor, true, true));
    } else {
        selector.value = valor;
    }
}

/**
 * Devuelve una explicación clara para el cliente según el estado actual de su solicitud.
 *
 * @param {string} estado Estado actual de la cita.
 * @returns {string} Mensaje explicativo para mostrar en el modal del cliente.
 */
function describirEstadoParaCliente(estado) {
    switch (estado) {
        case "Confirmada":
            return "Tu cita está confirmada en el horario indicado. Si necesitas cambiar algún dato o reprogramarla, puedes editarla abajo o eliminarla.";
        case "Confirmada (Editada)":
            return "El administrador actualizó y confirmó el horario de esta cita. También puedes editarla o eliminarla de tu lista cuando lo necesites.";
        case "Cambio solicitado":
            return "El administrador está revisando los cambios solicitados. Puedes volver a ajustar cualquier dato o eliminar la cita si ya no asistirás.";
        case "Rechazada":
            return "Esta solicitud fue rechazada. Puedes modificar la fecha/horario abajo para reenviarla a revisión o eliminarla de tu lista.";
        case "Cancelada":
            return "Esta cita fue cancelada. Puedes reprogramarla editando los campos de abajo o eliminarla de tu lista.";
        case "Pendiente":
        default:
            return "Tu solicitud está pendiente de confirmación. Puedes editar cualquier dato abajo o eliminarla si ya no la necesitas.";
    }
}

/**
 * Lee los atributos `data-*` del botón pulsado en la fila, puebla los campos del modal
 * mediante `textContent` y precarga todos los controles de edición para Admin y Cliente.
 *
 * @param {HTMLButtonElement} boton Botón `.accion-ver-cita` con los metadatos de la fila.
 * @returns {void}
 */
function abrirModalCita(boton) {
    if (!modalCita) return;

    ultimoBotonCitaActivo = boton;
    const datos = boton.dataset;
    const estado = datos.estado ?? "Pendiente";

    modalMascota.textContent = datos.mascota ?? "";
    modalServicio.textContent = datos.servicio ?? "";
    modalHorarioAprobado.textContent = datos.horarioAprobado ?? "Por asignar";
    modalDisponibilidad.textContent = datos.disponibilidad ?? "Sin preferencia registrada";
    modalMotivo.textContent = datos.motivo ?? "";

    modalEstado.textContent = estado;
    modalEstado.className = `citas-estado ${datos.estadoClase ?? "estado-pendiente"}`;

    if (modalCliente) modalCliente.textContent = datos.cliente ?? "";
    if (modalCorreo) modalCorreo.textContent = datos.correo ?? "";

    if (datos.motivoCambio) {
        modalBloqueCambio.hidden = false;
        modalMotivoCambio.textContent = datos.motivoCambio;
        modalEstadoCambio.textContent = datos.estadoCambio || "Pendiente";
    } else {
        modalBloqueCambio.hidden = true;
        modalMotivoCambio.textContent = "";
        modalEstadoCambio.textContent = "";
    }

    /* Configuración completa del formulario de edición y gestión para el Administrador */
    if (formModalAdmin) {
        inputAdminCitaId.value = datos.id ?? "";
        inputAdminVersion.value = datos.version ?? "0";
        formModalAdmin.dataset.excluirCita = datos.id ?? "0";

        const esPendiente = estado === "Pendiente";
        const esCambio = estado === "Cambio solicitado";
        const esConfirmada = estado === "Confirmada" || estado === "Confirmada (Editada)";

        subtituloAdminProgramacion.textContent = esConfirmada
            ? "Editar servicio, motivo o horario de la cita confirmada"
            : "Editar datos y asignar fecha y horario (24h)";

        seleccionarValorSelect(selectAdminServicio, datos.servicio ?? "");
        if (inputAdminMotivo) {
            inputAdminMotivo.value = datos.motivo ?? "";
        }

        const fechaInicial = (esPendiente || esCambio)
            ? (datos.fechaPreferida || datos.fechaAprobada || "")
            : (datos.fechaAprobada || datos.fechaPreferida || "");
        const horaInicial = (esPendiente || esCambio)
            ? (datos.horaPreferida || datos.horaAprobada || "")
            : (datos.horaAprobada || datos.horaPreferida || "");

        const fechaMinima = inputAdminFecha.getAttribute("min") || "";
        inputAdminFecha.value = (fechaInicial && fechaInicial >= fechaMinima) ? fechaInicial : "";
        selectAdminHora.dataset.horaSeleccionada = horaInicial;

        if (typeof formModalAdmin.recargarHorarios === "function") {
            formModalAdmin.recargarHorarios();
        }

        btnConfirmar.hidden = !(esPendiente || esCambio);
        btnConfirmar.textContent = esCambio ? "Aprobar nuevo horario" : "Asignar y confirmar";
        btnEditar.hidden = false;
        btnMantener.hidden = !esCambio;
        btnRechazar.hidden = estado === "Rechazada";
        btnCancelar.hidden = estado === "Cancelada";
    }

    /* Configuración completa del formulario de edición y eliminación para el Cliente */
    if (avisoEstadoCliente) {
        avisoEstadoCliente.textContent = describirEstadoParaCliente(estado);
    }
    if (formModalCliente) {
        inputClienteCitaId.value = datos.id ?? "";
        inputClienteVersion.value = datos.version ?? "0";
        formModalCliente.dataset.excluirCita = datos.id ?? "0";

        if (selectClienteMascota && datos.mascotaId) {
            selectClienteMascota.value = datos.mascotaId;
        }
        seleccionarValorSelect(selectClienteServicio, datos.servicio ?? "");

        const fechaClienteInicial = datos.fechaPreferida || datos.fechaAprobada || "";
        const horaClienteInicial = datos.horaPreferida || datos.horaAprobada || "";
        const fechaMinimaCliente = inputClienteFecha.getAttribute("min") || "";

        inputClienteFecha.value = (fechaClienteInicial && fechaClienteInicial >= fechaMinimaCliente)
            ? fechaClienteInicial
            : "";
        selectClienteHora.dataset.horaSeleccionada = horaClienteInicial;

        if (inputClienteMotivoConsulta) {
            inputClienteMotivoConsulta.value = datos.motivo ?? "";
        }
        if (inputClienteMotivo) {
            inputClienteMotivo.value = datos.motivoCambio ?? "";
        }

        if (typeof formModalCliente.recargarHorarios === "function") {
            formModalCliente.recargarHorarios();
        }
    }

    modalCita.hidden = false;
    document.body.classList.add("modal-abierto");
    botonCerrarModal?.focus();
}

/**
 * Cierra la ventana modal de citas, restaura el desplazamiento de la página y
 * devuelve el foco al botón que originó la apertura.
 *
 * @returns {void}
 */
function cerrarModalCita() {
    if (!modalCita || modalCita.hidden) return;

    modalCita.hidden = true;
    document.body.classList.remove("modal-abierto");

    if (ultimoBotonCitaActivo) {
        ultimoBotonCitaActivo.focus();
        ultimoBotonCitaActivo = null;
    }
}

if (tablaCitas && modalCita) {
    tablaCitas.addEventListener("click", (evento) => {
        const boton = evento.target.closest(".accion-ver-cita");
        if (boton) {
            abrirModalCita(boton);
        }
    });

    botonCerrarModal?.addEventListener("click", cerrarModalCita);
    botonCerrarPie?.addEventListener("click", cerrarModalCita);

    modalCita.addEventListener("click", (evento) => {
        if (evento.target === modalCita) {
            cerrarModalCita();
        }
    });

    document.addEventListener("keydown", (evento) => {
        if (evento.key === "Escape" && !modalCita.hidden) {
            cerrarModalCita();
        }
    });
}
