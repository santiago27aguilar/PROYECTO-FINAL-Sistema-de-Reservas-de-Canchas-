<?php
    session_start();
    if(!isset($_SESSION['usuario_nombre'])){
        header("Location: login.php");
        exit();
    }
    include '../php/conexion.php';

    $rol_usuario = $_SESSION['usuario_rol'];
    $busqueda = isset($_GET['buscar']) ? $_GET['buscar'] : '';
    
    // --- CONFIGURACIÓN DE PAGINACIÓN ---
    $registros_por_pagina = 5; 
    $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    if ($pagina_actual < 1) $pagina_actual = 1;
    $offset = ($pagina_actual - 1) * $registros_por_pagina;

    // Calcular el total de CLIENTES ACTIVOS para las páginas
    $sql_total = "SELECT COUNT(*) as total FROM clientes WHERE estado = 'Activo' AND (dni LIKE :busqueda OR nombre LIKE :busqueda OR apellido LIKE :busqueda)";
    $stmt_total = $conexion->prepare($sql_total);
    $stmt_total->execute([':busqueda' => "%$busqueda%"]);
    $total_registros = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'];
    $total_paginas = ceil($total_registros / $registros_por_pagina);

    // CONSULTA CLIENTES ACTIVOS (Añadimos parámetros PDO estrictos para LIMIT y OFFSET)
    $sql_activos = "SELECT * FROM clientes WHERE estado = 'Activo' AND (dni LIKE :busqueda OR nombre LIKE :busqueda OR apellido LIKE :busqueda) LIMIT :limite OFFSET :offset";
    $stmt_activos = $conexion->prepare($sql_activos);
    $stmt_activos->bindValue(':busqueda', "%$busqueda%", PDO::PARAM_STR);
    $stmt_activos->bindValue(':limite', (int)$registros_por_pagina, PDO::PARAM_INT);
    $stmt_activos->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt_activos->execute();
    $resultado_clientes = $stmt_activos->fetchAll(PDO::FETCH_ASSOC);

    // CONSULTA CLIENTES INACTIVOS (La dejamos igual, como un listado rápido)
    $sql_inactivos = "SELECT * FROM clientes WHERE estado = 'Inactivo' AND (dni LIKE :busqueda OR nombre LIKE :busqueda OR apellido LIKE :busqueda)";
    $stmt_inactivos = $conexion->prepare($sql_inactivos);
    $stmt_inactivos->execute([':busqueda' => "%$busqueda%"]);
    $resultado_inactivos = $stmt_inactivos->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Clientes - Pampa Fútbol</title>
    <!-- Actualizamos a v=2 para que el navegador lea los nuevos estilos css -->
    <link rel="stylesheet" href="../css/estilos_inicio.css?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    
    <div class="navbar">
        <div class="menu-toggle" id="mobile-menu"><i class="fas fa-bars"></i></div>
        <div class="nav-links" id="nav-links">
            
            <!-- 👑 COSAS QUE ***SOLO*** VE EL DUEÑO -->
            <?php if(isset($_SESSION['usuario_rol']) && in_array(strtolower($_SESSION['usuario_rol']), ['duenio', 'dueño'])): ?>
                <a href="dashboard.php">Tablero</a>
                <a href="personal.php">Personal</a>
                <a href="calendario.php">Calendario</a> 
            <?php endif; ?>
            
            <!-- 👥 COSAS QUE VEN TODOS (Dueño, Admin y Empleados) -->
            <a href="inicio.php" class="link-activo">Clientes</a> <!-- Acá está el link verde -->
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
        <!--<div class="header-titulo">
            <h1>PANEL DE CLIENTES</h1>
            <img src="../img/icono-usuario.png" alt="Icono Usuario" class="icono-usuario">
        </div>-->
        
        <div class="card-blanca">
            <h2>REGISTRAR NUEVO CLIENTE</h2>
            <form action="../php/registrar_cliente.php" method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nombre <span class="asterisco">*</span></label>
                        <input type="text" name="nombre" placeholder="Ej: Juan" required>
                    </div>
                    <div class="form-group">
                        <label>Apellido <span class="asterisco">*</span></label>
                        <input type="text" name="apellido" placeholder="Ej: Pérez" required>
                    </div>
                    <div class="form-group">
                        <label>DNI <span class="asterisco">*</span></label>
                        <input type="number" name="dni" placeholder="Sin puntos">
                    </div>
                    <div class="form-group">
                        <label>Teléfono <span class="asterisco">*</span></label>
                        <input type="text" name="telefono" placeholder="Ej: 381...">
                    </div>
                    <div class="form-group">
                        <label>Correo <span class="asterisco">*</span></label>
                        <input type="email" name="correo" placeholder="email@ejemplo.com">
                    </div>
                    <!-- Agregada la clase align-self-end en vez del style -->
                    <button type="submit" class="btn-guardar btn-full align-self-end">GUARDAR CLIENTE</button>
                </div>
            </form>
        </div>

        <div class="seccion-clientes">
            <h2>CLIENTES REGISTRADOS (ACTIVOS)</h2>
            <div class="contenedor-busqueda-disenio">
                <form method="GET" action="inicio.php" class="buscador-largo">
                    <input type="text" name="buscar" placeholder="Buscar por DNI o Nombre..." value="<?php echo htmlspecialchars($busqueda); ?>">
                </form>
                <div class="fila-botones-disenio">
                    <button type="submit" form="form-real" class="btn-disenio btn-verde">BUSCAR</button>
                    <a href="inicio.php" class="link-disenio"><button type="button" class="btn-disenio btn-rojo">LIMPIAR</button></a>
                </div>
            </div>

            <!-- Agregada la clase d-none en vez del style -->
            <form id="form-real" method="GET" action="inicio.php" class="d-none">
                <input type="hidden" name="buscar" value="<?php echo htmlspecialchars($busqueda); ?>">
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
                            <tr><td colspan="4" class="texto-centrado">No se encontraron clientes activos.</td></tr>
                        <?php else: ?>
                            <?php foreach ($resultado_clientes as $fila) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($fila['nombre'] . ' ' . $fila['apellido']); ?></td>
                                    <td><strong><?php echo htmlspecialchars($fila['dni']);?></strong></td>
                                    <td><?php echo htmlspecialchars($fila['telefono']);?></td>
                                    <td>
                                        <div class="acciones-flex">
                                            <a href="editar_cliente.php?id=<?php echo $fila['idclientes'];?>" class="btn-editar">Editar</a>
                                            <?php if (in_array(strtolower($rol_usuario), ['admin', 'administrador', 'duenio', 'dueño'])): ?>
                                                <!-- Agregada la clase btn-suspender en vez del style naranja -->
                                                <a href="../php/eliminar_cliente.php?id=<?php echo $fila['idclientes'];?>" class="btn-eliminar btn-suspender" onclick="return confirm('¿Deseas suspender a este cliente?')">Suspender</a>
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
                        $url_busqueda = !empty($busqueda) ? "&buscar=".urlencode($busqueda) : "";
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

        <!-- Agregada la clase mt-30 en vez del style -->
        <div class="seccion-clientes mt-30">
            <h2 style="color: #666;">CLIENTES INACTIVOS (SUSPENDIDOS)</h2>
            <div class="table-responsive-wrapper">
                <!-- Agregada la clase tabla-opaca en vez del style -->
                <table class="tabla-moderna tabla-opaca">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>DNI</th>
                            <th>Teléfono</th>
                            <th>Acciones</th> 
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($resultado_inactivos)): ?>
                            <tr><td colspan="4" class="texto-centrado">No hay clientes suspendidos.</td></tr>
                        <?php else: ?>
                            <?php foreach ($resultado_inactivos as $fila) { ?>
                                <tr>
                                    <td><del><?php echo htmlspecialchars($fila['nombre'] . ' ' . $fila['apellido']); ?></del></td>
                                    <td><strong><del><?php echo htmlspecialchars($fila['dni']);?></del></strong></td>
                                    <td><del><?php echo htmlspecialchars($fila['telefono']);?></del></td>
                                    <td>
                                        <div class="acciones-flex">
                                            <?php if (in_array(strtolower($rol_usuario), ['admin', 'administrador', 'duenio', 'dueño'])): ?>
                                                <!-- Agregada la clase btn-reactivar en vez del style verde -->
                                                <a href="../php/reactivar_cliente.php?id=<?php echo $fila['idclientes'];?>" class="btn-editar btn-reactivar" onclick="return confirm('¿Restaurar a este cliente?')">Reactivar</a>
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
        </div>

    </div>
    <script src="../js/menu_desplegable.js"></script>
</body>
</html>
