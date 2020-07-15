<?php
namespace Francerz\EFPDF;

use FPDF;

class EFPDF extends FPDF
{
    private $headerFunc;
    private $footerFunc;
    private $footerHeight;

    public function SetHeader(callable $headerFunc)
    {
        $this->headerFunc = $headerFunc;
    }
    public function SetFooter(callable $footerFunc, $footerHeight = null)
    {
        $this->footerFunc = $footerFunc;
        $this->footerHeight = $footerHeight;
    }

    public function Header()
    {
        parent::Header();
        if (isset($this->headerFunc)) {
            $headerFunc = $this->headerFunc;
            $headerFunc($this);
        }
    }
    
    public function Footer()
    {
        parent::Footer();
        if (!isset($this->footerFunc)) {
            return;
        }
        if (isset($this->footerHeight)) {
            $this->SetY($this->footerHeight * -1);
        }

        $footerFunc = $this->footerFunc;
        $footerFunc($this);
    }
}