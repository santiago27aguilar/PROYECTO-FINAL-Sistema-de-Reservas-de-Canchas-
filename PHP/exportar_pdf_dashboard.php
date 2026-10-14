<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);

if (!isset($_SESSION['usuario_rol'])) {
    header("Location: ../html/login.php");
    exit();
}

$rol_actual = strtolower($_SESSION['usuario_rol']);
if (!in_array($rol_actual, ['duenio', 'dueño'])) {
    die("Error: No tienes permisos para generar este reporte.");
}

require('../lib/fpdf/fpdf.php');
include 'conexion.php';

// Capturar las fechas pasadas por el botón JavaScript
$fecha_get_desde = isset($_GET['desde']) ? $_GET['desde'] : '';
$fecha_get_hasta = isset($_GET['hasta']) ? $_GET['hasta'] : '';

$es_historico = false;

// Si las fechas vienen vacías, es porque apretó "Historial Completo"
if (empty($fecha_get_desde) || empty($fecha_get_hasta)) {
    $es_historico = true;
    $fecha_desde = '2000-01-01'; 
    $fecha_hasta = '2100-12-31';
} else {
    $fecha_desde = $fecha_get_desde;
    $fecha_hasta = $fecha_get_hasta;
}

try {
    // A) Ganancias
    $stmtHoy = $conexion->prepare("SELECT COALESCE(SUM(monto), 0) as total FROM pagos WHERE fecha_pago BETWEEN ? AND ?");
    $stmtHoy->execute([$fecha_desde . ' 00:00:00', $fecha_hasta . ' 23:59:59']);
    $recaudadoPeriodo = $stmtHoy->fetch(PDO::FETCH_ASSOC)['total'];

    // B) Turnos
    $stmtTurnos = $conexion->prepare("SELECT COUNT(idreservas) as total FROM reservas WHERE hora_inicio BETWEEN ? AND ?");
    $stmtTurnos->execute([$fecha_desde . ' 00:00:00', $fecha_hasta . ' 23:59:59']);
    $turnosPeriodo = $stmtTurnos->fetch(PDO::FETCH_ASSOC)['total'];

    // C) Día más solicitado
    $stmtDia = $conexion->prepare("SELECT DAYOFWEEK(hora_inicio) as dia, COUNT(idreservas) as cantidad FROM reservas WHERE hora_inicio BETWEEN ? AND ? GROUP BY dia ORDER BY cantidad DESC LIMIT 1");
    $stmtDia->execute([$fecha_desde . ' 00:00:00', $fecha_hasta . ' 23:59:59']);
    $rowDia = $stmtDia->fetch(PDO::FETCH_ASSOC);
    $diasSemana = [1 => 'Domingo', 2 => 'Lunes', 3 => 'Martes', 4 => 'Miércoles', 5 => 'Jueves', 6 => 'Viernes', 7 => 'Sábado'];
    $diaMasSolicitado = $rowDia ? $diasSemana[$rowDia['dia']] : 'Sin datos';

    // D) Horario pico
    $stmtHora = $conexion->prepare("SELECT HOUR(hora_inicio) as hora, COUNT(idreservas) as cantidad FROM reservas WHERE hora_inicio BETWEEN ? AND ? GROUP BY hora ORDER BY cantidad DESC LIMIT 1");
    $stmtHora->execute([$fecha_desde . ' 00:00:00', $fecha_hasta . ' 23:59:59']);
    $rowHora = $stmtHora->fetch(PDO::FETCH_ASSOC);
    $horarioPico = $rowHora ? $rowHora['hora'] . ':00 hs' : 'Sin datos';

    // E) Cancha más usada
    $stmtCancha = $conexion->prepare("SELECT c.tipo_cancha, COUNT(r.idreservas) as cantidad FROM reservas r JOIN cancha c ON r.cancha_idcancha = c.idcancha WHERE r.hora_inicio BETWEEN ? AND ? GROUP BY c.idcancha ORDER BY cantidad DESC LIMIT 1");
    $stmtCancha->execute([$fecha_desde . ' 00:00:00', $fecha_hasta . ' 23:59:59']);
    $rowCancha = $stmtCancha->fetch(PDO::FETCH_ASSOC);
    $canchaMasUsada = $rowCancha ? $rowCancha['tipo_cancha'] : 'Sin datos';

    // F) Top Clientes
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
    $stmtTopClientes->execute([$fecha_desde . ' 00:00:00', $fecha_hasta . ' 23:59:59']);
    $topClientes = $stmtTopClientes->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die("Error en la base de datos.");
}

// --- CLASE PDF PERSONALIZADA ---
class PDF extends FPDF {
    public $rangoFechas = "";

    function Header() {
        $this->SetFont('Arial', 'B', 18);
        $this->SetTextColor(43, 27, 84); // Violeta
        $this->Cell(0, 15, mb_convert_encoding('SISTEMA DE GESTIÓN DE CANCHAS', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
        
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, mb_convert_encoding('REPORTE ESTADÍSTICO GERENCIAL', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
        
        // Letra normal (derecha) para el período
        $this->SetFont('Arial', '', 11);
        $this->SetTextColor(80, 80, 80);
        $this->Cell(0, 5, mb_convert_encoding($this->rangoFechas, 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
        $this->Ln(10);
    }
}

$pdf = new PDF();

// Definir texto del período
if ($es_historico) {
    $pdf->rangoFechas = "Período analizado: Historial Completo";
} else {
    $pdf->rangoFechas = "Período analizado: " . date("d/m/Y", strtotime($fecha_desde)) . " al " . date("d/m/Y", strtotime($fecha_hasta));
}

$pdf->AddPage();

// --- 1. RESUMEN FINANCIERO Y OPERATIVO ---
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(255);
$pdf->SetFillColor(43, 27, 84);
$pdf->Cell(0, 10, mb_convert_encoding('1. Resumen General', 'ISO-8859-1', 'UTF-8'), 1, 1, 'C', true);

$pdf->SetTextColor(0);
// Fila Ganancias (con borde 1)
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(95, 10, mb_convert_encoding('  Ganancias Totales:', 'ISO-8859-1', 'UTF-8'), 1, 0, 'L');
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(95, 10, '$' . number_format($recaudadoPeriodo, 2, ',', '.') . '  ', 1, 1, 'R');

// Fila Turnos (con borde 1)
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(95, 10, '  Total de Turnos:', 1, 0, 'L');
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(95, 10, $turnosPeriodo . ' turnos  ', 1, 1, 'R');
$pdf->Ln(8);

// --- 2. TENDENCIAS DE USO ---
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(255);
$pdf->Cell(0, 10, mb_convert_encoding('2. Estadísticas de Uso y Preferencias', 'ISO-8859-1', 'UTF-8'), 1, 1, 'C', true);

$pdf->SetTextColor(0);
// Fila Día (con borde 1)
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(95, 10, mb_convert_encoding('  Día mas solicitado:', 'ISO-8859-1', 'UTF-8'), 1, 0, 'L');
$pdf->Cell(95, 10, mb_convert_encoding($diaMasSolicitado . '  ', 'ISO-8859-1', 'UTF-8'), 1, 1, 'R');

// Fila Horario (con borde 1)
$pdf->Cell(95, 10, mb_convert_encoding('  Horario más solicitado:', 'ISO-8859-1', 'UTF-8'), 1, 0, 'L');
$pdf->Cell(95, 10, mb_convert_encoding($horarioPico . '  ', 'ISO-8859-1', 'UTF-8'), 1, 1, 'R');

// Fila Cancha (con borde 1)
$pdf->Cell(95, 10, mb_convert_encoding('  Cancha más utilizada:', 'ISO-8859-1', 'UTF-8'), 1, 0, 'L');
$pdf->Cell(95, 10, mb_convert_encoding($canchaMasUsada . '  ', 'ISO-8859-1', 'UTF-8'), 1, 1, 'R');
$pdf->Ln(10);

// --- 3. TOP CLIENTES ---
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(255);
$pdf->Cell(0, 10, mb_convert_encoding('3. Clientes Destacados', 'ISO-8859-1', 'UTF-8'), 1, 1, 'C', true);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(90, 10, 'Cliente', 1, 0, 'C', true);
$pdf->Cell(50, 10, 'Turnos en el mes', 1, 0, 'C', true);
$pdf->Cell(50, 10, mb_convert_encoding('Histórico', 'ISO-8859-1', 'UTF-8'), 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(0);

if (empty($topClientes)) {
    $pdf->Cell(190, 10, 'Sin registros en este periodo', 1, 1, 'C');
} else {
    foreach ($topClientes as $cliente) {
        $nombreCompleto = mb_convert_encoding($cliente['nombre'] . ' ' . $cliente['apellido'], 'ISO-8859-1', 'UTF-8');
        $turnosMes = ($cliente['total_periodo'] ?? 0) . ' turnos';
        $turnosHistorico = $cliente['total_historico'] . ' turnos';

        $pdf->Cell(90, 10, $nombreCompleto, 1, 0, 'C');
        $pdf->Cell(50, 10, $turnosMes, 1, 0, 'C');
        $pdf->Cell(50, 10, $turnosHistorico, 1, 1, 'C');
    }
}

ob_end_clean();
$pdf->Output('I', 'Reporte_Tablero_Gerencial.pdf');
?>
