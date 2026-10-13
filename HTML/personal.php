<?php
    session_start();
    if(!isset($_SESSION['usuario_rol'])){
        header("Location: login.php");
        exit();
    }

    // Bloque de seguridad: Solo el dueño puede entrar a esta pantalla
    $rol_actual = strtolower($_SESSION['usuario_rol']);
    if (!in_array($rol_actual, ['duenio', 'dueño'])) {
        header("Location: inicio.php?error=sin_permisos");
        exit();
    }

    require_once '../php/conexion.php'; 

    // CAPTURAR FILTROS
    $busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
    $f_rol = isset($_GET['f_rol']) ? $_GET['f_rol'] : 'Todos';
    $f_estado = isset($_GET['f_estado']) ? $_GET['f_estado'] : 'Activo'; 

    // ARMAR CONDICIONES
    $where = " WHERE 1=1 ";
    $params = [];

    if ($busqueda !== '') {
        $where .= " AND (nombre LIKE :busqueda OR correo LIKE :busqueda) ";
        $params[':busqueda'] = "%$busqueda%";
    }

    if ($f_rol !== 'Todos') {
        $where .= " AND rol = :rol ";
        $params[':rol'] = $f_rol;
    }

    if ($f_estado === 'Activo') {
        $where .= " AND estado = 'Activo' ";
    } elseif ($f_estado === 'Inactivo') {
        $where .= " AND estado = 'Inactivo' ";
    }

    // CONFIGURACIÓN DE PAGINACIÓN
    $registros_por_pagina = 5;
    $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    if ($pagina_actual < 1) $pagina_actual = 1;
    $offset = ($pagina_actual - 1) * $registros_por_pagina;

    // CALCULAR TOTAL PARA PAGINACIÓN
    $sql_total = "SELECT COUNT(*) as total FROM usuario $where";
    $stmt_total = $conexion->prepare($sql_total);
    $stmt_total->execute($params);
    $total_registros = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'];
    $total_paginas = ceil($total_registros / $registros_por_pagina);

    try {
        // CONSULTA PRINCIPAL CON FILTROS Y PAGINACIÓN
        $sql_usuarios = "SELECT idusuario, nombre, rol, correo, estado FROM usuario $where ORDER BY CASE rol WHEN 'Duenio' THEN 1 WHEN 'Administrador' THEN 2 WHEN 'Recepcionista' THEN 3 ELSE 4 END ASC, idusuario ASC LIMIT $registros_por_pagina OFFSET $offset";
        $stmtUsuarios = $conexion->prepare($sql_usuarios);
        $stmtUsuarios->execute($params);
        $listaUsuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);

    } catch(PDOException $e) {
        die("Error al cargar la lista de empleados: " . $e->getMessage());
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión del Personal - Planeta de Futbol</title>
    <?php include 'head_comun.php'; ?>
    <link rel="stylesheet" href="../css/estilos_personal.css?v=13"> 
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
            <a href="personal.php" class="link-activo">Personal</a>
            <a href="calendario.php">Calendario</a> 
        <?php endif; ?>
        <a href="inicio.php">Clientes</a>
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
    <div class="header-titulo">
        <h1>GESTION DEL PERSONAL</h1>
    </div>
    <p class="bienvenida">Panel de control de usuarios y permisos</p>

    <div class="card-blanca">
        <h2>REGISTRAR NUEVO EMPLEADO</h2>
        
        <?php if (isset($_GET['mensaje'])): ?>
            <div class="alerta alerta-exito">
                <i class="fas fa-check-circle"></i> 
                <?php 
                    if ($_GET['mensaje'] == 'exito') echo "¡Empleado registrado con éxito en el sistema!";
                    elseif ($_GET['mensaje'] == 'eliminado') echo "Empleado suspendido correctamente.";
                    elseif ($_GET['mensaje'] == 'reactivado') echo "Empleado reactivado con éxito.";
                    elseif ($_GET['mensaje'] == 'clave_reseteada') echo "Clave reseteada a 1234 (por defecto) con éxito.";
                    elseif ($_GET['mensaje'] == 'actualizado') echo "Datos del empleado actualizados correctamente.";
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alerta alerta-error">
                <i class="fas fa-exclamation-triangle"></i>
                <?php 
                    if ($_GET['error'] == 'duplicado') echo "Ese nombre de usuario ya existe. Elegí otro.";
                    elseif ($_GET['error'] == 'vacios') echo "Por favor, completá todos los campos.";
                    elseif ($_GET['error'] == 'no_borrar_admin') echo "No podés suspender a la cuenta principal.";
                    elseif ($_GET['error'] == 'fallo_db') echo "Error al conectar con la base de datos.";
                    else echo "Ocurrió un error al procesar la solicitud.";
                ?>
            </div>
        <?php endif; ?>
        
        <form action="../php/guardar_usuario.php" method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label for="nombre">Nombre de Usuario:</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Ej: santiago.aguilar" class="input-form" autocomplete="off" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" placeholder="Mínimo 4 caracteres" class="input-form" autocomplete="new-password" required>
                </div>

                <div class="form-group">
                    <label for="correo">Correo Electrónico:</label>
                    <input type="email" id="correo" name="correo" placeholder="ejemplo@correo.com" class="input-form" autocomplete="off" required>
                </div>

                <div class="form-group">
                    <label for="rol">Rol / Permisos del Sistema:</label>
                    <select id="rol" name="rol" class="input-form" required>
                        <option value="" disabled selected>Selecciona un rol...</option>
                        <option value="Duenio">Dueño (Acceso total / Delegar)</option>
                        <option value="Administrador">Administrador</option>
                        <option value="Recepcionista">Recepcionista (Solo turnos y clientes)</option>
                    </select>
                </div>
            </div>

            <!--<span class="asterisco">*</span>-->
            
            <button type="submit" class="btn-guardar btn-full">Registrar Empleado</button>
        </form>
    </div>

    <div class="seccion-clientes">
        
        <!-- EL TÍTULO SOLO Y CENTRADO ARRIBA -->
        <div class="header-lista">
            <h2>LISTADO DEL PERSONAL</h2>
        </div>

        <form action="personal.php" method="GET" id="formFiltros">
            
            <!-- EXCEL - BUSCADOR - PDF (IGUAL QUE EN LA IMAGEN) -->
            <div class="herramientas-tabla">
                <a href="../php/exportar_excel_personal.php" class="btn-exportar btn-excel">EXCEL</a>
                
                <input type="text" name="buscar" id="buscarPersonal" class="buscador-moderno" placeholder="Buscar por usuario o correo..." value="<?php echo htmlspecialchars($busqueda); ?>" autocomplete="off">
                
                <a href="../php/exportar_pdf_personal.php" class="btn-exportar btn-pdf">PDF</a>
            </div>

            <!-- FILTROS SECUNDARIOS -->
            <div class="barra-filtros">
                <div class="grupo-filtro">
                    <label>Filtrar por Rol:</label>
                    <select name="f_rol" onchange="document.getElementById('formFiltros').submit();">
                        <option value="Todos" <?php echo ($f_rol == 'Todos') ? 'selected' : ''; ?>>Todos los roles</option>
                        <option value="Duenio" <?php echo ($f_rol == 'Duenio') ? 'selected' : ''; ?>>Dueño</option>
                        <option value="Administrador" <?php echo ($f_rol == 'Administrador') ? 'selected' : ''; ?>>Administrador</option>
                        <option value="Recepcionista" <?php echo ($f_rol == 'Recepcionista') ? 'selected' : ''; ?>>Recepcionista</option>
                    </select>
                </div>

                <div class="grupo-filtro">
                    <label>Estado del Empleado:</label>
                    <select name="f_estado" onchange="document.getElementById('formFiltros').submit();">
                        <option value="Activo" <?php echo ($f_estado == 'Activo') ? 'selected' : ''; ?>>Activos</option>
                        <option value="Inactivo" <?php echo ($f_estado == 'Inactivo') ? 'selected' : ''; ?>>Suspendidos</option>
                        <option value="Todos" <?php echo ($f_estado == 'Todos') ? 'selected' : ''; ?>>Todos los estados</option>
                    </select>
                </div>
                
                <?php if($busqueda !== '' || $f_rol !== 'Todos' || $f_estado !== 'Activo'): ?>
                    <div class="grupo-botones-filtro">
                        <a href="personal.php" class="btn-limpiar"><i class="fas fa-times"></i> Limpiar</a>
                    </div>
                <?php endif; ?>
            </div>

        </form>
        
        <div class="table-responsive-wrapper">
            <table class="tabla-moderna">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Correo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($listaUsuarios)): ?>
                        <tr><td colspan="4" style="text-align: center;">No se encontraron empleados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($listaUsuarios as $usuario): 
                            $estado_actual = isset($usuario['estado']) ? $usuario['estado'] : 'Activo';
                            $es_inactivo = (strtolower($estado_actual) === 'inactivo');
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                                <td>
                                    <?php if($es_inactivo): ?>
                                        <span class="punto-inactivo">●</span> 
                                    <?php else: ?>
                                        <span class="punto-activo">●</span> 
                                    <?php endif; ?>
                                    <?= htmlspecialchars($usuario['rol']) ?>
                                </td>
                                <td><?= !empty($usuario['correo']) ? htmlspecialchars($usuario['correo']) : '<span class="sin-correo">Sin correo</span>' ?></td>
                                <td>
                                    <div class="acciones-flex">
                                        <?php if (strtolower($usuario['nombre']) === 'duenio'): ?>
                                            <span class="sin-permisos">CUENTA PRINCIPAL</span>
                                        <?php else: ?>
                                            <?php if($es_inactivo): ?>
                                                <a href="../php/reactivar_usuario.php?id=<?= $usuario['idusuario'] ?>" class="btn-editar btn-reactivar" onclick="return confirm('¿Restaurar el acceso?');">
                                                    <i class="fas fa-user-check"></i> Reactivar
                                                </a>
                                            <?php else: ?>
                                                <button type="button" class="btn-editar btn-accion-editar" 
                                                        onclick="abrirModal(<?= $usuario['idusuario'] ?>, '<?= htmlspecialchars($usuario['nombre']) ?>', '<?= htmlspecialchars($usuario['rol']) ?>', '<?= htmlspecialchars($usuario['correo'] ?? '') ?>')">
                                                    <i class="fas fa-edit"></i> Editar
                                                </button>

                                                <a href="../php/resetear_clave.php?id=<?= $usuario['idusuario'] ?>" class="btn-editar" onclick="return confirm('¿Seguro que querés resetear la clave a 1234?');">
                                                    <i class="fas fa-key"></i> Contraseña
                                                </a>
                                                
                                                <a href="../php/eliminar_usuario.php?id=<?= $usuario['idusuario'] ?>" class="btn-eliminar btn-suspender" onclick="return confirm('¿Estás seguro de suspender a este empleado?');">
                                                    <i class="fas fa-user-slash"></i> Suspender
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($total_paginas > 1): ?>
            <div class="paginacion-wrapper">
                <?php
                    $url_busqueda = "&buscar=".urlencode($busqueda)."&f_rol=".urlencode($f_rol)."&f_estado=".urlencode($f_estado);
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

<!-- Modal -->
<div id="modalEditar" class="modal">
    <div class="modal-content">
        <span class="close" onclick="cerrarModal()">&times;</span>
        <h2>EDITAR EMPLEADO</h2>
        <form action="../php/actualizar_usuario.php" method="POST" class="form-modal">
            <input type="hidden" id="edit_id" name="idusuario">
            
            <div>
                <label for="edit_nombre">Nombre de Usuario:</label>
                <input type="text" id="edit_nombre" name="nombre" required>
            </div>

            <div>
                <label for="edit_correo">Correo Electrónico:</label>
                <input type="email" id="edit_correo" name="correo" required>
            </div>
            
            <div>
                <label for="edit_rol">Rol / Permisos:</label>
                <select id="edit_rol" name="rol" required>
                    <option value="Duenio">Dueño (Delegar Acceso Total)</option>
                    <option value="Administrador">Administrador</option>
                    <option value="Recepcionista">Recepcionista</option>
                </select>
            </div>
            
            <button type="submit" class="btn-guardar btn-full">Guardar</button>
        </form>
    </div>
</div>

<script src="../js/menu_desplegable.js"></script>
<script src="../js/modal_personal.js"></script>
<script src="../js/alerta_cliente.js"></script>
<script src="../js/buscador_personal.js"></script>

</body>
</html>
