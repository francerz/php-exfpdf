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

### Percentual Cell Size

It's allowed to set Cell width and height to a percentual of page size.
Also this applies to X and Y coordinates.

```php
// Puts pointer at 1/4 top and left of page.
$pdf->SetXY('25%', '25%');
// Draws Cell at page center with 50% page width and height size.
$pdf->Cell('50%','50%', 'Center content', 1, 0, 'C');
```

Sizes and positions might be calculated with margins using the `~` character
before the numeric value.

```php
// Puts pointer at left and top margin.
$pdf->SetXY('~0', '~0');
// Draws Cell with 100% content region width and 20 units height.
$pdf->Cell('~100%', 20, 'THIS IS A HEADING', 1, 1, 'C');
```

### Direct UTF-8 decoding text Cell

`CellUTF8($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')`

Performs UTF-8 decoding strings without putting right into the code.

```php
$pdf->CellUTF8(0, 10, 'Benjamín pidió una bebida de kiwi y fresa; Noé, sin vergüenza, la más exquisita champaña del menú.');
```

### Right aligned Cell

`CellRight($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='', $margin=0)`

Puts a cell aligned to the right margin of the page.
Optionally `$margin` can be set to displace the Cell from the right margin.

```php
$pdf->CellRight(15, 5, 'Date: ', 0, 0, 'R', false, '', 30);
$pdf->CellRight(30, 5, date('Y-m-d'), 1, 1, 'C', false, '', 0);
```

### Header and Footer

Allows to define Header and Footer using anonymous functions.

* `SetHeader(callable $headerFunc, $headerHeight = null)`  
  Sets a callable function that will execute when Header is loading.
  If parameter `$headerHeight` is null, then will be calculated and body content
  will be displaced. If is set, then content will be displaced `$headerHeight`
  plus the top margin.
* `SetFooter(callable $footerFunc, $footerHeight = null)`  
  Sets a callable function that will execute when Footer is loading.
  If parameter `$footerHeight` is null, then will be calculated and page break
  will executed before reaching footer content. If is set, the footer will
  be displaced from based on page bottom edge.

```php
$userName = "My Name";

$pdf = new EFPDF();
$pdf->AliasNbPages();
$pdf->SetFont('Arial', '', 12);
$pdf->SetHeader(function(EFPDF $efpdf) use ($userName) {
    $efpdf->Cell('~100%', 10, "Hello {$userName}", 1);
});
$pdf->SetFooter(function(EFPDF $efpdf) {
    $efpdf->Cell('~100%', 10, 'Page '.$efpdf->PageNo(), 1, 0, 'R');
});
$pdf->AddPage();

$pdf->Output('I');
```
> **Note:**
> It's important to invoke `SetHeader()` and `SetFooter()` before `AddPage()`.

