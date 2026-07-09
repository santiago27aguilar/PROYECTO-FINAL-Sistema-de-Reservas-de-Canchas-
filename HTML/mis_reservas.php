<!DOCTYPE html>
<html lang="es"> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Mi Reserva</title>
    <link rel="stylesheet" href="../css/estilos_mis_reservas.css">
</head>
<body>

<div class="reserva-card">
    <h2>GESTIONÁ TU TURNO</h2>
    
    <img src="../img/usuario-logo.png" alt="Icono Usuario" class="icono-usuario">
    
    <p class="texto-explicativo">Ingresá tus datos para VER RESERVA o CANCELAR RESERVA si es necesario</p>

    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'cancelado'): ?>
        <div class="alerta-exito">
            Tu turno ha sido cancelado correctamente
        </div>
    <?php endif; ?>
    
    <form action="../php/buscar_reserva.php" method="POST">
        
        <div class="form-row-2">
            <div class="form-group">
                <label>Documento (DNI)</label>
                <input type="text" name="dni" placeholder="Sin puntos..." required>
            </div>

            <div class="form-group">
                <label>Correo Electrónico</label>
                <input type="email" name="correo" placeholder="ejemplo@correo.com" required>
            </div>
        </div>

        <button type="submit" class="btn-enviar">BUSCAR MI TURNO</button>
    </form>

    <div class="link-gestion">
        <a href="cliente.php">VOLVER AL FORMULARIO DE RESERVA</a>
    </div>

    <div class="link-gestion margen-top">
        <a href="historial_reservas.php" class="link-destacado">VER MI HISTORIAL COMPLETO DE TURNOS</a>
    </div>

</div>

</body> 
</html>
