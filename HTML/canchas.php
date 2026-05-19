<?php
    session_start();
    if(!isset($_SESSION['usuario_nombre'])){
        header('Location: login.html');
        exit();
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Canchas</title>
    <link rel="stylesheet" href="../css/estilos_canchas.css?v=2">
</head>
<body>
    <nav class="navbar">
        <a href="inicio.php">INICIO (CLIENTES)</a>
        <a href="reservas.php">RESERVAS</a>
        <a href="pagos.php">PAGOS</a>
        <a href="../php/cerrar_sesion.php" class="btn-salir">CERRAR SESION</a>
    </nav>

    <div class="container">

        <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'eliminado'): ?>
            <div class="alerta alerta-exito">Cancha eliminada correctamente</div>
        <?php endif; ?>
        <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'registrado'): ?>
            <div class="alerta alerta-exito">¡Cancha registrada con éxito!</div>
        <?php endif; ?>
        <?php if (isset($_GET['error']) && $_GET['error'] === 'sin_permisos'): ?>
            <div class="alerta alerta-error">No tienes permisos para realizar esta acción</div>
        <?php endif; ?>
        <?php if (isset($_GET['error']) && $_GET['error'] === 'tiene_reservas'): ?>
            <div class="alerta alerta-error">Error: La cancha tiene reservas activas</div>
        <?php endif; ?>
        
        <div class="card-blanca">
            
            <div class="header-titulo-cancha">
                <div class="textos-cancha">
                    <h2>REGISTRAR NUEVA CANCHA</h2>
                    <p class="subtitulo">(Futbol 5 - Futbol 7 - Padel)</p>
                </div>
                <img src="../img/logo-registrar.png" alt="Icono Cancha" class="icono-cancha">
            </div>
            
            <form action="../php/registrar_cancha.php" method="POST" class="reserva-form">
                
                <div class="grid-split">
                    
                    <div class="seccion-imagenes">
                        <img src="../img/futboll.png" alt="Foto Cancha 1" class="img-cuadro">
                        <img src="../img/padell.png" alt="Foto Cancha 2" class="img-cuadro">
                    </div>

                    <div class="seccion-form">
                        <div class="form-group">
                            <label>Tipo de CANCHA:</label>
                            <select name="tipo_cancha" required>
                                <option value="">> Elije una cancha <</option>
                                <option value="Futbol 5 - Cancha 1">Futbol 5 - Cancha 1</option>
                                <option value="Futbol 5 - Cancha 2">Futbol 5 - Cancha 2</option>
                                <option value="Futbol 5 - Cancha 3">Futbol 5 - Cancha 3</option>
                                <option value="Futbol 7 - Cancha 1">Futbol 7 - Cancha 1</option>
                                <option value="Futbol 7 - Cancha 2">Futbol 7 - Cancha 2</option>
                                <option value="Futbol 7 - Cancha 3">Futbol 7 - Cancha 3</option>
                                <option value="Padel - Cancha 1">Padel - Cancha 1</option>
                                <option value="Padel - Cancha 2">Padel - Cancha 2</option>
                                <option value="Padel - Cancha 3">Padel - Cancha 3</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Precio por HORA:</label>
                            <input type="number" name="precio_hora" onkeydown="return event.keyCode !== 69" placeholder="Ej: 5000" autocomplete="off" required>
                        </div>

                        <button type="submit" class="btn-guardar btn-full" style="margin-top: 15px;">GUARDAR CANCHA</button>
                    </div>
                    
                </div>
            </form>
        </div>

        <div class="table-container">
            <h2 class="titulo-izquierdo">LISTA DE CANCHAS DISPONIBLES</h2>
            
            <div class="table-responsive-wrapper">
                <table class="tabla-moderna">
                    <thead>
                        <tr>
                            <!--<th>ID</th>-->
                            <th>Tipo de CANCHA</th>
                            <th>Precio por HORA</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            include '../php/conexion.php';
                            $consulta = $conexion->query("SELECT * FROM cancha");
                            while($fila = $consulta->fetch(PDO::FETCH_ASSOC)){ ?>
                                <tr>
                                    <!--<td>#<?php echo $fila['idcancha']; ?></td>-->
                                    <td><strong><?php echo $fila['tipo_cancha']; ?></strong></td>
                                    <td>$<?php echo number_format($fila['precio_hora'], 2); ?></td>
                                    <td>
                                        <div class="acciones-flex">
                                            <?php if (strtolower($_SESSION['usuario_rol']) === 'admin' || strtolower($_SESSION['usuario_rol']) === 'administrador'): ?>
                                                <a href="../php/eliminar_cancha.php?id=<?php echo $fila['idcancha']; ?>" class="btn-eliminar" onclick="return confirm('¿Deseas eliminar esta cancha?')">Eliminar</a>
                                            <?php else: ?>
                                                <span class="sin-permisos">Sin Permisos</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="../js/alerta_cliente.js"></script>

</body>
</html>
