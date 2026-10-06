<?php
    session_start();
    if(!isset($_SESSION['usuario_nombre'])){
        header("Location: login.php");
        exit();
    }
    include '../php/conexion.php';

    $rol_usuario = $_SESSION['usuario_rol'];
    
    // CAPTURAR FILTROS
    $busqueda = isset($_GET['buscar']) ? $_GET['buscar'] : '';
    $f_estado = isset($_GET['f_estado']) ? $_GET['f_estado'] : 'Activo'; 
    
    // ARMAR LA CONDICIÓN DEL ESTADO
    $where_estado = "";
    if ($f_estado === 'Activo') {
        $where_estado = " AND estado = 'Activo'";
    } elseif ($f_estado === 'Inactivo') {
        $where_estado = " AND estado = 'Inactivo'";
    }

    // --- CONFIGURACIÓN DE PAGINACIÓN ---
    $registros_por_pagina = 5;
    $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    if ($pagina_actual < 1) $pagina_actual = 1;
    $offset = ($pagina_actual - 1) * $registros_por_pagina;

    // Calcular el total APLICANDO LOS FILTROS
    $sql_total = "SELECT COUNT(*) as total FROM clientes WHERE (dni LIKE :busqueda OR nombre LIKE :busqueda OR apellido LIKE :busqueda) $where_estado";
    $stmt_total = $conexion->prepare($sql_total);
    $stmt_total->execute([':busqueda' => "%$busqueda%"]);
    $total_registros = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'];
    $total_paginas = ceil($total_registros / $registros_por_pagina);

    // CONSULTA PRINCIPAL UNIFICADA
    $sql_clientes = "SELECT * FROM clientes WHERE (dni LIKE :busqueda OR nombre LIKE :busqueda OR apellido LIKE :busqueda) $where_estado LIMIT :limite OFFSET :offset";
    $stmt_clientes = $conexion->prepare($sql_clientes);
    $stmt_clientes->bindValue(':busqueda', "%$busqueda%", PDO::PARAM_STR);
    $stmt_clientes->bindValue(':limite', (int)$registros_por_pagina, PDO::PARAM_INT);
    $stmt_clientes->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt_clientes->execute();
    $resultado_clientes = $stmt_clientes->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Clientes - Planeta de Futbol</title>
    <?php include 'head_comun.php'; ?>
    <link rel="stylesheet" href="../css/estilos_inicio.css?v=11">
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
            
            <a href="inicio.php" class="link-activo">Clientes</a>
            <a href="reservas.php">Reservas</a>
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

        <?php if(isset($_GET['mensaje']) && $_GET['mensaje'] == 'reactivado'): ?>
            <div class="alerta alerta-exito">¡Cliente reactivado correctamente!</div>
        <?php endif; ?>

        <?php if(isset($_GET['mensaje']) && $_GET['mensaje'] == 'suspendido'): ?>
            <div class="alerta alerta-exito">¡Cliente suspendido correctamente!</div>
        <?php endif; ?>

        <?php if(isset($_GET['error']) && $_GET['error'] == 'sin_permisos'): ?>
            <div class="alerta alerta-error">No tienes permisos para realizar esta acción.</div>
        <?php endif; ?>
        
        <?php if(isset($_GET['error']) && $_GET['error'] == 'fallo_db'): ?>
            <div class="alerta alerta-error">Ocurrió un error en la base de datos al intentar procesar la solicitud.</div>
        <?php endif; ?>
        
        <div class="card-blanca">
            <h2>REGISTRAR NUEVO CLIENTE</h2>
            <form action="../php/registrar_cliente.php" method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nombre <span class="asterisco">*</span></label>
                        <input type="text" name="nombre" placeholder="Ej: Juan" required class="input-form">
                    </div>
                    <div class="form-group">
                        <label>Apellido <span class="asterisco">*</span></label>
                        <input type="text" name="apellido" placeholder="Ej: Pérez" required class="input-form">
                    </div>
                    <div class="form-group">
                        <label>DNI <span class="asterisco">*</span></label>
                        <input type="number" name="dni" placeholder="Sin puntos" class="input-form">
                    </div>
                    <div class="form-group">
                        <label>Teléfono <span class="asterisco">*</span></label>
                        <input type="text" name="telefono" placeholder="Ej: 381..." class="input-form">
                    </div>
                    <div class="form-group">
                        <label>Correo <span class="asterisco">*</span></label>
                        <input type="email" name="correo" placeholder="email@ejemplo.com" class="input-form">
                    </div>
                    <button type="submit" class="btn-guardar btn-full align-self-end">GUARDAR CLIENTE</button>
                </div>
            </form>
        </div>

        <div class="seccion-clientes">
            
            <!-- TÍTULO Y BOTONES DE EXPORTAR EN LA MISMA LÍNEA -->
            <div class="header-lista">
                <a href="../php/exportar_excel_inicio.php" class="btn-exportar btn-excel">EXCEL</a>
                <h2>LISTADO DE LOS CLIENTES</h2>
                <a href="../php/exportar_pdf_inicio.php" class="btn-exportar btn-pdf">PDF</a>
            </div>
            
            <form action="inicio.php" method="GET" id="formFiltros">
                
                <!-- FILTROS Y BUSCADOR -->
                <div class="barra-filtros">
                    
                    <div class="grupo-filtro filtro-largo">
                        <label>Buscar Cliente:</label>
                        <input type="text" name="buscar" placeholder="Buscar por DNI, Nombre o Apellido..." value="<?php echo htmlspecialchars($busqueda); ?>" autocomplete="off">
                    </div>

                    <div class="grupo-filtro">
                        <label>Estado del Cliente:</label>
                        <select name="f_estado" onchange="document.getElementById('formFiltros').submit();">
                            <option value="Activo" <?php echo ($f_estado == 'Activo') ? 'selected' : ''; ?>>Solo Activos</option>
                            <option value="Inactivo" <?php echo ($f_estado == 'Inactivo') ? 'selected' : ''; ?>>Suspendidos</option>
                            <option value="Todos" <?php echo ($f_estado == 'Todos') ? 'selected' : ''; ?>>Todos los estados</option>
                        </select>
                    </div>
                    
                    <div class="grupo-botones-filtro">
                        <button type="submit" style="display:none;">Buscar</button> 
                        <?php if($busqueda !== '' || $f_estado !== 'Activo'): ?>
                            <a href="inicio.php" class="btn-limpiar"><i class="fas fa-times"></i> Limpiar</a>
                        <?php endif; ?>
                    </div>
                </div>

            </form>

            <div class="table-responsive-wrapper">
                <table class="tabla-moderna">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>DNI</th>
                            <th>Teléfono</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($resultado_clientes)): ?>
                            <tr><td colspan="4" class="texto-centrado">No se encontraron clientes para este filtro.</td></tr>
                        <?php else: ?>
                            <?php foreach ($resultado_clientes as $fila) { 
                                $es_inactivo = (strtolower($fila['estado']) == 'inactivo');
                            ?>
                                <tr class="<?php echo $es_inactivo ? 'tabla-opaca' : ''; ?>">
                                    <td><?php echo htmlspecialchars($fila['nombre'] . ' ' . $fila['apellido']); ?></td>
                                    <td><strong><?php echo htmlspecialchars($fila['dni']);?></strong></td>
                                    <td><?php echo htmlspecialchars($fila['telefono']);?></td>
                                    <td>
                                        <div class="acciones-flex">
                                            <?php if(!$es_inactivo): ?>
                                                <a href="editar_cliente.php?id=<?php echo $fila['idclientes'];?>" class="btn-editar">Editar</a>
                                            <?php endif; ?>

                                            <?php if (in_array(strtolower($rol_usuario), ['admin', 'administrador', 'duenio', 'dueño'])): ?>
                                                <?php if($es_inactivo): ?>
                                                    <a href="../php/reactivar_cliente.php?id=<?php echo $fila['idclientes'];?>" class="btn-editar btn-reactivar" onclick="return confirm('¿Restaurar a este cliente?')">Reactivar</a>
                                                <?php else: ?>
                                                    <a href="../php/eliminar_cliente.php?id=<?php echo $fila['idclientes'];?>" class="btn-eliminar btn-suspender" onclick="return confirm('¿Deseas suspender a este cliente?')">Suspender</a>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="sin-permisos">Sin permisos</span>
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
                        $url_busqueda = "&buscar=".urlencode($busqueda)."&f_estado=".urlencode($f_estado);
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
    <script src="../js/alerta_cliente.js"></script>
    <script src="../js/buscador_inicio.js"></script>
</body>
</html>
