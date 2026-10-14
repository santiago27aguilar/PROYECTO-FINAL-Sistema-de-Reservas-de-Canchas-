<?php
session_start();
if (!isset($_SESSION['usuario_rol']) || !in_array(strtolower($_SESSION['usuario_rol']), ['duenio', 'dueño'])) {
    exit("Acceso denegado");
}

error_reporting(0);
ini_set('display_errors', 0);
require('../lib/fpdf/fpdf.php');
include 'conexion.php';

class PDF extends FPDF {
    function Header() {
        // Título Principal
        $this->SetFont('Arial','B',18);
        $this->SetTextColor(43, 27, 84); // Color institucional (morado oscuro)
        $this->Cell(0,15, mb_convert_encoding('SISTEMA DE GESTIÓN DE CANCHAS', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
        
        // Subtítulo
        $this->SetFont('Arial','B',12);
        $this->Cell(0,10, mb_convert_encoding('REPORTE DEL PERSONAL REGISTRADO', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
        $this->Ln(5);
        
        // Cabecera de la tabla
        $this->SetFillColor(43, 27, 84); // Fondo de la cabecera
        $this->SetTextColor(255); // Letra blanca
        $this->SetFont('Arial','B',10);
        
        // Anchos de columnas (Total = 190 para que entre perfecto en hoja A4)
        $this->Cell(45, 10, 'Usuario', 1, 0, 'C', true);
        $this->Cell(40, 10, 'Rol', 1, 0, 'C', true);
        $this->Cell(75, 10, 'Correo', 1, 0, 'C', true);
        $this->Cell(30, 10, 'Estado', 1, 1, 'C', true);
    }
}

// Capturamos el filtro de búsqueda por si quisieran imprimir una lista filtrada
$busqueda = isset($_GET['buscar']) ? $_GET['buscar'] : '';

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial','',10);
$pdf->SetTextColor(51, 51, 51); // Color de texto normal para las filas

// Consultamos el personal en la base de datos
$sql = "SELECT nombre, rol, correo, estado FROM usuario 
        WHERE nombre LIKE :busqueda OR correo LIKE :busqueda OR rol LIKE :busqueda 
        ORDER BY idusuario ASC";
$stmt = $conexion->prepare($sql);
$stmt->execute([':busqueda' => "%$busqueda%"]);

// Rellenamos las filas
while($f = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Si el correo está vacío, le ponemos un guión
    $correo = !empty($f['correo']) ? $f['correo'] : '-';

    $pdf->Cell(45, 10, mb_convert_encoding($f['nombre'], 'ISO-8859-1', 'UTF-8'), 1);
    $pdf->Cell(40, 10, mb_convert_encoding($f['rol'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
    $pdf->Cell(75, 10, mb_convert_encoding($correo, 'ISO-8859-1', 'UTF-8'), 1);
    $pdf->Cell(30, 10, mb_convert_encoding($f['estado'], 'ISO-8859-1', 'UTF-8'), 1, 1, 'C');
}

// Limpiamos el buffer de salida para evitar errores de FPDF
ob_end_clean();

// Mostramos el PDF en el navegador
$pdf->Output('I', 'Reporte_Personal_' . date('Y-m-d') . '.pdf');
?>
