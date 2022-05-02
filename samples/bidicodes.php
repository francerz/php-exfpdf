<?php

use Francerz\ExFPDF\ExFPDF;

define('ROOT_PATH', dirname(__DIR__));

require ROOT_PATH . '/vendor/autoload.php';

$pdf = new ExFPDF();
$pdf->AliasNbPages();
$pdf->SetSourceEncoding('UTF-8');
$pdf->SetFont('Arial', ExFPDF::STYLE_NONE, 12);
$pdf->AddPage('P', 'Letter');
$size = 5;
$space = 2;

$data = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-\n\"abcdefghijklmnopqrstuvwxyz.";

$qrMatrix = $pdf->GetQrCodeMatrix($data, 'H');
$dmMatrix = $pdf->GetDataMatrixMatrix($data);

for ($i = 0; $i < 18; $i++) {
    $pdf->DrawBinaryMatrix($qrMatrix, $size);
    $pdf->SetX("{$space}+");
    $pdf->DrawBinaryMatrix($dmMatrix, $size);
    $pdf->SetX("{$space}+");
    $pdf->Cell(20, 4, "{$size} mm");
    $size++;
    $pdf->SetXY('~0', "{$size}+");
}
// $pdf->SetFillColor('#123464');
// $pdf->Rect('~50%', '~0', '~50%', '~100%', 'F');
// $size = 5;
// $pdf->SetFillColor('#ffffff');
// $pdf->SetXY('~54%', '~5');
// for ($i = 0; $i < 18; $i++) {
//     $pdf->DrawBinaryMatrix($qrMatrix, $size);
//     $pdf->SetX("{$space}+");
//     $pdf->DrawBinaryMatrix($dmMatrix, $size);
//     $pdf->SetX("{$space}+");
//     $pdf->Cell(20, 4, "{$size} mm");
//     $size++;
//     $pdf->SetXY('~54%', "{$size}+");
// }

$pdf->Output('I');
