<?php
define('ROOT_PATH', dirname(__DIR__));
define('FPDF_IMGPATH', ROOT_PATH.'/samples/img');

require ROOT_PATH.'/vendor/autoload.php';

use Francerz\EFPDF\EFPDF;

$userName = "My Name";

$pdf = new EFPDF();
$pdf->AliasNbPages();
$pdf->SetSourceEncoding('UTF-8');
$pdf->SetFont('Arial', EFPDF::STYLE_NONE, 12);
$pdf->SetHeader(function(EFPDF $efpdf) use ($userName) {
    $efpdf->SetTextColor('#0000FF');
    $efpdf->SetFont('Arial', EFPDF::STYLE_BOLD, 16);
    $efpdf->Cell('~100%', 10, "Hello {$userName}!", EFPDF::BORDER_NONE);
});
$pdf->SetFooter(function(EFPDF $efpdf) {
    $efpdf->SetTextColor('#FF0000');
    $efpdf->SetFont('', 'B', 10);
    $efpdf->Cell('~100%', 10, 'Page '.$efpdf->PageNo(), EFPDF::BORDER_NONE, EFPDF::LN_NEW_LINE, EFPDF::ALIGN_RIGHT);
    $efpdf->Cell('~100%', 10, 'Company name', EFPDF::BORDER_NONE, EFPDF::LN_NEW_LINE, EFPDF::ALIGN_CENTER);
}, 30);

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

$pdf->Cell(0, 10, 'Benjamín pidió una bebida de kiwi y fresa; Noé, sin vergüenza, la más exquisita champaña del menú.');

$pdf->SetXY('25%', '25%');
$pdf->Cell('50%', '50%', '', EFPDF::BORDER_ALL, EFPDF::LN_RIGHT, EFPDF::ALIGN_CENTER);

$pdf->SetY('80%');
$pdf->Cell(0, 10, '', EFPDF::BORDER_ALL);

$pdf->Barcode128('17194608071620010020082321509', 70, 8, $pdf->CalcX('~50%')-39, $pdf->CalcY('~50%')-4);
$pdf->Barcode39('17194608071620010020082321509', 70, 8, $pdf->CalcX('~50%')-39, $pdf->CalcY('~50%')+8);

$pdf->AddPage();
$pdf->SetY('~20%');

$table = $pdf->CreateTable(['~20%','~20%','~30%','~10%','~20%']);
$row = $table->AddRow();
$alpha = "A\nB\nC\nD\nE\nF\nG\nH\nI\nJ\nK\nL\nM\nN\nO\nP\nQ\nR\nS\nT\nU\nV\nW\nX\nY\nZ\n";
$row->Cell("A B C D E F G H I J K L M N O P Q R S T U V W X Y Z", 'C', false, 2);
$row = $table->AddRow();
$row->Cell($alpha);
$row->Cell($alpha);
$row = $table->AddRow();
$row->Cell($alpha.$alpha.$alpha.$alpha);
$row->Cell($alpha);
$row = $table->AddRow();
$row->Cell($alpha);
$row->Cell($alpha);
$table->DrawBorders();

$pdf->Output('I');