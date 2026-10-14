<?php
error_reporting(0);
ini_set('display_errors', 0);
require('../lib/fpdf/fpdf.php');
include 'conexion.php';

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial','B',18);
        $this->SetTextColor(43, 27, 84); 
        $this->Cell(0,15, mb_convert_encoding('SISTEMA DE GESTIÓN DE CANCHAS', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
        $this->SetFont('Arial','B',12);
        $this->Cell(0,10, mb_convert_encoding('REPORTE SOBRE LOS PAGOS', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
        $this->Ln(5);
        $this->SetFillColor(43, 27, 84); 
        $this->SetTextColor(255);
        $this->SetFont('Arial','B',10);
        $this->Cell(60, 10, 'Cliente', 1, 0, 'C', true);
        $this->Cell(40, 10, 'Monto', 1, 0, 'C', true);
        $this->Cell(45, 10, mb_convert_encoding('Método', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', true);
        $this->Cell(45, 10, 'Fecha Pago', 1, 1, 'C', true);
    }
}
 
$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial','',10);

// Unimos pagos con reservas y luego reservas con clientes para sacar el nombre
$sql = "SELECT p.monto, p.metodo_pago, p.fecha_pago, cl.nombre, cl.apellido 
        FROM pagos p 
        INNER JOIN reservas r ON p.reservas_idreservas = r.idreservas 
        INNER JOIN clientes cl ON r.clientes_idclientes = cl.idclientes 
        ORDER BY p.fecha_pago DESC";
$res = $conexion->query($sql);

while($f = $res->fetch(PDO::FETCH_ASSOC)) {
    $pdf->Cell(60, 10, mb_convert_encoding($f['nombre']." ".$f['apellido'], 'ISO-8859-1', 'UTF-8'), 1);
    $pdf->Cell(40, 10, '$' . number_format($f['monto'], 2), 1);
    $pdf->Cell(45, 10, mb_convert_encoding($f['metodo_pago'], 'ISO-8859-1', 'UTF-8'), 1);
    $pdf->Cell(45, 10, date("d/m/Y H:i", strtotime($f['fecha_pago'])), 1);
    $pdf->Ln();
}
ob_end_clean();
$pdf->Output('I', 'Reporte_Pagos.pdf');
