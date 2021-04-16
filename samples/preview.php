<?php
define('ROOT_PATH', dirname(__DIR__));
define('FPDF_IMGPATH', ROOT_PATH.'/samples/img');

require ROOT_PATH.'/vendor/autoload.php';

use Francerz\ExFPDF\ExFPDF;

$userName = "My Name";

$pdf = new ExFPDF();
$pdf->AliasNbPages();
$pdf->SetSourceEncoding('UTF-8');
$pdf->SetFont('Arial', ExFPDF::STYLE_NONE, 12);
$pdf->SetHeader(function(ExFPDF $exfpdf) use ($userName) {
    $exfpdf->SetTextColor('#0000FF');
    $exfpdf->SetFont('Arial', ExFPDF::STYLE_BOLD, 16);
    $exfpdf->Cell('~100%', 10, "Hello {$userName}!", ExFPDF::BORDER_NONE);
});
$pdf->SetFooter(function(ExFPDF $exfpdf) {
    $exfpdf->SetTextColor('#FF0000');
    $exfpdf->SetFont('', 'B', 10);
    $exfpdf->Cell('~100%', 10, 'Page '.$exfpdf->PageNo(), ExFPDF::BORDER_NONE, ExFPDF::LN_NEW_LINE, ExFPDF::ALIGN_RIGHT);
    $exfpdf->Cell('~100%', 10, 'Company name', ExFPDF::BORDER_NONE, ExFPDF::LN_NEW_LINE, ExFPDF::ALIGN_CENTER);
}, 30);

$pdf->AddPage('P','Letter');

$pdf->SetX('~0');
$pdf->SetPin('top-left');

$pdf->Cell('~100%', 20, 'THIS IS A HEADING', ExFPDF::BORDER_ALL, ExFPDF::LN_BELOW, ExFPDF::ALIGN_CENTER);
$pdf->Image(
    FPDF_IMGPATH.'/logo.gif',
    $pdf->GetPinX('top-left') + 1,
    $pdf->GetPinY('top-left')
);

$pdf->CellRight(15, 5, 'Date: ', ExFPDF::BORDER_NONE, ExFPDF::LN_RIGHT, ExFPDF::ALIGN_RIGHT, false, '', 30);
$pdf->CellRight(30, 5, date('Y-m-d'), ExFPDF::BORDER_ALL, ExFPDF::LN_NEW_LINE, ExFPDF::ALIGN_CENTER, false, '', 0);

$pdf->Cell(0, 10, 'Benjamín pidió una bebida de kiwi y fresa; Noé, sin vergüenza, la más exquisita champaña del menú.');

$pdf->SetXY('25%', '25%');
$pdf->Cell('50%', '50%', '', ExFPDF::BORDER_ALL, ExFPDF::LN_RIGHT, ExFPDF::ALIGN_CENTER);

$pdf->SetY('80%');
$pdf->Cell(0, 10, '', ExFPDF::BORDER_ALL);

$pdf->Barcode128('17194608071620010020082321509', 70, 8, $pdf->CalcX('~50%')-39, $pdf->CalcY('~50%')-4);
$pdf->Barcode39('17194608071620010020082321509', 70, 8, $pdf->CalcX('~50%')-39, $pdf->CalcY('~50%')+8);

$alpha = "A\nB\nC\nD\nE\nF\nG\nH\nI\nJ\nK\nL\nM\nN\nO\nP\nQ\nR\nS\nT\nU\nV\nW\nX\nY\nZ\n";
$pdf->SetY('85%');
$pdf->MultiCell('~100%', null, $alpha.$alpha.$alpha.$alpha, 1);

$pdf->AddPage();
$pdf->SetY('~20%');

$table = $pdf->CreateTable(['~20%','~20%','~30%','~10%','~20%']);
$row = $table->AddRow();
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