<?php
include 'conexion.php';
include 'estilos_excel.php';

$busqueda = isset($_GET['buscar']) ? $_GET['buscar'] : '';

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Reporte_Clientes_" . date('Y-m-d') . ".xls");
 
echo $bom . $estilo_unico;
?>
<table border="1">
    <tr><th colspan="5" class="titulo-principal">LISTADO DE CLIENTES REGISTRADOS</th></tr>
    <tr>
        <th class="cabecera-tabla">Nombre</th>
        <th class="cabecera-tabla">Apellido</th>
        <th class="cabecera-tabla">DNI</th>
        <th class="cabecera-tabla">Telefono</th>
        <th class="cabecera-tabla">Correo</th>
    </tr>
    <?php
    $sql = "SELECT nombre, apellido, dni, telefono, correo FROM clientes 
            WHERE dni LIKE :busqueda OR nombre LIKE :busqueda OR apellido LIKE :busqueda";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':busqueda' => "%$busqueda%"]);
    while($c = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
    <tr>
        <td class="celda-datos"><?php echo $c['nombre']; ?></td>
        <td class="celda-datos"><?php echo $c['apellido']; ?></td>
        <td class="celda-datos"><?php echo $c['dni']; ?></td>
        <td class="celda-datos"><?php echo $c['telefono']; ?></td>
        <td class="celda-datos"><?php echo $c['correo']; ?></td>
    </tr>
    <?php } ?>
</table>
