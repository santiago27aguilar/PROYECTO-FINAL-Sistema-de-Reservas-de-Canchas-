<?php
session_start();
if(!isset($_SESSION['usuario_nombre'])){ header("Location: ../html/login.php"); exit(); }

require_once 'conexion.php';
include 'estilos_excel.php'; // LLAMAMOS AL ESTILO

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Historial_Pagos_" . date('Y-m-d') . ".xls");

echo $bom . $estilo_unico;
?>
<table border="1">
    <thead>
        <tr><th colspan="5" class="titulo-principal">HISTORIAL DE TODOS LOS PAGOS - CANCHAS</th></tr>
        <tr>
            <th class="cabecera-tabla">Nro de Pago</th>
            <th class="cabecera-tabla">Monto Pagado</th>
            <th class="cabecera-tabla">Método de Pago</th>
            <th class="cabecera-tabla">Cliente</th>
            <th class="cabecera-tabla">Cancha Reservada</th>
        </tr> 
    </thead>
    <tbody>
        <?php
        $sql = "SELECT pagos.idpagos, pagos.monto, pagos.metodo_pago, clientes.nombre, clientes.apellido, cancha.tipo_cancha 
                FROM pagos 
                JOIN reservas ON pagos.reservas_idreservas = reservas.idreservas 
                JOIN clientes ON reservas.clientes_idclientes = clientes.idclientes 
                JOIN cancha ON reservas.cancha_idcancha = cancha.idcancha 
                ORDER BY pagos.idpagos DESC";
        $resPagos = $conexion->query($sql);
        while($f = $resPagos->fetch(PDO::FETCH_ASSOC)) { ?>
        <tr>
            <td class="celda-datos">#<?php echo $f['idpagos']; ?></td>
            <td class="celda-datos">$<?php echo number_format($f['monto'], 2); ?></td>
            <td class="celda-datos"><?php echo ucfirst($f['metodo_pago']); ?></td>
            <td class="celda-datos"><?php echo $f['nombre'] . " " . $f['apellido']; ?></td>
            <td class="celda-datos"><?php echo $f['tipo_cancha']; ?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>
