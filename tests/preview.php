<?php
require "../vendor/autoload.php";

use Francerz\EFPDF\EFPDF;

$userName = "My Name";

$pdf = new EFPDF();
$pdf->AliasNbPages();
$pdf->SetFont('Arial', '', 12);
$pdf->SetHeader(function(EFPDF $efpdf) use ($userName) {
    $efpdf->Cell(190, 6, "Hello {$userName}", 1);
});
$pdf->SetFooter(function(EFPDF $efpdf) {
    $efpdf->Cell(190, 6, 'Page '.$efpdf->PageNo(), 1, 0, 'R');
}, 15);
$pdf->AddPage();

$pdf->Output('I');