<?php
    session_start();
    if(!isset($_SESSION['id_usuario'])){
        header("Location: login.php");
        exit();
    }
    include '../php/conexion.php';
    
    $rol_usuario = $_SESSION['usuario_rol'];

    // CAPTURAR FILTROS
    $busqueda = isset($_GET['buscar']) ? $_GET['buscar'] : '';
    $f_estado = isset($_GET['f_estado']) ? $_GET['f_estado'] : 'Completado'; 
    $f_metodo = isset($_GET['f_metodo']) ? $_GET['f_metodo'] : 'Todos'; 
    $f_fecha = isset($_GET['f_fecha']) ? $_GET['f_fecha'] : '';

    // ARMAR CONDICIONES
    $where = " WHERE 1=1 ";
    $params = [];

    if ($busqueda !== '') {
        $where .= " AND (clientes.nombre LIKE :busqueda OR clientes.apellido LIKE :busqueda) ";
        $params[':busqueda'] = "%$busqueda%";
    }

    if ($f_estado === 'Completado') {
        $where .= " AND (pagos.estado = 'Completado' OR pagos.estado IS NULL) ";
    } elseif ($f_estado === 'Anulado') {
        $where .= " AND pagos.estado = 'Anulado' ";
    }

    if ($f_metodo !== 'Todos') {
        $where .= " AND pagos.metodo_pago = :metodo ";
        $params[':metodo'] = $f_metodo;
    }

    if ($f_fecha !== '') {
        $where .= " AND DATE(pagos.fecha_pago) = :fecha ";
        $params[':fecha'] = $f_fecha;
    }

    // PAGINACIÓN
    $registros_por_pagina = 5;
    $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    if ($pagina_actual < 1) $pagina_actual = 1;
    $offset = ($pagina_actual - 1) * $registros_por_pagina;

    // TOTAL PARA PAGINACIÓN
    $sql_total = "SELECT COUNT(*) as total FROM pagos JOIN reservas ON pagos.reservas_idreservas = reservas.idreservas JOIN clientes ON reservas.clientes_idclientes = clientes.idclientes $where";
    $stmt_total = $conexion->prepare($sql_total);
    $stmt_total->execute($params);
    $total_registros = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'];
    $total_paginas = ceil($total_registros / $registros_por_pagina);

    // CONSULTA PRINCIPAL
    $sql_pagos = "SELECT pagos.*, clientes.nombre, clientes.apellido, cancha.tipo_cancha 
                  FROM pagos 
                  JOIN reservas ON pagos.reservas_idreservas = reservas.idreservas 
                  JOIN clientes ON reservas.clientes_idclientes = clientes.idclientes 
                  JOIN cancha ON reservas.cancha_idcancha = cancha.idcancha 
                  $where ORDER BY pagos.idpagos DESC LIMIT $registros_por_pagina OFFSET $offset";
    
    $stmt_pagos = $conexion->prepare($sql_pagos);
    $stmt_pagos->execute($params);
    $resultado_pagos = $stmt_pagos->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pagos - Planeta de Futbol</title>
    <?php include 'head_comun.php'; ?>
    <link rel="stylesheet" href="../css/estilos_pagos.css?v=18">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body> 

    <div class="navbar">
        <div class="menu-toggle" id="mobile-menu"><i class="fas fa-bars"></i></div>
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
            
            <?php if(isset($_SESSION['usuario_nombre'])): ?>
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
            
            <!-- TÍTULO Y LOGO ALINEADOS -->
            <div class="header-pagos">
                <img src="../img/flujo-de-efectivo.png" alt="Foto Pagos" class="img-cuadro-pagos">
                <h2>SECCION DE PAGOS</h2>
            </div>
            
            <?php if(isset($_GET['exito'])): ?>
                <div class='alerta alerta-exito'>Pago registrado con éxito!</div>
            <?php endif; ?>
            <?php if(isset($_GET['mensaje']) && $_GET['mensaje'] === 'anulado'): ?>
                <div class="alerta alerta-exito">Pago anulado correctamente</div>
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
                            <label>Selecciona una RESERVA</label>
                            <select name="id_reserva" id="id_reserva" onchange="actualizarCalculos()" required>
                                <option value="">> Elije una RESERVA <</option>
                                <?php
                                    $query = $conexion->query("SELECT r.idreservas, c.nombre, c.apellido, can.precio_hora, r.hora_inicio, r.hora_fin, TIMESTAMPDIFF(HOUR, r.hora_inicio, r.hora_fin) AS total_horas FROM reservas r JOIN clientes c ON r.clientes_idclientes = c.idclientes JOIN cancha can ON r.cancha_idcancha = can.idcancha WHERE r.estado = 'Confirmado'");
                                    while($reg = $query->fetch(PDO::FETCH_ASSOC)){
                                        echo "<option value='".$reg['idreservas']."' data-precio= '".$reg['precio_hora']."' data-horas='".$reg['total_horas']."'>".$reg['nombre']." ".$reg['apellido']."</option>";
                                    }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Metodos de PAGOS</label>
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

                <!-- SECCIÓN ALIAS Y BOTÓN QR -->
                <div id="seccion_alias" class="alias-caja">
                    <p><strong>ALIAS:</strong> planeta.futbol.padel</p>
                    <button type="button" id="btn-abrir-qr" class="btn-qr-modal">VER CÓDIGO QR</button>
                </div>

                <!-- VENTANA MODAL DEL QR (Con API QR dinámica) -->
                <div id="modal-qr" class="modal-overlay oculto">
                    <div class="modal-contenido">
                        <span id="btn-cerrar-qr" class="modal-cerrar">&times;</span>
                        <h3>CÓDIGO QR</h3>
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=https://link.mercadopago.com.ar/planetafutbol" alt="QR Transferencia" class="img-qr">
                        <p>Escaneá el código para completar la transferencia</p>
                    </div>
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
            
            <form action="pagos.php" method="GET" id="formFiltros">
                
                <div class="herramientas-tabla">
                    <a href="../php/exportar_excel_pagos.php" class="btn-exportar btn-excel">EXCEL</a>
                    <input type="text" name="buscar" id="buscarPago" class="buscador-moderno" placeholder="Buscar por nombre del cliente..." value="<?php echo htmlspecialchars($busqueda); ?>">
                    <a href="../php/exportar_pdf_pagos.php" class="btn-exportar btn-pdf">PDF</a>
                </div>

                <div class="barra-filtros">
                    <div class="grupo-filtro">
                        <label>Estado del Pago:</label>
                        <select name="f_estado" onchange="document.getElementById('formFiltros').submit();">
                            <option value="Completado" <?php echo ($f_estado == 'Completado') ? 'selected' : ''; ?>>Completados</option>
                            <option value="Anulado" <?php echo ($f_estado == 'Anulado') ? 'selected' : ''; ?>>Anulados</option>
                            <option value="Todos" <?php echo ($f_estado == 'Todos') ? 'selected' : ''; ?>>Todos</option>
                        </select>
                    </div>

                    <div class="grupo-filtro">
                        <label>Método:</label>
                        <select name="f_metodo" onchange="document.getElementById('formFiltros').submit();">
                            <option value="Todos" <?php echo ($f_metodo == 'Todos') ? 'selected' : ''; ?>>Todos</option>
                            <option value="Efectivo" <?php echo ($f_metodo == 'Efectivo') ? 'selected' : ''; ?>>Efectivo</option>
                            <option value="Transferencia" <?php echo ($f_metodo == 'Transferencia') ? 'selected' : ''; ?>>Transferencia</option>
                        </select>
                    </div>

                    <div class="grupo-filtro">
                        <label>Fecha de Pago:</label>
                        <input type="date" name="f_fecha" value="<?php echo htmlspecialchars($f_fecha); ?>" onchange="document.getElementById('formFiltros').submit();">
                    </div>

                    <?php if($busqueda !== '' || $f_estado !== 'Completado' || $f_metodo !== 'Todos' || $f_fecha !== ''): ?>
                        <div class="grupo-botones-filtro">
                             <a href="pagos.php" class="btn-limpiar"><i class="fas fa-times"></i> Limpiar</a>
                        </div>
                    <?php endif; ?>
                </div>

            </form>

            <div class="table-responsive-wrapper">
                <table class="tabla-moderna">
                    <thead>
                        <tr>
                            <th>Monto</th>
                            <th>Método</th>
                            <th>Detalle</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Cancha</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead> 
                    <tbody>
                        <?php if (empty($resultado_pagos)): ?>
                            <tr><td colspan="8" class="tabla-vacia-msj">No se encontraron pagos.</td></tr>
                        <?php else: ?>
                            <?php foreach($resultado_pagos as $f) { 
                                $es_anulado = (isset($f['estado']) && strtolower($f['estado']) === 'anulado');
                            ?>
                            <tr class="<?php echo $es_anulado ? 'tr-anulado' : ''; ?>">
                                <td><strong>$<?php echo number_format($f['monto'], 2); ?></strong></td>
                                <td><?php echo ucfirst($f['metodo_pago']); ?></td>
                                <td><?php echo !empty($f['detalle_senia']) ? $f['detalle_senia'] : '-'; ?></td>
                                <td><?php echo !empty($f['fecha_pago']) ? date("d/m/Y H:i", strtotime($f['fecha_pago'])) : '-'; ?></td>
                                <td><?php echo $f['nombre'] . " " . $f['apellido']; ?></td>
                                <td><?php echo $f['tipo_cancha']; ?></td>
                                <td class="estado-columna <?php echo $es_anulado ? 'estado-anulado' : 'estado-completado'; ?>">
                                    <?php echo $es_anulado ? 'ANULADO' : 'COMPLETADO'; ?>
                                </td>
                                <td>
                                    <div class="acciones-flex">
                                        <?php if (in_array(strtolower($_SESSION['usuario_rol']), ['admin', 'administrador', 'duenio', 'dueño'])): ?>
                                            <?php if (!$es_anulado): ?>
                                                <a href="../php/eliminar_pagos.php?id=<?php echo $f['idpagos']; ?>" class="btn-eliminar" onclick="return confirm('¿Deseas anular este pago? La reserva volverá a estar pendiente.')">Anular</a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="sin-permisos">Sin Permisos</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php } ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if($total_paginas > 1): ?>
                <div class="paginacion-wrapper">
                    <?php
                        $url_busqueda = "&buscar=".urlencode($busqueda)."&f_estado=".urlencode($f_estado)."&f_metodo=".urlencode($f_metodo)."&f_fecha=".urlencode($f_fecha);
                        if($pagina_actual > 1):
                    ?>
                        <a href="?pagina=<?php echo $pagina_actual - 1; ?><?php echo $url_busqueda; ?>" class="btn-pag">&laquo; Anterior</a>
                    <?php endif; ?>

                    <?php for($i = 1; $i <= $total_paginas; $i++): ?>
                        <a href="?pagina=<?php echo $i; ?><?php echo $url_busqueda; ?>" class="btn-pag <?php echo ($i == $pagina_actual) ? 'activo' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>

                    <?php if($pagina_actual < $total_paginas): ?>
                        <a href="?pagina=<?php echo $pagina_actual + 1; ?><?php echo $url_busqueda; ?>" class="btn-pag">Siguiente &raquo;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <script src="../js/menu_desplegable.js"></script>
    <script src="../js/calculos_pagos.js?v=1"></script>
    <script src="../js/alerta_cliente.js"></script>
    <script src="../js/modal_qr.js"></script>
</body>
</html>
