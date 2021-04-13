<?php
define('ROOT_PATH', dirname(__DIR__));

require ROOT_PATH.'/vendor/autoload.php';

use Francerz\EFPDF\EFPDF;

$pdf = new EFPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// Creates table with three columns with given widths
$table = $pdf->CreateTable(['~25%','~60%','~15%']);

// Creates a row with heading
$pdf->SetLineHeight(1.2);
$pdf->SetFont('','B', 14);
$pdf->SetFillColor('#CA4A0F');
$pdf->SetTextColor('#FFF');
$row = $table->AddRow();
$row->Cell('Price', $align='C', $fill=true, $colspan=1, $rowspan=2);  // 2 rows tall
$row->Cell('Product', $align='C', $fill=true, $colspan=2);            // 2 colums wide
$row = $table->AddRow();
$row->CellSpan($fill=true); // $rowspan still not supported this is placeholder
$row->Cell('Description', $align='C', $fill=true);
$row->Cell('Quantity', $align='C', $fill=true);

// Creates another row
$pdf->SetFont('', '', 12);
$pdf->SetTextColor('#000');
$row = $table->AddRow();
$row->Cell('$ 340.00', $align='R');
$row->Cell('Gymbal');
$row->Cell('3', 'C');
$row = $table->AddRow();
$row->Cell('$ 970.00', $align='R');
$row->Cell('Laptop');
$row->Cell('1', 'C');
$row = $table->AddRow();
$row->Cell('$ 120.00', $align='R');
$row->Cell('Headphones');
$row->Cell('2', 'C');

// Draws all borders in table
$table->DrawBorders();

$pdf->Output('I');