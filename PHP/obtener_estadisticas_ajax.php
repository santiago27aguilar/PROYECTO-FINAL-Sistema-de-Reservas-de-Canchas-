<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_rol']) || !in_array(strtolower($_SESSION['usuario_rol']), ['duenio', 'dueño'])) {
    echo json_encode(['error' => 'Sin permisos']);
    exit();
}

require_once 'conexion.php';

$esHistorico = isset($_GET['historico']) && $_GET['historico'] == 'true';
$desde = isset($_GET['desde']) ? $_GET['desde'] . ' 00:00:00' : null;
$hasta = isset($_GET['hasta']) ? $_GET['hasta'] . ' 23:59:59' : null;

try {
    $whereReservas = "";
    $wherePagos = "";
    $paramsReservas = [];
    $paramsPagos = [];

    if (!$esHistorico && $desde && $hasta) {
        $whereReservas = "WHERE hora_inicio BETWEEN :desde AND :hasta";
        $wherePagos = "WHERE fecha_pago BETWEEN :desde_pago AND :hasta_pago";
        $paramsReservas[':desde'] = $desde;
        $paramsReservas[':hasta'] = $hasta;
        $paramsPagos[':desde_pago'] = $desde;
        $paramsPagos[':hasta_pago'] = $hasta;
    } else {
        $whereReservas = "WHERE hora_inicio IS NOT NULL";
        $wherePagos = "WHERE fecha_pago IS NOT NULL";
    }

    // 1. Ganancias del período (tabla pagos)
    $stmtGanancias = $conexion->prepare("SELECT COALESCE(SUM(monto), 0) as total FROM pagos $wherePagos");
    $stmtGanancias->execute($paramsPagos);
    $gananciasPeriodo = $stmtGanancias->fetch(PDO::FETCH_ASSOC)['total'];

    // 2. Turnos del período (tabla reservas)
    $stmtTurnos = $conexion->prepare("SELECT COUNT(idreservas) as total FROM reservas $whereReservas");
    $stmtTurnos->execute($paramsReservas);
    $turnosPeriodo = $stmtTurnos->fetch(PDO::FETCH_ASSOC)['total'];

    // 3. Día más solicitado
    $stmtDia = $conexion->prepare("SELECT DAYOFWEEK(hora_inicio) as dia, COUNT(idreservas) as cantidad FROM reservas $whereReservas GROUP BY dia ORDER BY cantidad DESC LIMIT 1");
    $stmtDia->execute($paramsReservas);
    $rowDia = $stmtDia->fetch(PDO::FETCH_ASSOC);
    $diasSemana = [1 => 'Domingo', 2 => 'Lunes', 3 => 'Martes', 4 => 'Miércoles', 5 => 'Jueves', 6 => 'Viernes', 7 => 'Sábado'];
    $diaMasSolicitado = $rowDia ? $diasSemana[$rowDia['dia']] : 'Sin datos';

    // 4. Horario pico
    $stmtHora = $conexion->prepare("SELECT HOUR(hora_inicio) as hora, COUNT(idreservas) as cantidad FROM reservas $whereReservas GROUP BY hora ORDER BY cantidad DESC LIMIT 1");
    $stmtHora->execute($paramsReservas);
    $rowHora = $stmtHora->fetch(PDO::FETCH_ASSOC);
    $horarioPico = $rowHora ? $rowHora['hora'] . ':00 hs' : 'Sin datos';

    // 5. Cancha más usada
    $stmtCancha = $conexion->prepare("
        SELECT c.tipo_cancha, COUNT(r.idreservas) as cantidad 
        FROM reservas r 
        JOIN cancha c ON r.cancha_idcancha = c.idcancha 
        $whereReservas 
        GROUP BY c.idcancha 
        ORDER BY cantidad DESC LIMIT 1
    ");
    $stmtCancha->execute($paramsReservas);
    $rowCancha = $stmtCancha->fetch(PDO::FETCH_ASSOC);
    $canchaMasUsada = $rowCancha ? $rowCancha['tipo_cancha'] : 'Sin datos';

    // 6. Mejores Clientes
    $condicionPeriodo = $esHistorico ? "1=1" : "r.hora_inicio BETWEEN :desde_cliente AND :hasta_cliente";
    $sqlClientes = "
        SELECT c.nombre, c.apellido, 
               COUNT(r.idreservas) as total_historico, 
               SUM(CASE WHEN $condicionPeriodo THEN 1 ELSE 0 END) as total_periodo 
        FROM reservas r 
        JOIN clientes c ON r.clientes_idclientes = c.idclientes 
        GROUP BY c.idclientes 
        ORDER BY total_periodo DESC, total_historico DESC 
        LIMIT 3
    ";
    
    $stmtClientes = $conexion->prepare($sqlClientes);
    $paramsClientes = [];
    if (!$esHistorico && $desde && $hasta) {
        $paramsClientes[':desde_cliente'] = $desde;
        $paramsClientes[':hasta_cliente'] = $hasta;
    }
    $stmtClientes->execute($paramsClientes);
    $topClientes = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'ganancias_periodo' => number_format($gananciasPeriodo, 0, ',', '.'),
        'turnos_periodo' => $turnosPeriodo,
        'dia_mas_solicitado' => $diaMasSolicitado,
        'horario_pico' => $horarioPico,
        'cancha_mas_usada' => $canchaMasUsada,
        'top_clientes' => $topClientes
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error SQL: ' . $e->getMessage()]);
}
