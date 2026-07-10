<?php
    session_start();
    if(!isset($_SESSION['usuario_nombre'])){
        header("Location: login.php");
        exit();
    }

    include '../php/conexion.php';
    $rol_usuario = $_SESSION['usuario_rol']; 

    // --- CAPTURAR DATOS DEL CALENDARIO (SI EXISTEN) ---
    $cancha_pre = isset($_GET['cancha_pre']) ? $_GET['cancha_pre'] : '';
    $fecha_pre  = isset($_GET['fecha_pre']) ? $_GET['fecha_pre'] : '';
    $hora_pre   = isset($_GET['hora_pre']) ? $_GET['hora_pre'] : '';
    
    // Calcular automáticamente la hora de fin (1 hora después) para agilizar la carga
    $hora_fin_pre = '';
    if (!empty($hora_pre)) {
        $hora_fin_pre = date('H:i', strtotime($hora_pre . ' + 1 hour'));
    }

    // --- CONFIGURACIÓN DE PAGINACIÓN ---
    $registros_por_pagina = 5; 
    $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    if ($pagina_actual < 1) $pagina_actual = 1;
    $offset = ($pagina_actual - 1) * $registros_por_pagina;

    $sql_total = $conexion->query("SELECT COUNT(*) as total FROM reservas");
    $total_registros = $sql_total->fetch(PDO::FETCH_ASSOC)['total'];
    $total_paginas = ceil($total_registros / $registros_por_pagina);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de Reservas - Pampa Fútbol</title>
    <link rel="stylesheet" href="../css/estilos_reservas.css?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    
    <div class="navbar">
        <div class="menu-toggle" id="mobile-menu">
            <i class="fas fa-bars"></i>
        </div>
        
        <div class="nav-links" id="nav-links">
            <?php if(isset($_SESSION['usuario_rol']) && in_array(strtolower($_SESSION['usuario_rol']), ['duenio', 'dueño'])): ?>
                <a href="dashboard.php">Tablero</a>
                <a href="personal.php">Personal</a>
                <a href="calendario.php">Calendario</a> 
            <?php endif; ?>
            
            <a href="inicio.php">Clientes</a>
            <a href="reservas.php" class="link-activo">Reservas</a> 
            <a href="canchas.php">Canchas</a>
            <a href="pagos.php">Pagos</a>
            
            <?php if(isset($_SESSION['usuario_nombre']) && isset($_SESSION['usuario_rol'])): ?>
                <div class="user-info">
                    <i class="fas fa-user-circle"></i> 
                    <strong><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></strong> 
                    <span class="user-rol">(<?= htmlspecialchars($_SESSION['usuario_rol']) ?>)</span>
                </div>
            <?php endif; ?>
            <a href="../php/cerrar_sesion.php" class="btn-salir">Cerrar Sesion</a>
        </div>
    </div>
 
    <div class="container">
        
        <!-- ALERTAS DEL SISTEMA -->
        <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'eliminado'): ?>
            <div class="alerta alerta-exito">Reserva eliminada correctamente</div>
        <?php endif; ?>
        <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'registrado'): ?>
            <div class="alerta alerta-exito">¡Reserva registrada con éxito!</div>
        <?php endif; ?>
        <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'actualizado'): ?>
            <div class="alerta alerta-exito">¡Estado del turno actualizado!</div>
        <?php endif; ?>
        <?php if (isset($_GET['error']) && $_GET['error'] === 'sin_permisos'): ?>
            <div class="alerta alerta-error">No tienes permisos para realizar esta acción</div>
        <?php endif; ?>
        <?php if (isset($_GET['error']) && $_GET['error'] === 'horario_ocupado'): ?>
            <div class="alerta alerta-error">El horario ya está ocupado para la cancha seleccionada</div>
        <?php endif; ?>
        
        <div class="card-blanca"> 
            <div class="header-reserva">
                <h2 class="titulo-centrado">REGISTRAR NUEVA RESERVA</h2>
                <img src="../img/logo-reserva.png" alt="Icon calendario" class="icono-calendario">
            </div>
            
            <form action="../php/registrar_reservas.php" method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Fecha de la Reserva <span class="asterisco">*</span></label>
                        <input type="date" name="fecha_reserva" value="<?php echo htmlspecialchars($fecha_pre); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Seleccionar Cliente <span class="asterisco">*</span></label>
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
                        <label>Hora de Inicio <span class="asterisco">*</span></label>
                        <input type="time" name="hora_inicio" value="<?php echo htmlspecialchars($hora_pre); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Seleccionar Cancha <span class="asterisco">*</span></label>
                        <select name="idcancha" required>
                            <option value="">> Elija una cancha <</option>
                            <?php
                                $queryC = $conexion->query("SELECT idcancha, tipo_cancha FROM cancha");
                                while($regC = $queryC->fetch(PDO::FETCH_ASSOC)){
                                    // AUTO-SELECCIONAR CANCHA
                                    $selected = ($regC['idcancha'] == $cancha_pre) ? 'selected' : '';
                                    echo "<option value='".$regC['idcancha']."' ".$selected.">".$regC['tipo_cancha']."</option>";
                                }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Hora de Finalizacion <span class="asterisco">*</span></label>
                        <input type="time" name="hora_fin" value="<?php echo htmlspecialchars($hora_fin_pre); ?>" required>
                    </div>
                    <button type="submit" class="btn-guardar btn-full">GUARDAR RESERVA</button>
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
                            <th>Estado</th>
                            <th>WhatsApp</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $sql = "SELECT r.idreservas, c.nombre, c.apellido, c.telefono, ca.tipo_cancha, r.hora_inicio, r.hora_fin, r.estado 
                                    FROM reservas r 
                                    LEFT JOIN clientes c ON r.clientes_idclientes = c.idclientes 
                                    JOIN cancha ca ON r.cancha_idcancha = ca.idcancha 
                                    ORDER BY r.hora_inicio DESC 
                                    LIMIT $registros_por_pagina OFFSET $offset";
            
                            $consulta = $conexion->query($sql);

                            while($fila = $consulta->fetch(PDO::FETCH_ASSOC)){
                                $soloFecha = date("d-m-Y", strtotime($fila['hora_inicio']));
                                $horaI = date("H:i", strtotime($fila['hora_inicio']));
                                $horaF = date("H:i", strtotime($fila['hora_fin']));
                                $nombreCompleto = $fila['nombre'] . " " . $fila['apellido'];
                                
                                // Color para la columna estado
                                $color_estado = '#333';
                                $estado_texto = isset($fila['estado']) ? $fila['estado'] : 'Pendiente';

                                if($estado_texto == 'Confirmado') $color_estado = '#2e7d32'; // Verde
                                if($estado_texto == 'Cancelado') $color_estado = '#c62828'; // Rojo
                                if($estado_texto == 'Pendiente') $color_estado = '#ef6c00'; // Naranja
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($nombreCompleto); ?></td>
                            <td><?php echo htmlspecialchars($fila['tipo_cancha']); ?></td>
                            <td><?php echo $soloFecha; ?></td>
                            <td><?php echo $horaI . " a " . $horaF; ?> hs</td>
                            
                            <td style="color: <?php echo $color_estado; ?>; font-weight: bold;">
                                <?php echo strtoupper($estado_texto); ?>
                            </td>
                            
                            <td>
                                <a href="https://wa.me/<?php echo $fila['telefono']; ?>?text=Hola..." target="_blank" class="btn-whatsapp">WhatsApp</a>
                            </td>
                            <td> 
                                <div class="acciones-flex">
                                    <?php if (in_array(strtolower($rol_usuario), ['admin', 'administrador', 'duenio', 'dueño'])): ?>
                                        
                                        <!-- Botones para Confirmar o Cancelar solo si está Pendiente -->
                                        <?php if(strtolower($estado_texto) == 'pendiente'): ?>
                                            <a href="../php/cambiar_estado_reserva.php?id=<?php echo $fila['idreservas']; ?>&accion=confirmar" class="btn-editar" style="background-color: #2e7d32;">Confirmar</a>
                                            <a href="../php/cambiar_estado_reserva.php?id=<?php echo $fila['idreservas']; ?>&accion=cancelar" class="btn-eliminar" style="background-color: #f39c12;">Cancelar</a>
                                        <?php endif; ?>

                                        <a href="../php/eliminar_reserva.php?id=<?php echo $fila['idreservas']; ?>" class="btn-eliminar" onclick="return confirm('¿Deseas eliminar definitivamente esta reserva?')">Borrar</a>
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

            <?php if($total_paginas > 1): ?>
                <div class="paginacion-wrapper">
                    <?php if($pagina_actual > 1): ?>
                        <a href="?pagina=<?php echo $pagina_actual - 1; ?>" class="btn-pag">&laquo; Anterior</a>
                    <?php endif; ?>

                    <?php for($i = 1; $i <= $total_paginas; $i++): ?>
                        <a href="?pagina=<?php echo $i; ?>" class="btn-pag <?php echo ($i == $pagina_actual) ? 'activo' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>

                    <?php if($pagina_actual < $total_paginas): ?>
                        <a href="?pagina=<?php echo $pagina_actual + 1; ?>" class="btn-pag">Siguiente &raquo;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>
    
    <script src="../js/menu_desplegable.js"></script>
</body>
</html>
