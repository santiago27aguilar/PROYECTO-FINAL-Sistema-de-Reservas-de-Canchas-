// --- LIMPIAR URL DESPUÉS DE MOSTRAR ALERTAS EN EL PANEL ADMIN ---
if (window.location.search.includes('error=') || window.location.search.includes('mensaje=')) {
    window.history.replaceState(null, null, window.location.pathname);
}
