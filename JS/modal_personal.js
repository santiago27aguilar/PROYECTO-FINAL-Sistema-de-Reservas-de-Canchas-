function abrirModal(id, nombre, rol, correo) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nombre').value = nombre;
    document.getElementById('edit_rol').value = rol;
    document.getElementById('edit_correo').value = correo;
    
    document.getElementById('modalEditar').style.display = 'block';
}

function cerrarModal() {
    document.getElementById('modalEditar').style.display = 'none';
}

window.onclick = function(event) {
    var modal = document.getElementById('modalEditar');
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
