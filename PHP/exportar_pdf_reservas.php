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
        $this->Cell(0,10, mb_convert_encoding('REPORTE ACERCA DE LAS RESERVAS', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
        $this->Ln(5);
        $this->SetFillColor(43, 27, 84); 
        $this->SetTextColor(255);
        $this->SetFont('Arial','B',10);
        $this->Cell(50, 10, 'Cliente', 1, 0, 'C', true);
        $this->Cell(35, 10, 'Cancha', 1, 0, 'C', true);
        $this->Cell(30, 10, 'Fecha', 1, 0, 'C', true);
        $this->Cell(40, 10, 'Horario', 1, 0, 'C', true);
        $this->Cell(35, 10, 'WhatsApp', 1, 1, 'C', true);
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial','',10);
$pdf->SetTextColor(0);

$sql = "SELECT c.nombre, c.apellido, ca.tipo_cancha, r.hora_inicio, r.hora_fin, c.telefono 
        FROM reservas r LEFT JOIN clientes c ON r.clientes_idclientes = c.idclientes 
        JOIN cancha ca ON r.cancha_idcancha = ca.idcancha ORDER BY r.hora_inicio DESC";
$res = $conexion->query($sql);

while($f = $res->fetch(PDO::FETCH_ASSOC)) {
    $pdf->Cell(50, 10, mb_convert_encoding($f['nombre']." ".$f['apellido'], 'ISO-8859-1', 'UTF-8'), 1);
    $pdf->Cell(35, 10, mb_convert_encoding($f['tipo_cancha'], 'ISO-8859-1', 'UTF-8'), 1);
    $pdf->Cell(30, 10, date("d-m-Y", strtotime($f['hora_inicio'])), 1);
    $pdf->Cell(40, 10, date("H:i",strtotime($f['hora_inicio']))." a ".date("H:i",strtotime($f['hora_fin'])), 1);
    $pdf->Cell(35, 10, $f['telefono'], 1);
    $pdf->Ln();
}
ob_end_clean();
$pdf->Output('I', 'Reporte_Reservas.pdf');
