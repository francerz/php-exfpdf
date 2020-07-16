<?php
define('ROOT_PATH', dirname(__DIR__));
define('FPDF_IMGPATH', ROOT_PATH.'/tests/img');

require ROOT_PATH.'/vendor/autoload.php';

use Francerz\EFPDF\EFPDF;

$userName = "My Name";

$pdf = new EFPDF();
$pdf->AliasNbPages();
$pdf->SetFont('Arial', '', 12);
$pdf->SetHeader(function(EFPDF $efpdf) use ($userName) {
    $efpdf->SetFont('Arial','B', 16);
    $efpdf->CellUTF8('~100%', 10, "Hello {$userName}!", 1);
});
$pdf->SetFooter(function(EFPDF $efpdf) {
    $efpdf->CellUTF8('~100%', 10, 'Page '.$efpdf->PageNo(), 1, 0, 'R');
});

$pdf->AddPage('P','Letter');

$pdf->SetX('~0');
$pdf->SetXYPin('top-left');

$pdf->Cell('~100%', 20, 'THIS IS A HEADING', 1, 2, 'C');
$pdf->Image(
    FPDF_IMGPATH.'/logo.gif',
    $pdf->GetXPin('top-left') + 1,
    $pdf->GetYPin('top-left')
);

$pdf->CellRight(15, 5, 'Date: ', 0, 0, 'R', false, '', 30);
$pdf->CellRight(30, 5, date('Y-m-d'), 1, 1, 'C', false, '', 0);

$pdf->CellUTF8(0, 10, 'Benjamín pidió una bebida de kiwi y fresa; Noé, sin vergüenza, la más exquisita champaña del menú.');

$pdf->SetXY('25%', '25%');
$pdf->Cell('50%', '50%', '', 1, 0, 'C', false, '');

$pdf->SetY('80%');
$pdf->Cell(0, 10, '', 1);

$pdf->Output('I');