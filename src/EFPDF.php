<?php
namespace Francerz\EFPDF;

use FPDF;

class EFPDF extends FPDF
{
    private $headerFunc;
    private $footerFunc;
    private $headerHeight;
    private $footerHeight;
    private $headerLimit = 0;

    public function SetHeader(callable $headerFunc, $headerHeight = null)
    {
        $this->headerFunc = $headerFunc;
        $this->headerHeight = $headerHeight;
    }
    public function SetFooter(callable $footerFunc, $footerHeight = null)
    {
        $this->footerFunc = $footerFunc;
        $this->footerHeight = $footerHeight;
    }

    public function Header()
    {
        if (isset($this->headerFunc)) {
            $headerFunc = $this->headerFunc;
            $headerFunc($this);
        }
    }
    
    public function Footer()
    {
        if (!isset($this->footerFunc)) {
            return;
        }
        if (isset($this->footerHeight)) {
            $this->SetY($this->footerHeight * -1);
        } else {
            $this->SetY($this->bMargin * -1);
        }

        $footerFunc = $this->footerFunc;
        $footerFunc($this);
    }
    public function GetHeaderBottom()
    {
        if (isset($this->headerHeight)) {
            return $this->tMargin + $this->headerHeight;
        }
        return $this->headerLimit;
    }
    public function AddPage($orientation = '', $size = '', $rotation = 0)
    {
        $this->headerLimit = 0;
        parent::AddPage($orientation, $size, $rotation);
        if (isset($this->headerFunc)) {
            $this->SetY($this->GetHeaderBottom());
        }
    }

    const REL_PATTERN = '/^~(\\d+)$/';
    const PCT_PATTERN = '/^(~)?([0-9]+)%$/';
    protected function calcHorzSize($w)
    {
        if (preg_match(self::PCT_PATTERN, $w, $matches)) {
            $pw = $this->w;
            if ($matches[1] == '~') {
                $pw -= $this->lMargin + $this->rMargin;
            }
            $w = $matches[2] * $pw / 100;
        }
        return $w;
    }
    protected function calcVertSize($h)
    {
        if (preg_match(self::PCT_PATTERN, $h, $matches)) {
            $ph = $this->h;
            if ($matches[1] == '~') {
                $ph -= $this->tMargin + $this->bMargin;
            }
            $h = $matches[2] * $ph / 100;
        }
        return $h;
    }
    public function SetX($x)
    {
        if (preg_match(self::REL_PATTERN, $x, $matches)) {
            $x = $matches[1] + $this->lMargin;
        }
        parent::SetX($this->calcHorzSize($x));
    }
    public function SetY($y, $resetX = true)
    {
        if (preg_match(self::REL_PATTERN, $y, $matches)) {
            $y = $matches[1] + $this->tMargin;
        }
        parent::SetY($this->calcVertSize($y), $resetX);
    }
    public function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
    {
        $w = $this->calcHorzSize($w);
        $h = $this->calcVertSize($h);
        parent::Cell($w, $h, $txt, $border, $ln, $align, $fill, $link);
        if ($this->InHeader) {
            $this->headerLimit = max($this->headerLimit, $this->y + $h);
        }
    }
    public function CellUTF8($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
    {
        $this->Cell($w, $h, iconv('UTF-8','ISO-8859-1', $txt), $border, $ln, $align, $fill, $link);
    }
    public function CellRight($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='', $margin=0)
    {
        $x0 = $this->x;
        $x = $this->calcHorzSize($w) * -1 - $this->rMargin - $margin;
        $this->SetX($x);
        $this->Cell($w, $h, $txt, $border, $ln, $align, $fill, $link);
        $this->x = $x0;
    }
}