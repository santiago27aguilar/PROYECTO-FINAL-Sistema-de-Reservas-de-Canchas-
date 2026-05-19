<?php
    session_start();
    if(!isset($_SESSION['usuario_nombre'])){
        header("Location: login.php");
        exit();
    }

    include '../php/conexion.php';

    $rol_usuario = $_SESSION['usuario_rol']; 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de Reservas</title>
    <link rel="stylesheet" href="../css/estilos_reservas.css?v=<?php echo time(); ?>">
</head>
<body>
    <nav class="navbar">
        <a href="inicio.php">INICIO (CLIENTES)</a>
        <a href="canchas.php">CANCHAS</a>
        <a href="pagos.php">PAGOS</a>
        <a href="../php/cerrar_sesion.php" class="btn-salir">CERRAR SESION</a>
    </nav>
 
    <div class="container">
        
        <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'eliminado'): ?>
            <div class="alerta alerta-exito">Reserva eliminada correctamente</div>
        <?php endif; ?>
        <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'registrado'): ?>
            <div class="alerta alerta-exito">¡Reserva registrada con éxito!</div>
        <?php endif; ?>
        <?php if (isset($_GET['error']) && $_GET['error'] === 'sin_permisos'): ?>
            <div class="alerta alerta-error">No tienes permisos para realizar esta acción</div>
        <?php endif; ?>
        <?php if (isset($_GET['error']) && $_GET['error'] === 'horario_ocupado'): ?>
            <div class="alerta alerta-error">Error: Ese horario ya está ocupado para la cancha seleccionada</div>
        <?php endif; ?>

        <div class="card-blanca"> 

            <div class="header-reserva">
                <h2 class="titulo-centrado">REGISTRAR NUEVA RESERVA</h2>
                <img src="../img/logo-reserva.png" alt="Icon calendario" class="icono-calendario">
            </div>
            
            <form action="../php/registrar_reservas.php" method="POST">
                <div class="form-grid">
                    
                    <div class="form-group">
                        <label>Fecha de la Reserva</label>
                        <input type="date" name="fecha_reserva" required>
                    </div>
                    <div class="form-group">
                        <label>Seleccionar Cliente</label>
                        <select name="id_cliente" required>
                            <option value="">> Elija un cliente <</option>
                            <?php
                                $query = $conexion->query("SELECT idclientes, nombre, apellido FROM clientes");
                                while($reg = $query->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='".$reg['idclientes']."'>".$reg['nombre']." ".$reg['apellido']."</option>";
                                }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Hora de Inicio</label>
                        <input type="time" name="hora_inicio" required>
                    </div>
                    <div class="form-group">
                        <label>Seleccionar Cancha</label>
                        <select name="idcancha" required>
                            <option value="">> Elija una cancha <</option>
                            <?php
                                $queryC = $conexion->query("SELECT idcancha, tipo_cancha FROM cancha");
                                while($regC = $queryC->fetch(PDO::FETCH_ASSOC)){
                                    echo "<option value='".$regC['idcancha']."'>".$regC['tipo_cancha']."</option>";
                                }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Hora de Finalizacion</label>
                        <input type="time" name="hora_fin" required>
                    </div>
                    <button type="submit" class="btn-guardar btn-full" style="align-self: end;">GUARDAR RESERVA</button>
                    
                </div>
            </form>
        </div>

        <div class="seccion-tablas">
            <div class="header-lista-reservas">
                <h2 class="titulo-izquierdo">LISTAS DE LAS RESERVAS</h2>
                <div class="botones-exportar">
                    <a href="../php/exportar_pdf_reservas.php" class="btn-exportar btn-pdf">PDF</a>
                    <a href="../php/exportar_excel_reservas.php" class="btn-exportar btn-excel">EXCEL</a>
                </div>
            </div>
            
            <div class="table-responsive-wrapper">
                <table class="tabla-moderna">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Cancha</th>
                            <th>Fecha</th>
                            <th>Horario</th>
                            <th>WhatsApp</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $sql = "SELECT r.idreservas, c.nombre, c.apellido, c.telefono, ca.tipo_cancha, r.hora_inicio, r.hora_fin 
                                    FROM reservas r 
                                    LEFT JOIN clientes c ON r.clientes_idclientes = c.idclientes 
                                    JOIN cancha ca ON r.cancha_idcancha = ca.idcancha 
                                    ORDER BY r.hora_inicio DESC";
            
                            $consulta = $conexion->query($sql);

                            while($fila = $consulta->fetch(PDO::FETCH_ASSOC)){
                                $soloFecha = date("d-m-Y", strtotime($fila['hora_inicio']));
                                $horaI = date("H:i", strtotime($fila['hora_inicio']));
                                $horaF = date("H:i", strtotime($fila['hora_fin']));
                                $nombreCompleto = $fila['nombre'] . " " . $fila['apellido'];
                        ?>
                        <tr>
                            <td><?php echo $nombreCompleto; ?></td>
                            <td><?php echo $fila['tipo_cancha']; ?></td>
                            <td><?php echo $soloFecha; ?></td>
                            <td><?php echo $horaI . " a " . $horaF; ?> hs</td>
                            <td>
                                <a href="https://wa.me/<?php echo $fila['telefono']; ?>?text=Hola..." target="_blank" class="btn-whatsapp">WhatsApp</a>
                            </td>
                            <td> 
                                <div class="acciones-flex">
                                    <?php if (strtolower($rol_usuario) === 'admin' || strtolower($rol_usuario) === 'administrador'): ?>
                                        <a href="../php/eliminar_reserva.php?id=<?php echo $fila['idreservas']; ?>" class="btn-eliminar" onclick="return confirm('¿Deseas eliminar esta reserva?')">Eliminar</a>
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
