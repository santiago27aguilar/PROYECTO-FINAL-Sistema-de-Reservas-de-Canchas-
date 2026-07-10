<?php
    session_start();
    if(!isset($_SESSION['id_usuario'])){
        header("Location: login.php");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pagos - Pampa Fútbol</title>
    <link rel="stylesheet" href="../css/estilos_pagos.css?v=9">
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
            <a href="reservas.php">Reservas</a>
            <a href="canchas.php">Canchas</a>
            <a href="pagos.php" class="link-activo">Pagos</a>
            
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
        
        <div class="card-blanca">
            
            <div class="header-pagos">
                <h2>SECCION DE PAGOS</h2>
                <img src="../img/flujo-de-efectivo.png" alt="Foto Pagos" class="img-cuadro-pagos">
            </div>
            
            <?php if(isset($_GET['exito'])): ?>
                <div class='alerta alerta-exito'>Pago registrado y cancha liberada con éxito!</div>
            <?php endif; ?>

            <?php if(isset($_GET['mensaje']) && $_GET['mensaje'] === 'eliminado'): ?>
                <div class="alerta alerta-exito">Pago eliminado correctamente</div>
            <?php endif; ?>

            <?php if(isset($_GET['error']) && $_GET['error'] == 'monto_invalido'): ?>
                <div class='alerta alerta-error'>Error: el monto debe ser mayor a cero</div>
            <?php endif; ?> 

            <?php if(isset($_GET['error']) && $_GET['error'] == 'sin_permisos'): ?>
                <div class="alerta alerta-error">No tienes permisos para realizar esta acción</div>
            <?php endif; ?>

            <form id="formPago" action="../php/registrar_pagos.php" method="POST" class="reserva-form">
                
                <div class="grid-pagos-top">
                    
                    <div class="columna-izq">
                        <div class="form-group">
                            <label>Selecciona una RESERVA <span class="asterisco"></span></label>
                            <select name="id_reserva" id="id_reserva" onchange="actualizarCalculos()" required>
                                <option value="">> Elije una RESERVA <</option>
                                <?php
                                    include '../php/conexion.php';
                                    
                                    // LA ÚNICA LÍNEA MODIFICADA: r.estado = 'Confirmado'
                                    $query = $conexion->query("SELECT r.idreservas, c.nombre, c.apellido, can.precio_hora, r.hora_inicio, r.hora_fin, TIMESTAMPDIFF(HOUR, r.hora_inicio, r.hora_fin) AS total_horas FROM reservas r JOIN clientes c ON r.clientes_idclientes = c.idclientes JOIN cancha can ON r.cancha_idcancha = can.idcancha WHERE r.estado = 'Confirmado'");

                                    while($reg = $query->fetch(PDO::FETCH_ASSOC)){
                                        echo "<option value='".$reg['idreservas']."' data-precio= '".$reg['precio_hora']."' data-horas='".$reg['total_horas']."' data-inicio='".$reg['hora_inicio']."' data-fin= '".$reg['hora_fin']."'>".$reg['nombre']." ".$reg['apellido']."</option>";
                                    }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Metodos de PAGOS <span class="asterisco"></span></label>
                            <select name="metodo_pago" id="metodo" onchange="mostrarOpciones()" required>
                                <option value="">> Elije un MÉTODO <</option>
                                <option value="Efectivo">EFECTIVO</option>
                                <option value="Transferencia">TRANSFERENCIA</option>
                            </select>
                        </div>
                    </div>

                    <div class="columna-der">
                        <div class="form-group">
                            <label class="label-derecha">Precio por HORA TOTAL</label>
                            <div class="info-pago">$ <span id="precio_display">0.00</span></div>
                        </div>

                        <div class="form-group">
                            <label class="label-derecha">Horas de USO</label>
                            <div class="info-pago"><span id="horas_display">0</span> hrs</div>
                        </div>
                    </div>

                </div>

                <div id="seccion_alias" class="alias-caja">
                    <p><strong>ALIAS:</strong> planeta.futbol.padel</p>
                </div>

                <div class="grid-pagos-bottom">
                    <div class="monto-final-container">
                        <span class="monto-texto">MONTO FINAL : </span>
                        <span class="monto-valor">$ <span id="monto_visual">0.00</span></span>
                        <input type="hidden" name="monto" id="monto_resultado" readonly>
                    </div>

                    <button type="submit" class="btn-guardar">CONFIRMAR PAGO</button>
                </div>

            </form>
        </div>

        <div class="table-container"> 
            <h2 class="titulo-centrado">HISTORIAL DE TODOS LOS PAGOS</h2>
            
            <div class="herramientas-tabla">
                <a href="../php/exportar_excel_pagos.php" class="btn-exportar btn-excel">EXCEL</a>
                
                <input type="text" id="buscarPago" class="buscador-moderno" placeholder="Buscar por nombre del cliente...">
                
                <a href="../php/exportar_pdf_pagos.php" class="btn-exportar btn-pdf">PDF</a>
            </div>

            <div class="table-responsive-wrapper">
                <table class="tabla-moderna">
                    <thead>
                        <tr>
                            <th>Monto</th>
                            <th>Método</th>
                            <th>Cliente</th>
                            <th>Cancha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead> 
                    <tbody>
                        <?php
                            $sql = "SELECT pagos.idpagos, pagos.monto, pagos.metodo_pago, clientes.nombre, clientes.apellido, cancha.tipo_cancha FROM pagos JOIN reservas ON pagos.reservas_idreservas = reservas.idreservas JOIN clientes ON reservas.clientes_idclientes = clientes.idclientes JOIN cancha ON reservas.cancha_idcancha = cancha.idcancha ORDER BY pagos.idpagos DESC";
                            
                            $resPagos = $conexion->query($sql);
                            while($f = $resPagos->fetch(PDO::FETCH_ASSOC)) { ?>
                            <tr>
                                <td>$<?php echo number_format($f['monto'], 2); ?></td>
                                <td><?php echo ucfirst($f['metodo_pago']); ?></td>
                                <td><?php echo $f['nombre'] . " " . $f['apellido']; ?></td>
                                <td><?php echo $f['tipo_cancha']; ?></td>
                                <td>
                                    <div class="acciones-flex">
                                        
                                        <?php if (isset($_SESSION['usuario_rol']) && in_array(strtolower($_SESSION['usuario_rol']), ['admin', 'administrador', 'duenio', 'dueño'])): ?>
                                            <a href="../php/eliminar_pagos.php?id=<?php echo $f['idpagos']; ?>" class="btn-eliminar" onclick="return confirm('¿Deseas eliminar este pago?')">Eliminar</a>
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

    <script src="../js/menu_desplegable.js"></script>
    <script src="../js/calculos_pagos.js?v=1"></script>
    <script src="../js/alerta_cliente.js"></script>

</body>
</html>
