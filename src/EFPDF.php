<?php
namespace Francerz\EFPDF;

use FPDF;

class EFPDF extends FPDF
{
    const STYLE_NONE = '';
    const STYLE_BOLD = 'B';
    const STYLE_ITALIC = 'I';
    const STYLE_BOLD_ITALIC = 'BI';

    private $headerFunc;
    private $footerFunc;
    private $headerHeight;
    private $footerHeight;
    private $headerLimit = 0;

    private $xyPins = array();

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

    const REL_PATTERN = '/^(\[\])?~([-+]?\\d+(?:\\.\\d+)?)$/';
    const PCT_PATTERN = '/^(\[\])?(~)?([-+]?\\d+(?:\\.\\d+)?)%$/';
    public function CalcWidth($w)
    {
        if (preg_match(self::PCT_PATTERN, $w, $matches)) {
            $pw = $this->w;
            if ($matches[2] == '~') {
                $pw -= $this->lMargin + $this->rMargin;
            }
            $w = $matches[3] * $pw / 100;
        }
        return $w;
    }
    public function CalcHeight($h)
    {
        if (preg_match(self::PCT_PATTERN, $h, $matches)) {
            $ph = $this->h;
            if ($matches[2] == '~') {
                $ph -= $this->tMargin + $this->bMargin;
            }
            $h = $matches[3] * $ph / 100;
        }
        return $h;
    }
    public function CalcX($x)
    {
        if (preg_match(self::REL_PATTERN, $x, $matches)) {
            $x = $matches[2] + $this->lMargin;
        }
        return $this->CalcWidth($x);
    }
    public function CalcY($y)
    {
        if (preg_match(self::REL_PATTERN, $y, $matches)) {
            $y = $matches[2] + $this->tMargin;
        }
        return $this->CalcHeight($y);
    }
    public function SetX($x)
    {
        $x = $this->CalcX($x);
        parent::SetX($x);
    }
    public function SetY($y, $resetX = true)
    {
        $y = $this->CalcY($y);
        parent::SetY($y, $resetX);
    }
    public function SetPin($coordName, $x = null, $y = null)
    {
        $this->xyPins[$coordName] = array(
            'x'=> $x ?? $this->x,
            'y'=> $y ?? $this->y
        );
    }
    public function GetPinX($coordName)
    {
        return $this->xyPins[$coordName]['x'];
    }
    public function GetPinY($coordName)
    {
        return $this->xyPins[$coordName]['y'];
    }
    public function MoveToPin($coordName)
    {
        $this->x = $this->GetPinX($coordName);
        $this->y = $this->GetPinY($coordName);
    }
    public function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
    {
        $w = $this->CalcWidth($w);
        $h = $this->CalcHeight($h);
        parent::Cell($w, $h, $txt, $border, $ln, $align, $fill, $link);
        if ($this->InHeader) {
            $this->headerLimit = max($this->headerLimit, $this->y + $h);
        }
    }
    public function CellUTF8($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
    {
        if (is_string($txt)) {
            $txt = iconv('UTF-8','ISO-8859-1', $txt);
        }
        $this->Cell($w, $h, $txt, $border, $ln, $align, $fill, $link);
    }
    public function CellRight($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='', $margin=0)
    {
        $x0 = $this->x;
        $x = $this->CalcWidth($w) * -1 - $this->rMargin - $margin;
        $this->SetX($x);
        $this->CellUTF8($w, $h, $txt, $border, $ln, $align, $fill, $link);
        $this->x = $x0;
    }
    public function MultiCell($w, $h, $txt, $border = 0, $align = 'J', $fill = false)
    {
        $w = $this->CalcWidth($w);
        $h = $this->CalcHeight($h);
        parent::MultiCell($w, $h, $txt, $border, $align, $fill);
    }
    public function MultiCellUTF8($w, $h, $txt, $border = 0, $align = 'J', $fill = false)
    {
        if (is_string($txt)) {
            $txt = iconv('UTF-8','ISO-8859-1', $txt);
        }
        $this->MultiCell($w, $h, $txt, $border, $align, $fill);
    }
    public function Image($file, $x = null, $y = null, $w = 0, $h = 0, $type = '', $link = '')
    {
        $x = $this->CalcX($x);
        $y = $this->CalcY($y);
        $w = $this->CalcWidth($w);
        $h = $this->CalcHeight($h);
        parent::Image($file, $x, $y, $w, $h, $type, $link);
    }
}