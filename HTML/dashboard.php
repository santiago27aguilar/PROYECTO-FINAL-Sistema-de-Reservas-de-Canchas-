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
    $hoy_str = date('Y-m-d');
    $rapido_raw = isset($_GET['rapido']) ? trim($_GET['rapido']) : '';
    $desde_raw = isset($_GET['desde']) ? trim($_GET['desde']) : '';
    $hasta_raw = isset($_GET['hasta']) ? trim($_GET['hasta']) : '';

    $ts_desde = ($desde_raw !== '') ? strtotime($desde_raw) : false;
    $ts_hasta = ($hasta_raw !== '') ? strtotime($hasta_raw) : false;

    $rapido_activo = 'mes';

    if ($rapido_raw === 'hoy') {
        $fechaInicio = $hoy_str;
        $fechaFin = $hoy_str;
        $rapido_activo = 'hoy';
        $etiquetaPeriodo = 'DE HOY';
    } elseif ($rapido_raw === 'semana') {
        $fechaInicio = date('Y-m-d', strtotime('monday this week', strtotime($hoy_str)));
        $fechaFin = date('Y-m-d', strtotime('sunday this week', strtotime($hoy_str)));
        $rapido_activo = 'semana';
        $etiquetaPeriodo = 'DE LA SEMANA';
    } elseif ($rapido_raw === 'mes') {
        $fechaInicio = date('Y-m-01', strtotime($hoy_str));
        $fechaFin = date('Y-m-t', strtotime($hoy_str));
        $rapido_activo = 'mes';
        $etiquetaPeriodo = 'DEL MES';
    } elseif ($rapido_raw === 'personalizado' || ($ts_desde !== false && $ts_hasta !== false)) {
        $fechaInicio = ($ts_desde !== false) ? date('Y-m-d', $ts_desde) : date('Y-m-01', strtotime($hoy_str));
        $fechaFin = ($ts_hasta !== false) ? date('Y-m-d', $ts_hasta) : date('Y-m-t', strtotime($hoy_str));

        if ($fechaInicio > $fechaFin) {
            $tmp = $fechaInicio;
            $fechaInicio = $fechaFin;
            $fechaFin = $tmp;
        }
        $rapido_activo = 'personalizado';
        $etiquetaPeriodo = 'DEL PERÍODO';
    } else {
        $fechaInicio = date('Y-m-01', strtotime($hoy_str));
        $fechaFin = date('Y-m-t', strtotime($hoy_str));
        $rapido_activo = 'mes';
        $etiquetaPeriodo = 'DEL MES';
    }

    $dtInicio = $fechaInicio . ' 00:00:00';
    $dtFin = $fechaFin . ' 23:59:59';

    // A) Ganancias filtradas por rango
    $stmtGanancias = $conexion->prepare("
        SELECT COALESCE(SUM(monto), 0) AS total
        FROM pagos
        WHERE fecha_pago BETWEEN ? AND ?
    ");
    $stmtGanancias->execute([$dtInicio, $dtFin]);
    $recaudadoPeriodo = (float)($stmtGanancias->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

    // B) Turnos filtrados por rango
    $stmtTurnos = $conexion->prepare("
        SELECT COUNT(idreservas) AS total
        FROM reservas
        WHERE hora_inicio BETWEEN ? AND ?
          AND LOWER(COALESCE(estado, '')) <> 'cancelado'
    ");
    $stmtTurnos->execute([$dtInicio, $dtFin]);
    $turnosPeriodo = (int)($stmtTurnos->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

    // C) Ticket promedio del rango
    $ticketPromedio = ($turnosPeriodo > 0) ? ($recaudadoPeriodo / $turnosPeriodo) : 0;

    // D) Día más solicitado en el rango
    $stmtDia = $conexion->prepare("
        SELECT DAYOFWEEK(hora_inicio) AS dia, COUNT(idreservas) AS cantidad
        FROM reservas
        WHERE hora_inicio BETWEEN ? AND ?
          AND LOWER(COALESCE(estado, '')) <> 'cancelado'
        GROUP BY dia
        ORDER BY cantidad DESC
        LIMIT 1
    ");
    $stmtDia->execute([$dtInicio, $dtFin]);
    $rowDia = $stmtDia->fetch(PDO::FETCH_ASSOC);
    $diasSemana = [
        1 => 'Domingo', 2 => 'Lunes', 3 => 'Martes', 4 => 'Miércoles',
        5 => 'Jueves', 6 => 'Viernes', 7 => 'Sábado'
    ];
    $diaMasSolicitado = $rowDia ? ($diasSemana[(int)$rowDia['dia']] ?? 'Sin datos') : 'Sin datos';

    // E) Horario más pedido en el rango
    $stmtHora = $conexion->prepare("
        SELECT HOUR(hora_inicio) AS hora, COUNT(idreservas) AS cantidad
        FROM reservas
        WHERE hora_inicio BETWEEN ? AND ?
          AND LOWER(COALESCE(estado, '')) <> 'cancelado'
        GROUP BY hora
        ORDER BY cantidad DESC
        LIMIT 1
    ");
    $stmtHora->execute([$dtInicio, $dtFin]);
    $rowHora = $stmtHora->fetch(PDO::FETCH_ASSOC);
    $horarioPico = $rowHora ? str_pad((int)$rowHora['hora'], 2, '0', STR_PAD_LEFT) . ':00 hs' : 'Sin datos';

    // F) Cancha más usada en el rango
    $stmtCancha = $conexion->prepare("
        SELECT c.tipo_cancha, COUNT(r.idreservas) AS cantidad
        FROM reservas r
        INNER JOIN cancha c ON r.cancha_idcancha = c.idcancha
        WHERE r.hora_inicio BETWEEN ? AND ?
          AND LOWER(COALESCE(r.estado, '')) <> 'cancelado'
        GROUP BY r.cancha_idcancha, c.tipo_cancha
        ORDER BY cantidad DESC
        LIMIT 1
    ");
    $stmtCancha->execute([$dtInicio, $dtFin]);
    $rowCancha = $stmtCancha->fetch(PDO::FETCH_ASSOC);
    $canchaMasUsada = $rowCancha ? $rowCancha['tipo_cancha'] : 'Sin datos';

    // G) Top Clientes filtrados por rango
    $stmtTopClientes = $conexion->prepare("
        SELECT c.nombre, c.apellido,
               COUNT(r.idreservas) AS total_periodo
        FROM reservas r
        INNER JOIN clientes c ON r.clientes_idclientes = c.idclientes
        WHERE r.hora_inicio BETWEEN ? AND ?
          AND LOWER(COALESCE(r.estado, '')) <> 'cancelado'
        GROUP BY r.clientes_idclientes, c.nombre, c.apellido
        ORDER BY total_periodo DESC
        LIMIT 3
    ");
    $stmtTopClientes->execute([$dtInicio, $dtFin]);
    $topClientes = $stmtTopClientes->fetchAll(PDO::FETCH_ASSOC);

    // H) Gráfica de Torta 1: Participación por cancha en el rango
    $stmtTortaCanchas = $conexion->prepare("
        SELECT c.tipo_cancha, COUNT(r.idreservas) AS cant_turnos
        FROM reservas r
        INNER JOIN cancha c ON r.cancha_idcancha = c.idcancha
        WHERE r.hora_inicio BETWEEN ? AND ?
          AND LOWER(COALESCE(r.estado, '')) <> 'cancelado'
        GROUP BY r.cancha_idcancha, c.tipo_cancha
        ORDER BY cant_turnos DESC
    ");
    $stmtTortaCanchas->execute([$dtInicio, $dtFin]);
    $filasTortaCanchas = $stmtTortaCanchas->fetchAll(PDO::FETCH_ASSOC);

    $labelsTortaCanchas = [];
    $valoresTortaCanchas = [];
    foreach ($filasTortaCanchas as $ftc) {
        $labelsTortaCanchas[] = $ftc['tipo_cancha'];
        $valoresTortaCanchas[] = (int)$ftc['cant_turnos'];
    }

    // I) Gráfica de Torta 2: Turnos por franja horaria en el rango
    $stmtTortaFranjas = $conexion->prepare("
        SELECT
            CASE
                WHEN HOUR(r.hora_inicio) BETWEEN 14 AND 18 THEN 'Turno Tarde (14-18 hs)'
                ELSE 'Turno Noche (19-00 hs)'
            END AS franja,
            COUNT(r.idreservas) AS cant_turnos
        FROM reservas r
        WHERE r.hora_inicio BETWEEN ? AND ?
          AND LOWER(COALESCE(r.estado, '')) <> 'cancelado'
        GROUP BY franja
        ORDER BY cant_turnos DESC
    ");
    $stmtTortaFranjas->execute([$dtInicio, $dtFin]);
    $filasTortaFranjas = $stmtTortaFranjas->fetchAll(PDO::FETCH_ASSOC);

    $labelsTortaFranjas = [];
    $valoresTortaFranjas = [];
    foreach ($filasTortaFranjas as $ftf) {
        $labelsTortaFranjas[] = $ftf['franja'];
        $valoresTortaFranjas[] = (int)$ftf['cant_turnos'];
    }

    $queryPdf = http_build_query([
        'desde'  => $fechaInicio,
        'hasta'  => $fechaFin,
        'rapido' => $rapido_activo
    ]);

} catch (PDOException $e) {
    die("Error al cargar los datos del tablero: " . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Dueño - Planeta de Futbol</title>
    <?php include 'head_comun.php'; ?>
    <link rel="stylesheet" href="../css/estilos_dashboard.css?v=18"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
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
        <h1>ESTADÍSTICAS GENERALES</h1>
        
        <form method="GET" action="dashboard.php" id="formFiltroDashboard" class="filtro-periodo">
            <input type="hidden" name="rapido" id="input-rapido" value="<?= htmlspecialchars($rapido_activo) ?>">

            <label><i class="fas fa-calendar-alt"></i> Filtrar:</label>
            
            <div class="grupo-fecha">
                <span>Desde:</span>
                <input type="date" id="fecha-inicio" name="desde" value="<?= htmlspecialchars($fechaInicio) ?>">
            </div>
            
            <div class="grupo-fecha">
                <span>Hasta:</span>
                <input type="date" id="fecha-fin" name="hasta" value="<?= htmlspecialchars($fechaFin) ?>">
            </div>

            <button type="submit" id="btn-filtrar" class="btn-verde-chico" title="Buscar por fechas">
                <i class="fas fa-search"></i> Buscar
            </button>

            <a href="../php/exportar_pdf_dashboard.php?<?= htmlspecialchars($queryPdf) ?>" id="btn-pdf-dashboard" class="btn-pdf-estilo" title="Generar Reporte PDF">
                <i class="fas fa-file-pdf"></i> Reporte PDF
            </a>
        </form>

        <div class="barra-periodo-rapido">
            <div class="periodo-rapido-label">
                <i class="far fa-calendar-alt"></i>
                <span>Período rápido:</span>
            </div>

            <div class="botones-rapidos">
                <a href="dashboard.php?rapido=hoy" class="btn-rapido <?= ($rapido_activo === 'hoy') ? 'activo' : '' ?>">Hoy</a>
                <a href="dashboard.php?rapido=semana" class="btn-rapido <?= ($rapido_activo === 'semana') ? 'activo' : '' ?>">Esta Semana</a>
                <a href="dashboard.php?rapido=mes" class="btn-rapido <?= ($rapido_activo === 'mes') ? 'activo' : '' ?>">Este Mes</a>
                <button type="button" id="btn-rapido-personalizado" class="btn-rapido <?= ($rapido_activo === 'personalizado') ? 'activo' : '' ?>">Personalizado</button>
            </div>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="card-blanca card-dashboard borde-verde">
            <div class="icono-dash texto-verde"><i class="fas fa-dollar-sign"></i></div>
            <div class="info-dash">
                <span class="titulo-dash" id="titulo-tarjeta-ganancias">GANANCIAS <?= htmlspecialchars($etiquetaPeriodo) ?></span>
                <span class="valor-dash" id="val-ganancias">$<?= number_format($recaudadoPeriodo, 0, ',', '.') ?></span>
            </div>
        </div>

        <div class="card-blanca card-dashboard borde-verde">
            <div class="icono-dash texto-verde"><i class="fas fa-futbol"></i></div>
            <div class="info-dash">
                <span class="titulo-dash" id="titulo-tarjeta-turnos">TURNOS <?= htmlspecialchars($etiquetaPeriodo) ?></span>
                <span class="valor-dash" id="val-turnos"><?= $turnosPeriodo ?> turnos</span>
            </div>
        </div>

        <div class="card-blanca card-dashboard borde-verde">
            <div class="icono-dash texto-verde"><i class="fas fa-receipt"></i></div>
            <div class="info-dash">
                <span class="titulo-dash" id="titulo-tarjeta-promedio">TICKET PROMEDIO</span>
                <span class="valor-dash" id="val-promedio">$<?= number_format($ticketPromedio, 0, ',', '.') ?></span>
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
                    <span class="etiqueta-stat">Horario más pedido:</span>
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
                            <th>Turnos en rango</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-clientes-body">
                        <?php if (empty($topClientes)): ?>
                            <tr><td colspan="2">Sin turnos en el rango seleccionado</td></tr>
                        <?php else: ?>
                            <?php foreach ($topClientes as $cliente): ?>
                                <tr>
                                    <td><?= htmlspecialchars(trim($cliente['nombre'] . ' ' . $cliente['apellido'])) ?></td>
                                    <td><?= (int)($cliente['total_periodo'] ?? 0) ?> turnos</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="dashboard-row-2">
        <div class="card-blanca card-grafico">
            <h3 class="titulo-seccion"><i class="fas fa-chart-pie"></i> Participación por Cancha</h3>
            <div class="grafico-canvas-box">
                <canvas id="tortaCanchas"
                        data-labels='<?= htmlspecialchars(json_encode($labelsTortaCanchas, JSON_UNESCAPED_UNICODE), ENT_QUOTES, "UTF-8") ?>'
                        data-valores='<?= htmlspecialchars(json_encode($valoresTortaCanchas), ENT_QUOTES, "UTF-8") ?>'></canvas>
            </div>
        </div>

        <div class="card-blanca card-grafico">
            <h3 class="titulo-seccion"><i class="fas fa-clock"></i> Turnos por Franja Horaria</h3>
            <div class="grafico-canvas-box">
                <canvas id="tortaFranjas"
                        data-labels='<?= htmlspecialchars(json_encode($labelsTortaFranjas, JSON_UNESCAPED_UNICODE), ENT_QUOTES, "UTF-8") ?>'
                        data-valores='<?= htmlspecialchars(json_encode($valoresTortaFranjas), ENT_QUOTES, "UTF-8") ?>'></canvas>
            </div>
        </div>
    </div>

</div>

<script src="../js/menu_desplegable.js"></script>
<script src="../js/graficos_dashboard.js"></script>

</body>
</html>
