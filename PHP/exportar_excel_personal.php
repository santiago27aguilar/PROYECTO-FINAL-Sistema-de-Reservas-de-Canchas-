<?php
session_start();
if (!isset($_SESSION['usuario_rol']) || !in_array(strtolower($_SESSION['usuario_rol']), ['duenio', 'dueño'])) {
    exit("Acceso denegado");
}

include 'conexion.php';
include 'estilos_excel.php';

$busqueda = isset($_GET['buscar']) ? $_GET['buscar'] : '';

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Reporte_Personal_" . date('Y-m-d') . ".xls");

echo $bom . $estilo_unico;
?>
<table border="1">
    <tr><th colspan="4" class="titulo-principal">REPORTE DE PERSONAL REGISTRADO</th></tr>
    <tr>
        <th class="cabecera-tabla">Usuario</th>
        <th class="cabecera-tabla">Rol</th>
        <th class="cabecera-tabla">Correo</th>
        <th class="cabecera-tabla">Estado</th>
    </tr>
    <?php
    $sql = "SELECT nombre, rol, correo, estado FROM usuario 
            WHERE nombre LIKE :busqueda OR correo LIKE :busqueda OR rol LIKE :busqueda 
            ORDER BY idusuario ASC";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':busqueda' => "%$busqueda%"]);
    
    while ($p = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
    <tr>
        <td class="celda-datos"><?php echo htmlspecialchars($p['nombre']); ?></td>
        <td class="celda-datos"><?php echo htmlspecialchars($p['rol']); ?></td>
        <td class="celda-datos"><?php echo htmlspecialchars($p['correo']); ?></td>
        <td class="celda-datos"><?php echo htmlspecialchars($p['estado']); ?></td>
    </tr>
    <?php } ?>
</table>
