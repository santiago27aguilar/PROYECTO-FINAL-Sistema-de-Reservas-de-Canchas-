<?php
session_start();

if (!isset($_SESSION['usuario_rol'])) {
    header("Location: login.php");
    exit();
}

// Bloqueo de seguridad: Solo el dueño puede entrar a esta pantalla
$rol_actual = strtolower($_SESSION['usuario_rol']);
if (!in_array($rol_actual, ['duenio', 'dueño'])) {
    header("Location: inicio.php?error=sin_permisos");
    exit();
}

require_once '../php/conexion.php'; 

try {
    // Consulta 1: Solo Empleados Activos
    $stmtActivos = $conexion->query("SELECT idusuario, nombre, rol, correo FROM usuario WHERE estado = 'Activo' AND idusuario != 1 ORDER BY idusuario ASC");
    $listaActivos = $stmtActivos->fetchAll(PDO::FETCH_ASSOC);

    // Consulta 2: Solo Empleados Inactivos
    $stmtInactivos = $conexion->query("SELECT idusuario, nombre, rol, correo FROM usuario WHERE estado = 'Inactivo' AND idusuario != 1 ORDER BY idusuario ASC");
    $listaInactivos = $stmtInactivos->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die("Error al cargar la lista de empleados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Personal - Sistema de Reservas</title>
    <!-- CSS enlazado limpiamente (v=4 para limpiar caché) -->
    <link rel="stylesheet" href="../css/estilos_personal.css?v=4"> 
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
                    elseif ($_GET['mensaje'] == 'clave_reseteada') echo "Clave reseteada a 1234 con éxito.";
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
                <div>
                    <label for="nombre">Nombre de Usuario: <span class="asterisco">*</span></label>
                    <input type="text" id="nombre" name="nombre" placeholder="Ej: santiago.aguilar" autocomplete="off" required>
                </div>
                
                <div>
                    <label for="password">Contraseña: <span class="asterisco">*</span></label>
                    <input type="password" id="password" name="password" placeholder="Mínimo 4 caracteres" autocomplete="new-password" required>
                </div>

                <div>
                    <label for="correo">Correo Electrónico: <span class="asterisco">*</span></label>
                    <input type="email" id="correo" name="correo" placeholder="ejemplo@correo.com" autocomplete="off" required>
                </div>
            </div>
            
            <div class="form-grupo-unico">
                <label for="rol">Rol / Permisos del Sistema: <span class="asterisco">*</span></label>
                <select id="rol" name="rol" required>
                    <option value="" disabled selected>Selecciona un rol...</option>
                    <option value="Administrador">Administrador (Acceso total)</option>
                    <option value="Recepcionista">Recepcionista (Solo turnos y clientes)</option>
                </select>
            </div>
            
            <button type="submit" class="btn-guardar btn-full">Registrar Empleado</button>
        </form>
    </div>

    <div class="card-blanca seccion-clientes">
        <h2>Empleados Activos</h2>
        
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
                    <?php if (empty($listaActivos)): ?>
                        <tr><td colspan="4">No hay empleados activos en el sistema.</td></tr>
                    <?php else: ?>
                        <?php foreach ($listaActivos as $usuario): ?>
                            <tr>
                                <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                                <td><span class="punto-activo">●</span> <?= htmlspecialchars($usuario['rol']) ?></td>
                                <td><?= !empty($usuario['correo']) ? htmlspecialchars($usuario['correo']) : '<span class="sin-correo">Sin correo asignado</span>' ?></td>
                                <td>
                                    <div class="acciones-flex">
                                        <?php if (strtolower($usuario['rol']) == 'duenio' || strtolower($usuario['rol']) == 'dueño'): ?>
                                            <span class="sin-permisos">Cuenta Principal</span>
                                        <?php else: ?>
                                            <?php if (isset($_SESSION['usuario_rol']) && in_array(strtolower($_SESSION['usuario_rol']), ['duenio', 'dueño'])): ?>
                                                
                                                <button type="button" class="btn-editar btn-accion-editar" 
                                                        onclick="abrirModal(<?= $usuario['idusuario'] ?>, '<?= htmlspecialchars($usuario['nombre']) ?>', '<?= htmlspecialchars($usuario['rol']) ?>', '<?= htmlspecialchars($usuario['correo'] ?? '') ?>')">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>

                                                <a href="../php/resetear_clave.php?id=<?= $usuario['idusuario'] ?>" class="btn-editar" onclick="return confirm('¿Seguro que querés resetear la clave a 1234?');">
                                                    <i class="fas fa-key"></i> Clave
                                                </a>
                                                <a href="../php/eliminar_usuario.php?id=<?= $usuario['idusuario'] ?>" class="btn-eliminar btn-suspender" onclick="return confirm('¿Estás seguro de suspender a este empleado?');">
                                                    <i class="fas fa-user-slash"></i>Susp</a>
                                            <?php else: ?>
                                                <span class="sin-permisos">Solo lectura</span>
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
    </div>

    <div class="card-blanca seccion-clientes seccion-inactivos">
        <h2 class="titulo-inactivos">Empleados Inactivos (Suspendidos)</h2>
        <div class="table-responsive-wrapper">
            <table class="tabla-moderna tabla-inactivos">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Correo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($listaInactivos)): ?>
                        <tr><td colspan="4">No hay empleados suspendidos.</td></tr>
                    <?php else: ?>
                        <?php foreach ($listaInactivos as $usuario): ?>
                            <tr>
                                <td><del><?= htmlspecialchars($usuario['nombre']) ?></del></td>
                                <td><span class="punto-inactivo">●</span> <?= htmlspecialchars($usuario['rol']) ?></td>
                                <td><?= !empty($usuario['correo']) ? htmlspecialchars($usuario['correo']) : '<span class="sin-correo">Sin correo</span>' ?></td>
                                <td>
                                    <div class="acciones-flex">
                                        <a href="../php/reactivar_usuario.php?id=<?= $usuario['idusuario'] ?>" class="btn-editar btn-reactivar" onclick="return confirm('¿Restaurar el acceso?');">
                                            <i class="fas fa-user-check"></i> Reactivar
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="modalEditar" class="modal">
    <div class="modal-content">
        <span class="close" onclick="cerrarModal()">&times;</span>
        <h2>Editar Empleado</h2>
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
                    <option value="Administrador">Administrador</option>
                    <option value="Recepcionista">Recepcionista</option>
                </select>
            </div>
            
            <button type="submit" class="btn-guardar btn-full">Guardar Cambios</button>
        </form>
    </div>
</div>

<script src="../js/menu_desplegable.js"></script>
<script src="../js/modal_personal.js"></script>

</body>
</html>
