document.addEventListener('DOMContentLoaded', function() {
    // Buscamos el input del buscador por su nombre en el documento
    const inputBuscar = document.querySelector('input[name="buscar"]');

    if (inputBuscar) {
        inputBuscar.addEventListener('keyup', function() {
            const texto = this.value.toLowerCase();
            const filas = document.querySelectorAll('.tabla-moderna tr');

            filas.forEach((fila, indice) => {
                if (indice === 0) return; // Saltea la cabecera de la tabla
                const contenidoFila = fila.textContent.toLowerCase();
                fila.style.display = contenidoFila.includes(texto) ? '' : 'none';
            });
        });
    }
});
