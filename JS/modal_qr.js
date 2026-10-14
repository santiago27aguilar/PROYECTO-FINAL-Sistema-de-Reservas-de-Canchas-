document.addEventListener("DOMContentLoaded", function () {
    const btnAbrirQr = document.getElementById("btn-abrir-qr");
    const modalQr = document.getElementById("modal-qr");
    const btnCerrarQr = document.getElementById("btn-cerrar-qr");

    // Abrir modal al hacer clic en el botón QR
    if (btnAbrirQr && modalQr) {
        btnAbrirQr.addEventListener("click", function () {
            modalQr.classList.remove("oculto");
        });
    }

    // Cerrar modal al hacer clic en la "X"
    if (btnCerrarQr && modalQr) {
        btnCerrarQr.addEventListener("click", function () {
            modalQr.classList.add("oculto");
        });
    }

    // Cerrar modal si hacen clic fuera de la caja blanca (en el fondo oscuro)
    if (modalQr) {
        window.addEventListener("click", function (event) {
            if (event.target === modalQr) {
                modalQr.classList.add("oculto");
            }
        });
    }
});
