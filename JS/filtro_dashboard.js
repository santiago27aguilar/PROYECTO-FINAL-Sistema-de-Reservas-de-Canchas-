document.addEventListener('DOMContentLoaded', function() {
    const btnFiltrar = document.getElementById('btn-filtrar');
    const btnHistorico = document.getElementById('btn-historico');
    const fechaInicio = document.getElementById('fecha-inicio');
    const fechaFin = document.getElementById('fecha-fin');
    const btnPdf = document.getElementById('btn-pdf-dashboard'); // Nueva constante para el PDF

    function cargarEstadisticas(url, esHistorico = false) {
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if(data.error) {
                    console.error("Error:", data.error);
                    alert("Ocurrió un error al traer las estadísticas.");
                    return;
                }

                // 1. Actualizar las 3 tarjetas superiores de forma dinámica
                document.getElementById('val-ganancias').textContent = '$' + data.ganancias_periodo;
                document.getElementById('val-turnos').textContent = data.turnos_periodo + ' turnos';
                document.getElementById('val-recaudado').textContent = '$' + data.ganancias_periodo;

                // Cambiar títulos dinámicamente según lo que se esté viendo
                if(esHistorico) {
                    document.getElementById('titulo-tarjeta-ganancias').textContent = "GANANCIAS HISTÓRICAS";
                    document.getElementById('titulo-tarjeta-turnos').textContent = "TURNOS HISTÓRICOS";
                    document.getElementById('titulo-tarjeta-recaudado').textContent = "TOTAL RECAUDADO";
                } else {
                    document.getElementById('titulo-tarjeta-ganancias').textContent = "GANANCIAS DEL MES";
                    document.getElementById('titulo-tarjeta-turnos').textContent = "TURNOS DEL MES";
                    document.getElementById('titulo-tarjeta-recaudado').textContent = "RECAUDADO EN EL MES";
                }

                // 2. Actualizar las Estadísticas Generales
                document.getElementById('stat-dia').textContent = data.dia_mas_solicitado;
                document.getElementById('stat-hora').textContent = data.horario_pico;
                document.getElementById('stat-cancha').textContent = data.cancha_mas_usada;

                // 3. Reconstruir la tabla de Mejores Clientes
                const tbody = document.getElementById('tabla-clientes-body');
                tbody.innerHTML = ''; 

                if(data.top_clientes.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="3">Aún no hay reservas registradas en este período</td></tr>';
                } else {
                    data.top_clientes.forEach(cliente => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${cliente.nombre} ${cliente.apellido}</td>
                            <td>${cliente.total_periodo || 0} turnos</td>
                            <td>${cliente.total_historico} turnos</td>
                        `;
                        tbody.appendChild(tr);
                    });
                }
            })
            .catch(error => console.error("Error de conexión:", error));
    }

    // ACCIÓN 1: Filtrar por fechas
    btnFiltrar.addEventListener('click', function() {
        const desde = fechaInicio.value;
        const hasta = fechaFin.value;

        if(!desde || !hasta) {
            alert("Por favor seleccioná ambas fechas (Desde y Hasta).");
            return;
        }

        if(desde > hasta) {
            alert("La fecha 'Desde' no puede ser posterior a la fecha 'Hasta'.");
            return;
        }

        const url = `../php/obtener_estadisticas_ajax.php?desde=${desde}&hasta=${hasta}`;
        cargarEstadisticas(url, false);
    });

    // ACCIÓN 2: Ver Historial Completo
    btnHistorico.addEventListener('click', function() {
        fechaInicio.value = '';
        fechaFin.value = '';

        const url = `../php/obtener_estadisticas_ajax.php?historico=true`;
        cargarEstadisticas(url, true);
    });

    // ACCIÓN 3: Exportar a PDF
    if (btnPdf) {
        btnPdf.addEventListener('click', function(e) {
            e.preventDefault(); // Evitamos que el enlace recargue la página
            
            const desde = fechaInicio.value;
            const hasta = fechaFin.value;
            
            // Redirigimos al archivo PHP de exportación pasando los parámetros
            window.location.href = `../php/exportar_pdf_dashboard.php?desde=${desde}&hasta=${hasta}`;
        });
    }
});
