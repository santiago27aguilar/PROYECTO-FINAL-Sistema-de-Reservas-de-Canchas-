function mostrarDetalle(index) {
    document.getElementById('vista-lista').classList.add('oculto');
    document.getElementById('detalle-' + index).classList.remove('oculto');
}

function volverALista(index) {
    document.getElementById('detalle-' + index).classList.add('oculto');
    document.getElementById('vista-lista').classList.remove('oculto');
}

function filtrarTurnos(categoria, botonClick) {
    const botones = document.querySelectorAll('.tab-btn');
    botones.forEach(btn => btn.classList.remove('activo'));
    botonClick.classList.add('activo');

    const tarjetas = document.querySelectorAll('.mini-tarjeta-turno');
    let visibles = 0;
    
    tarjetas.forEach(tarjeta => {
        const categoriaTarjeta = tarjeta.getAttribute('data-categoria');
        
        if (categoria === 'Todas' || categoriaTarjeta.includes(categoria)) {
            tarjeta.style.display = 'flex';
            visibles++;
        } else {
            tarjeta.style.display = 'none';
        }
    });

    let mensajeVacio = document.getElementById('mensaje-vacio-filtro');
    
    if (visibles === 0) {
        if (!mensajeVacio) {
            mensajeVacio = document.createElement('div');
            mensajeVacio.id = 'mensaje-vacio-filtro';
            mensajeVacio.className = 'mensaje-filtro-vacio'; // Usamos la clase CSS
            mensajeVacio.innerText = 'No tienes TURNOS ACTIVOS en esta categoría.';
            document.querySelector('.contenedor-lista-turnos').appendChild(mensajeVacio);
        } else {
            mensajeVacio.style.display = 'block';
        }
    } else {
        if (mensajeVacio) {
            mensajeVacio.style.display = 'none';
        }
    }
}
