function mostrarOpciones(){
    let seleccion = document.getElementById("metodo").value;
    //document.getElementById("opciones_tarjeta").style.display = (seleccion === "Tarjeta")? "block" : "none";
    //document.getElementById("seccion_alias").style.display = (seleccion === "Transferencia")? "block" : "none";

    let divAlias = document.getElementById("seccion_alias");

    if (divAlias) {
        if (seleccion === "Transferencia") {
            divAlias.style.display = "block";
        } else {
            divAlias.style.display = "none";
        }
    }
}

document.addEventListener('change',function(e){

    if(e.target && e.target.name === 'id_reserva'){
        const selectedOption = e.target.options[e.target.selectedIndex];

        const precio = parseFloat(selectedOption.getAttribute('data-precio'));
        const horasTotales = parseFloat(selectedOption.getAttribute('data-horas')); 

        if(!isNaN(precio) && !isNaN(horasTotales)){
            document.getElementById('precio_display').innerText = precio.toFixed(2);
            document.getElementById('horas_display').innerText = Math.floor(horasTotales);

            const total = (precio * horasTotales).toFixed(2);
            document.getElementById('monto_visual').innerText = total;
            document.getElementById('monto_resultado').value = total;
        } else {
            document.getElementById('precio_display').innerText = "0.00";
            document.getElementById('horas_display').innerText = "0";
            document.getElementById('monto_visual').innerText = "0.00";
            document.getElementById('monto_resultado').value = "";
        }
    }
});

document.addEventListener('DOMContentLoaded', function(){

    const form = document.getElementById('formPago');

    if(form){
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const monto = document.getElementById('monto_resultado').value;
            const reserva = document.getElementsByName('id_reserva')[0].value;

            if(reserva === "" || monto <= 0){
                alert("Error: debes seleccionar una reserva valida antes de confirmar.");
                return;
            }

            const mensaje = "Confirmar el registro de este pago por $" + monto + "?\n\n" + "Al aceptar: \n" + "- La reserva se marcara como 'Pagado'.\n" + "- La cancha quedara 'Disponible' inmediatamente.";

            if(confirm(mensaje)){
                this.submit();
            }
        });
    }

    const metodoSelect = document.getElementById('metodo');
    if(metodoSelect){
        metodoSelect.addEventListener('change', mostrarOpciones);
    }

    mostrarOpciones();

});

document.getElementById('buscarPago').addEventListener('keyup', function() {
    const texto = this.value.toLowerCase();
    const filas = document.querySelectorAll('table tr');

    filas.forEach((fila, indice)=>{
        if (indice === 0) return;
        const contenidoFila = fila.textContent.toLocaleLowerCase();
        fila.style.display = contenidoFila.includes(texto) ? '' : 'none';
    })
});
