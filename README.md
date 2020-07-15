EFPDF (Extended Free PDF)
=======================================

This library extends basic functionality of the FPDF class.

FPDF is a PHP class which allows to generate PDF files with pure PHP.
F from FPDF stands for Free: you may use it for any kind of usage and modify it
to suit your needs.

Installation with Composer
---------------------------------------

Add the repository and require to your **composer.json** file:
```json
    {
        "repositories": [{
            "type":"vcs",
            "url":"https://github.com/francerz/EFPDF"
        }],
        "require": {
            "francerz/efpdf": "master"
        }
    }
```

Extended functionality
---------------------------------------

### Header and Footer

Allows to define Header and Footer using anonymous functions.

* `SetHeader(callable $headerFunc)`
* `SetFooter(callable $footerFunc, $footerHeight = null)`

Anonymous functions will receive a parameter of type EFPDF wich is the $pdf
instance.

```php
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
```
> **Note:**
> It's important to invoke `SetHeader()` and `SetFooter()` before `AddPage()`.

