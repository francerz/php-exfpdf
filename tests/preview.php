<?php
define('ROOT_PATH', dirname(__DIR__));
define('FPDF_IMGPATH', ROOT_PATH.'/tests/img');

require ROOT_PATH.'/vendor/autoload.php';

use Francerz\EFPDF\EFPDF;

$userName = "My Name";

$pdf = new EFPDF();
$pdf->AliasNbPages();
$pdf->SetFont('Arial', EFPDF::STYLE_NONE, 12);
$pdf->SetHeader(function(EFPDF $efpdf) use ($userName) {
    $efpdf->SetFont('Arial', EFPDF::STYLE_BOLD, 16);
    $efpdf->CellUTF8('~100%', 10, "Hello {$userName}!", EFPDF::BORDER_ALL);
});
$pdf->SetFooter(function(EFPDF $efpdf) {
    $efpdf->CellUTF8('~100%', 10, 'Page '.$efpdf->PageNo(), EFPDF::BORDER_ALL, EFPDF::LN_RIGHT, EFPDF::ALIGN_RIGHT);
});

$pdf->AddPage('P','Letter');

$pdf->SetX('~0');
$pdf->SetPin('top-left');

$pdf->Cell('~100%', 20, 'THIS IS A HEADING', EFPDF::BORDER_ALL, EFPDF::LN_BELOW, EFPDF::ALIGN_CENTER);
$pdf->Image(
    FPDF_IMGPATH.'/logo.gif',
    $pdf->GetPinX('top-left') + 1,
    $pdf->GetPinY('top-left')
);

$pdf->CellRight(15, 5, 'Date: ', EFPDF::BORDER_NONE, EFPDF::LN_RIGHT, EFPDF::ALIGN_RIGHT, false, '', 30);
$pdf->CellRight(30, 5, date('Y-m-d'), EFPDF::BORDER_ALL, EFPDF::LN_NEW_LINE, EFPDF::ALIGN_CENTER, false, '', 0);

$pdf->CellUTF8(0, 10, 'Benjamín pidió una bebida de kiwi y fresa; Noé, sin vergüenza, la más exquisita champaña del menú.');

$pdf->SetXY('25%', '25%');
$pdf->Cell('50%', '50%', '', EFPDF::BORDER_ALL, EFPDF::LN_RIGHT, EFPDF::ALIGN_CENTER);

$pdf->SetY('80%');
$pdf->Cell(0, 10, '', EFPDF::BORDER_ALL);

$pdf->Output('I');