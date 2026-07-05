<?php
session_start();

if (!isset($_SESSION['id_cliente'])) {
    header("Location: login_cliente.php");
    exit();
}

require '../php/conexion.php';

$id_cliente = $_SESSION['id_cliente'];
$nombre_cliente = $_SESSION['cliente_nombre'];

try {
    $sql = "SELECT r.idreservas, r.hora_inicio, r.hora_fin, r.estado, c.tipo_cancha 
            FROM reservas r 
            JOIN cancha c ON r.cancha_idcancha = c.idcancha 
            WHERE r.clientes_idclientes = :id_cliente 
            ORDER BY r.hora_inicio DESC";
            
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':id_cliente' => $id_cliente]);
    $mis_reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error al cargar el historial: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Turnos - Pampa Fútbol</title>
    <!-- Vinculamos el nuevo archivo CSS exclusivo -->
    <link rel="stylesheet" href="../css/estilos_historial_reservas.css"> 
</head>
<body>

<div class="reserva-card">
    <h2>HISTORIAL COMPLETO</h2>
    <p>
        Aquí podés ver todos los turnos que solicitaste, <b><?= htmlspecialchars($nombre_cliente) ?></b>.
    </p>

    <div style="overflow-x: auto;">
        <table class="tabla-reservas">
            <thead>
                <tr>
                    <th>Cancha</th>
                    <th>Fecha</th>
                    <th>Horario</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($mis_reservas)): ?>
                    <tr><td colspan="4" style="text-align: center;">No tenés reservas en tu historial.</td></tr>
                <?php else: ?>
                    <?php foreach ($mis_reservas as $reserva): 
                        $fecha_reserva = date('d/m/Y', strtotime($reserva['hora_inicio']));
                        $hora_inicio_formateada = date('H:i', strtotime($reserva['hora_inicio']));
                        $hora_fin_formateada = date('H:i', strtotime($reserva['hora_fin']));
                        
                        $clase_estado = '';
                        $estado = strtolower($reserva['estado']);
                        if ($estado == 'pendiente') $clase_estado = 'estado-pendiente';
                        elseif ($estado == 'confirmado' || $estado == 'confirmada') $clase_estado = 'estado-confirmado';
                        else $clase_estado = 'estado-cancelado';
                    ?>
                        <tr>
                            <td><b><?= htmlspecialchars($reserva['tipo_cancha']) ?></b></td>
                            <td><?= $fecha_reserva ?></td>
                            <td><?= $hora_inicio_formateada ?> a <?= $hora_fin_formateada ?> hs</td>
                            <td class="<?= $clase_estado ?>"><?= strtoupper($reserva['estado']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="contenedor-boton">
        <a href="mis_reservas.php" class="btn-volver">VOLVER AL BUSCADOR</a>
    </div>
</div>

</body>
</html>
