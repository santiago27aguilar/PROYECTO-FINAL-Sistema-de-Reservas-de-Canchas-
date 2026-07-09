<?php
session_start();

// 1. Verificamos si hay alguien logueado
if (!isset($_SESSION['usuario_rol'])) {
    header("Location: ../html/login.php");
    exit();
}

// 2. BLOQUEO DE SEGURIDAD: Solo el dueño puede ver el Tablero
$rol_actual = strtolower($_SESSION['usuario_rol']);
if (!in_array($rol_actual, ['duenio', 'dueño'])) {
    header("Location: inicio.php?error=sin_permisos");
    exit();
}

require_once '../php/conexion.php'; 

try {
    
    // A) Ganancias de Hoy
    $stmtHoy = $conexion->query("SELECT COALESCE(SUM(monto), 0) as total FROM pagos WHERE DATE(fecha_pago) = CURDATE()");
    $gananciasHoy = $stmtHoy->fetch(PDO::FETCH_ASSOC)['total'];

    // B) Turnos Hoy
    $stmtTurnos = $conexion->query("SELECT COUNT(idreservas) as total FROM reservas WHERE DATE(hora_inicio) = CURDATE()");
    $turnosHoy = $stmtTurnos->fetch(PDO::FETCH_ASSOC)['total'];

    // C) Recaudado del Mes
    $stmtMes = $conexion->query("SELECT COALESCE(SUM(monto), 0) as total FROM pagos WHERE MONTH(fecha_pago) = MONTH(CURDATE()) AND YEAR(fecha_pago) = YEAR(CURDATE())");
    $recaudadoMes = $stmtMes->fetch(PDO::FETCH_ASSOC)['total'];

    // D) Día más solicitado
    $stmtDia = $conexion->query("SELECT DAYOFWEEK(hora_inicio) as dia, COUNT(idreservas) as cantidad FROM reservas GROUP BY dia ORDER BY cantidad DESC LIMIT 1");
    $rowDia = $stmtDia->fetch(PDO::FETCH_ASSOC);
    $diasSemana = [1 => 'Domingo', 2 => 'Lunes', 3 => 'Martes', 4 => 'Miércoles', 5 => 'Jueves', 6 => 'Viernes', 7 => 'Sábado'];
    $diaMasSolicitado = $rowDia ? $diasSemana[$rowDia['dia']] : 'Sin datos';

    // E) Horario pico
    $stmtHora = $conexion->query("SELECT HOUR(hora_inicio) as hora, COUNT(idreservas) as cantidad FROM reservas GROUP BY hora ORDER BY cantidad DESC LIMIT 1");
    $rowHora = $stmtHora->fetch(PDO::FETCH_ASSOC);
    $horarioPico = $rowHora ? $rowHora['hora'] . ':00 hs' : 'Sin datos';

    // F) Cancha más usada
    $stmtCancha = $conexion->query("SELECT c.tipo_cancha, COUNT(r.idreservas) as cantidad FROM reservas r JOIN cancha c ON r.cancha_idcancha = c.idcancha GROUP BY c.idcancha ORDER BY cantidad DESC LIMIT 1");
    $rowCancha = $stmtCancha->fetch(PDO::FETCH_ASSOC);
    $canchaMasUsada = $rowCancha ? $rowCancha['tipo_cancha'] : 'Sin datos';

    $stmtTopClientes = $conexion->query("
        SELECT c.nombre, c.apellido, COUNT(r.idreservas) as total_historico, SUM(CASE WHEN MONTH(r.hora_inicio) = MONTH(CURDATE()) AND YEAR(r.hora_inicio) = YEAR(CURDATE()) THEN 1 ELSE 0 END) as total_mes FROM reservas r JOIN clientes c ON r.clientes_idclientes = c.idclientes GROUP BY c.idclientes ORDER BY total_mes DESC, total_historico DESC LIMIT 3
    ");
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
    <title>Panel del Dueño - Pampa Fútbol</title>
    <!-- Le sumamos el control de caché v=2 por si agregás el CSS del link activo -->
    <link rel="stylesheet" href="../css/estilos_dashboard.css?v=2"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<div class="navbar">
    <div class="menu-toggle" id="mobile-menu">
        <i class="fas fa-bars"></i>
    </div>
    <div class="nav-links" id="nav-links">
        
        <!-- 👑 COSAS QUE ***SOLO*** VE EL DUEÑO -->
        <?php if(isset($_SESSION['usuario_rol']) && in_array(strtolower($_SESSION['usuario_rol']), ['duenio', 'dueño'])): ?>
            <a href="dashboard.php" class="link-activo">Tablero</a>
            <a href="personal.php">Personal</a>
            <a href="calendario.php">Calendario</a> 
        <?php endif; ?>

        <!-- 👥 COSAS QUE VEN TODOS (Dueño, Admin y Empleados) -->
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
    </div>

    <div class="dashboard-grid">
        <div class="card-blanca card-dashboard borde-verde">
            <div class="icono-dash texto-verde"><i class="fas fa-dollar-sign"></i></div>
            <div class="info-dash">
                <span class="titulo-dash">GANANCIAS DE HOY</span>
                <span class="valor-dash">$<?= number_format($gananciasHoy, 0, ',', '.') ?></span>
            </div>
        </div>
        <div class="card-blanca card-dashboard borde-verde">
            <div class="icono-dash texto-verde"><i class="fas fa-futbol"></i></div>
            <div class="info-dash">
                <span class="titulo-dash">TURNOS HOY</span>
                <span class="valor-dash"><?= $turnosHoy ?> turnos</span>
            </div>
        </div>
        <div class="card-blanca card-dashboard borde-verde">
            <div class="icono-dash texto-verde"><i class="fas fa-calendar-alt"></i></div>
            <div class="info-dash">
                <span class="titulo-dash">RECAUDADO DEL MES</span>
                <span class="valor-dash">$<?= number_format($recaudadoMes, 0, ',', '.') ?></span>
            </div>
        </div>
    </div>

    <div class="dashboard-row-2">
        
        <div class="card-blanca card-estadisticas">
            <h3 class="titulo-seccion"><i class="fas fa-chart-pie"></i> Estadísticas Generales</h3>
            <div class="lista-estadisticas">
                <div class="item-estadistica">
                    <span class="etiqueta-stat">Día más solicitado:</span>
                    <span class="valor-stat"><?= htmlspecialchars($diaMasSolicitado) ?></span>
                </div>
                <div class="item-estadistica">
                    <span class="etiqueta-stat">Horario pico:</span>
                    <span class="valor-stat"><?= htmlspecialchars($horarioPico) ?></span>
                </div>
                <div class="item-estadistica">
                    <span class="etiqueta-stat">Cancha más usada:</span>
                    <span class="valor-stat"><?= htmlspecialchars($canchaMasUsada) ?></span>
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
                            <th>Este Mes</th>
                            <th>Histórico</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($topClientes)): ?>
                            <tr><td colspan="3">Aún no hay reservas registradas</td></tr>
                        <?php else: ?>
                            <?php foreach ($topClientes as $cliente): ?>
                                <tr>
                                    <td><?= htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']) ?></td>
                                    <td><?= $cliente['total_mes'] ?? 0 ?> turnos</td>
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
</body>
</html>
