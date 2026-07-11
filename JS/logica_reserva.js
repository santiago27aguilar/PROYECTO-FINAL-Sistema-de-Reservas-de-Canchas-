function actualizarPrecio() {
    const selectCancha = document.getElementById('id_cancha');
    const selectDuracion = document.getElementById('duracion_turno');
    const cuadro = document.getElementById('cuadro_precio');
    const spanPrecio = document.getElementById('precio_final');
    const spanTexto = document.getElementById('texto_duracion');

    const opcionCancha = selectCancha.options[selectCancha.selectedIndex];
    
    if (!opcionCancha || !opcionCancha.value) {
        cuadro.classList.add('invisible');
        return;
    }

    const precioBase = parseFloat(opcionCancha.getAttribute('data-precio'));
    const horas = parseFloat(selectDuracion.value);

    if(precioBase) {
        cuadro.classList.remove('invisible');
        spanPrecio.innerText = '$' + (precioBase * horas);
        spanTexto.innerText = horas + (horas === 1 ? ' Hora = ' : ' Horas = ');
    }
}

function actualizarHorarios() {
    const idCancha = document.getElementById('id_cancha').value;
    const fecha = document.getElementById('fecha_reserva').value;
    const selectHora = document.getElementById('hora_reserva');

    if (idCancha && fecha && selectHora) {
        selectHora.innerHTML = '<option value="">Buscando...</option>';
        fetch(`../php/obtener_horarios.php?id_cancha=${idCancha}&fecha=${fecha}&t=${new Date().getTime()}`)
            .then(res => res.json())
            .then(horarios => {
                selectHora.innerHTML = '<option value="">Elegir Horario...</option>';
                horarios.forEach(hora => {
                    const option = document.createElement('option');
                    option.value = hora;
                    option.textContent = hora + ' hs';
                    selectHora.appendChild(option);
                });
            })
            .catch(() => {
                selectHora.innerHTML = '<option value="">Sin turnos</option>';
            });
    }
}

document.getElementById('id_cancha').addEventListener('change', () => {
    actualizarPrecio();
    actualizarHorarios(); 
});

document.getElementById('fecha_reserva').addEventListener('change', actualizarHorarios); 
document.getElementById('duracion_turno').addEventListener('change', actualizarPrecio);

if (window.history.replaceState) {
    
    window.history.replaceState(null, null, window.location.pathname);
}

