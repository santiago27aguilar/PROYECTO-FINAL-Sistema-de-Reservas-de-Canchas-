<?php
session_start();

if (!isset($_SESSION['usuario_rol'])) {
    header("Location: ../html/login.php");
    exit();
}

$rol_actual = strtolower($_SESSION['usuario_rol']);
if (!in_array($rol_actual, ['duenio', 'dueño'])) {
    header("Location: inicio.php?error=sin_permisos");
    exit();
}

require_once '../php/conexion.php'; 

try {
    // Variables iniciales por defecto (Mes actual en curso)
    $fechaInicioMes = date('Y-m-01');
    $fechaFinMes = date('Y-m-t'); 

    // A) Ganancias Iniciales (Del Mes actual)
    $stmtHoy = $conexion->prepare("SELECT COALESCE(SUM(monto), 0) as total FROM pagos WHERE fecha_pago BETWEEN ? AND ?");
    $stmtHoy->execute([$fechaInicioMes . ' 00:00:00', $fechaFinMes . ' 23:59:59']);
    $recaudadoPeriodo = $stmtHoy->fetch(PDO::FETCH_ASSOC)['total'];

    // B) Turnos Iniciales (Del Mes actual)
    $stmtTurnos = $conexion->prepare("SELECT COUNT(idreservas) as total FROM reservas WHERE hora_inicio BETWEEN ? AND ?");
    $stmtTurnos->execute([$fechaInicioMes . ' 00:00:00', $fechaFinMes . ' 23:59:59']);
    $turnosPeriodo = $stmtTurnos->fetch(PDO::FETCH_ASSOC)['total'];

    // C) Recaudado Inicial (Pagos del Mes actual - idéntico al anterior o sumado por pagos)
    $recaudadoMes = $recaudadoPeriodo;

    // D) Día más solicitado
    $stmtDia = $conexion->prepare("SELECT DAYOFWEEK(hora_inicio) as dia, COUNT(idreservas) as cantidad FROM reservas WHERE hora_inicio BETWEEN ? AND ? GROUP BY dia ORDER BY cantidad DESC LIMIT 1");
    $stmtDia->execute([$fechaInicioMes . ' 00:00:00', $fechaFinMes . ' 23:59:59']);
    $rowDia = $stmtDia->fetch(PDO::FETCH_ASSOC);
    $diasSemana = [1 => 'Domingo', 2 => 'Lunes', 3 => 'Martes', 4 => 'Miércoles', 5 => 'Jueves', 6 => 'Viernes', 7 => 'Sábado'];
    $diaMasSolicitado = $rowDia ? $diasSemana[$rowDia['dia']] : 'Sin datos';

    // E) Horario pico
    $stmtHora = $conexion->prepare("SELECT HOUR(hora_inicio) as hora, COUNT(idreservas) as cantidad FROM reservas WHERE hora_inicio BETWEEN ? AND ? GROUP BY hora ORDER BY cantidad DESC LIMIT 1");
    $stmtHora->execute([$fechaInicioMes . ' 00:00:00', $fechaFinMes . ' 23:59:59']);
    $rowHora = $stmtHora->fetch(PDO::FETCH_ASSOC);
    $horarioPico = $rowHora ? $rowHora['hora'] . ':00 hs' : 'Sin datos';

    // F) Cancha más usada
    $stmtCancha = $conexion->prepare("SELECT c.tipo_cancha, COUNT(r.idreservas) as cantidad FROM reservas r JOIN cancha c ON r.cancha_idcancha = c.idcancha WHERE r.hora_inicio BETWEEN ? AND ? GROUP BY c.idcancha ORDER BY cantidad DESC LIMIT 1");
    $stmtCancha->execute([$fechaInicioMes . ' 00:00:00', $fechaFinMes . ' 23:59:59']);
    $rowCancha = $stmtCancha->fetch(PDO::FETCH_ASSOC);
    $canchaMasUsada = $rowCancha ? $rowCancha['tipo_cancha'] : 'Sin datos';

    // G) Top Clientes
    $stmtTopClientes = $conexion->prepare("
        SELECT c.nombre, c.apellido, 
               COUNT(r.idreservas) as total_historico, 
               SUM(CASE WHEN r.hora_inicio BETWEEN ? AND ? THEN 1 ELSE 0 END) as total_periodo 
        FROM reservas r 
        JOIN clientes c ON r.clientes_idclientes = c.idclientes 
        GROUP BY c.idclientes 
        ORDER BY total_periodo DESC, total_historico DESC 
        LIMIT 3
    ");
    $stmtTopClientes->execute([$fechaInicioMes . ' 00:00:00', $fechaFinMes . ' 23:59:59']);
    $topClientes = $stmtTopClientes->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die("Error al cargar los datos del tablero: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Dueño - Planeta de Futbol</title>
    <?php include 'head_comun.php'; ?>
    <link rel="stylesheet" href="../css/estilos_dashboard.css?v=10"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<div class="navbar">
    <div class="menu-toggle" id="mobile-menu">
        <i class="fas fa-bars"></i>
    </div>
    <div class="nav-links" id="nav-links">
        <?php if(isset($_SESSION['usuario_rol']) && in_array(strtolower($_SESSION['usuario_rol']), ['duenio', 'dueño'])): ?>
            <a href="dashboard.php" class="link-activo">Tablero</a>
            <a href="personal.php">Personal</a>
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
        <h1>ESTADISTICAS GENERALES</h1>
        
        <div class="filtro-periodo">
            <label><i class="fas fa-calendar-alt"></i> Filtrar:</label>
            
            <div class="grupo-fecha">
                <span>Desde:</span>
                <input type="date" id="fecha-inicio" value="<?= $fechaInicioMes ?>">
            </div>
            
            <div class="grupo-fecha">
                <span>Hasta:</span>
                <input type="date" id="fecha-fin" value="<?= $fechaFinMes ?>">
            </div>

            <button id="btn-filtrar" class="btn-verde-chico" title="Buscar por fechas"><i class="fas fa-search"></i> Buscar</button>
            <button id="btn-historico" class="btn-rojo btn-historico-estilo" title="Ver Todo el Historial Completo"><i class="fas fa-globe"></i> Historial Completo</button>
        </div>
    </div>

    <!-- Tarjetas superiores con IDs dinámicos para JavaScript -->
    <div class="dashboard-grid">
        <div class="card-blanca card-dashboard borde-verde">
            <div class="icono-dash texto-verde"><i class="fas fa-dollar-sign"></i></div>
            <div class="info-dash">
                <span class="titulo-dash" id="titulo-tarjeta-ganancias">GANANCIAS DEL MES</span>
                <span class="valor-dash" id="val-ganancias">$<?= number_format($recaudadoPeriodo, 0, ',', '.') ?></span>
            </div>
        </div>
        <div class="card-blanca card-dashboard borde-verde">
            <div class="icono-dash texto-verde"><i class="fas fa-futbol"></i></div>
            <div class="info-dash">
                <span class="titulo-dash" id="titulo-tarjeta-turnos">TURNOS DEL MES</span>
                <span class="valor-dash" id="val-turnos"><?= $turnosPeriodo ?> turnos</span>
            </div>
        </div>
        <div class="card-blanca card-dashboard borde-verde">
            <div class="icono-dash texto-verde"><i class="fas fa-calendar-alt"></i></div>
            <div class="info-dash">
                <span class="titulo-dash" id="titulo-tarjeta-recaudado">TOTAL RECAUDADO</span>
                <span class="valor-dash" id="val-recaudado">$<?= number_format($recaudadoMes, 0, ',', '.') ?></span>
            </div>
        </div>
    </div>

    <div class="dashboard-row-2">
        <div class="card-blanca card-estadisticas">
            <h3 class="titulo-seccion"><i class="fas fa-chart-pie"></i> Estadísticas Generales</h3>
            <div class="lista-estadisticas">
                <div class="item-estadistica">
                    <span class="etiqueta-stat">Día más solicitado:</span>
                    <span class="valor-stat" id="stat-dia"><?= htmlspecialchars($diaMasSolicitado) ?></span>
                </div>
                <div class="item-estadistica">
                    <span class="etiqueta-stat">Horario mas pedido:</span>
                    <span class="valor-stat" id="stat-hora"><?= htmlspecialchars($horarioPico) ?></span>
                </div>
                <div class="item-estadistica">
                    <span class="etiqueta-stat">Cancha más usada:</span>
                    <span class="valor-stat" id="stat-cancha"><?= htmlspecialchars($canchaMasUsada) ?></span>
                </div>
            </div>
        </div>

        <div class="card-blanca card-top-clientes">
            <h3 class="titulo-seccion"><i class="fas fa-medal"></i> Mejores Clientes</h3>
            <div class="table-responsive-wrapper">
                <table class="tabla-moderna tabla-chica">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>En el mes</th>
                            <th>Histórico</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-clientes-body">
                        <?php if (empty($topClientes)): ?>
                            <tr><td colspan="3">Aún no hay reservas registradas</td></tr>
                        <?php else: ?>
                            <?php foreach ($topClientes as $cliente): ?>
                                <tr>
                                    <td><?= htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']) ?></td>
                                    <td><?= $cliente['total_periodo'] ?? 0 ?> turnos</td>
                                    <td><?= $cliente['total_historico'] ?> turnos</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script src="../js/menu_desplegable.js"></script>
<script src="../js/filtro_dashboard.js"></script>
</body>
</html>
