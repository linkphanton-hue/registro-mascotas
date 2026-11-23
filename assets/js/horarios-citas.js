// Cada formulario consulta los turnos de su fecha y desactiva los ocupados.
document.querySelectorAll('form[data-agenda-citas]').forEach((formulario) => {
    const fecha = formulario.querySelector('[data-fecha-cita]');
    const horario = formulario.querySelector('[data-hora-cita]');
    const mensaje = formulario.querySelector('[data-estado-horarios]');
    let consulta = null;

    async function actualizarHorarios() {
        if (consulta) consulta.abort();
        const control = new AbortController();
        consulta = control;
        const seleccionado = horario.value || horario.dataset.horaSeleccionada || '';
        delete horario.dataset.horaSeleccionada;
        horario.replaceChildren(new Option('Selecciona un horario', ''));
        horario.disabled = true;
        fecha.setCustomValidity('');
        mensaje.textContent = '';
        if (!fecha.value) {
            mensaje.textContent = 'Selecciona una fecha para ver los horarios.';
            return;
        }

        mensaje.textContent = 'Consultando disponibilidad…';
        try {
            const url = new URL(formulario.dataset.horariosUrl, window.location.href);
            url.searchParams.set('fecha', fecha.value);
            if (formulario.dataset.excluirCita) {
                url.searchParams.set('excluir_cita', formulario.dataset.excluirCita);
            }
            const respuesta = await fetch(url, {signal: control.signal, cache: 'no-store'});
            const datos = await respuesta.json();
            if (control.signal.aborted) return;
            if (!respuesta.ok) {
                const error = datos.mensaje || 'No se pudo consultar la agenda.';
                mensaje.textContent = error;
                if (respuesta.status === 422) fecha.setCustomValidity(error);
                return;
            }
            let disponibles = 0;
            for (const turno of datos.turnos) {
                const opcion = document.createElement('option');
                opcion.value = turno.hora;
                opcion.textContent = turno.etiqueta + (turno.disponible ? '' : ' — No disponible');
                opcion.disabled = !turno.disponible;
                opcion.selected = turno.disponible && turno.hora === seleccionado;
                horario.appendChild(opcion);
                if (turno.disponible) disponibles++;
            }
            horario.disabled = disponibles === 0;
            mensaje.textContent = disponibles > 0
                ? 'Selecciona un turno disponible. Se reserva cuando el administrador confirma.'
                : 'No quedan turnos disponibles para esa fecha. Selecciona otro día.';
        } catch (error) {
            if (!control.signal.aborted && error.name !== 'AbortError') {
                mensaje.textContent = 'No se pudo consultar la agenda. Recarga la página e inténtalo de nuevo.';
            }
        }
    }

    fecha.addEventListener('change', actualizarHorarios);
    actualizarHorarios();
});
