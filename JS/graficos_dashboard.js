(function () {
    const inputRapido = document.getElementById('input-rapido');
    const btnPersonalizado = document.getElementById('btn-rapido-personalizado');
    const fechaInicio = document.getElementById('fecha-inicio');
    const fechaFin = document.getElementById('fecha-fin');
    const formFiltro = document.getElementById('formFiltroDashboard');
    const btnPdf = document.getElementById('btn-pdf-dashboard');

    function actualizarLinkPdf() {
        if (!btnPdf) return;
        const d = fechaInicio ? fechaInicio.value : '';
        const h = fechaFin ? fechaFin.value : '';
        const r = inputRapido ? inputRapido.value : '';
        const params = new URLSearchParams();
        if (d) params.set('desde', d);
        if (h) params.set('hasta', h);
        if (r) params.set('rapido', r);
        btnPdf.href = '../php/exportar_pdf_dashboard.php?' + params.toString();
    }

    function marcarPersonalizado() {
        if (inputRapido) inputRapido.value = 'personalizado';
        document.querySelectorAll('.btn-rapido').forEach(btn => btn.classList.remove('activo'));
        if (btnPersonalizado) btnPersonalizado.classList.add('activo');
        actualizarLinkPdf();
    }

    if (btnPersonalizado) {
        btnPersonalizado.addEventListener('click', function () {
            marcarPersonalizado();
            if (fechaInicio) fechaInicio.focus();
        });
    }

    if (fechaInicio) fechaInicio.addEventListener('change', marcarPersonalizado);
    if (fechaFin) fechaFin.addEventListener('change', marcarPersonalizado);
    if (formFiltro) {
        formFiltro.addEventListener('submit', function () {
            if (inputRapido && !inputRapido.value) {
                inputRapido.value = 'personalizado';
            }
            actualizarLinkPdf();
        });
    }
    actualizarLinkPdf();

    const paletaColores = [
        '#2b1b54',
        '#11ac35',
        '#1976d2',
        '#ef6c00',
        '#8e24aa',
        '#00897b',
        '#c62828',
        '#5d4037',
        '#3949ab',
        '#00acc1',
        '#7cb342',
        '#d81b60'
    ];

    const ctxCanchas = document.getElementById('tortaCanchas');
    if (ctxCanchas && typeof Chart !== 'undefined') {
        const labelsCanchas = JSON.parse(ctxCanchas.dataset.labels || '[]');
        const valoresCanchas = JSON.parse(ctxCanchas.dataset.valores || '[]');

        new Chart(ctxCanchas, {
            type: 'pie',
            data: {
                labels: labelsCanchas.length ? labelsCanchas : ['Sin datos'],
                datasets: [{
                    label: 'Turnos',
                    data: valoresCanchas.length ? valoresCanchas : [1],
                    backgroundColor: labelsCanchas.length ? paletaColores : ['#d1d5db'],
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    const ctxFranjas = document.getElementById('tortaFranjas');
    if (ctxFranjas && typeof Chart !== 'undefined') {
        const labelsFranjas = JSON.parse(ctxFranjas.dataset.labels || '[]');
        const valoresFranjas = JSON.parse(ctxFranjas.dataset.valores || '[]');

        new Chart(ctxFranjas, {
            type: 'pie',
            data: {
                labels: labelsFranjas.length ? labelsFranjas : ['Sin datos'],
                datasets: [{
                    label: 'Turnos',
                    data: valoresFranjas.length ? valoresFranjas : [1],
                    backgroundColor: labelsFranjas.length ? ['#ef6c00', '#2b1b54'] : ['#d1d5db'],
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
})();
