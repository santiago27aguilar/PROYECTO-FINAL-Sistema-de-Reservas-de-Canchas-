<?php
include 'conexion.php';
include 'estilos_excel.php';

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Reporte_Reservas_" . date('Y-m-d') . ".xls");

echo $bom . $estilo_unico;
?> 
<table border="1">
    <tr><th colspan="6" class="titulo-principal">LISTA DE TODAS LAS RESERVAS</th></tr>
    <tr>
        <th class="cabecera-tabla">Cliente</th>
        <th class="cabecera-tabla">Cancha</th>
        <th class="cabecera-tabla">Fecha</th>
        <th class="cabecera-tabla">Horario Inicio</th>
        <th class="cabecera-tabla">Horario Fin</th>
        <th class="cabecera-tabla">WhatsApp/Tel</th>
    </tr>
    <?php
    $sql = "SELECT c.nombre, c.apellido, ca.tipo_cancha, r.hora_inicio, r.hora_fin, c.telefono FROM reservas r 
            LEFT JOIN clientes c ON r.clientes_idclientes = c.idclientes 
            JOIN cancha ca ON r.cancha_idcancha = ca.idcancha ORDER BY r.hora_inicio DESC";
    $stmt = $conexion->query($sql);
    while($r = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
    <tr>
        <td class="celda-datos"><?php echo $r['nombre'] . " " . $r['apellido']; ?></td>
        <td class="celda-datos"><?php echo $r['tipo_cancha']; ?></td>
        <td class="celda-datos"><?php echo date("d-m-Y", strtotime($r['hora_inicio'])); ?></td>
        <td class="celda-datos"><?php echo date("H:i", strtotime($r['hora_inicio'])); ?> hs</td>
        <td class="celda-datos"><?php echo date("H:i", strtotime($r['hora_fin'])); ?> hs</td>
        <td class="celda-datos"><?php echo $r['telefono']; ?></td>
    </tr>
    <?php } ?>
</table>
