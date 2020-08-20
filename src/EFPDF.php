<?php
namespace Francerz\EFPDF;

use FPDF;
use Francerz\EFPDF\Barcode\Code128;
use Francerz\EFPDF\Barcode\Code39;

class EFPDF extends FPDF
{
    const WIDTH_FILL = 0;

    const STYLE_NONE = '';
    const STYLE_BOLD = 'B';
    const STYLE_ITALIC = 'I';
    const STYLE_BOLD_ITALIC = 'BI';

    const BORDER_NONE = 0;
    const BORDER_ALL = 1;
    const BORDER_LEFT = 'L';
    const BORDER_RIGHT = 'R';
    const BORDER_TOP = 'T';
    const BORDER_BOTTOM = 'B';
    const BORDER_SIDES = 'LR';
    const BORDER_TOP_BOTTOM = 'TB';
    const BORDER_LEFT_RIGHT = 'LR';

    const LN_RIGHT = 0;
    const LN_NEW_LINE = 1;
    const LN_BELOW = 2;

    const ALIGN_LEFT = 'L';
    const ALIGN_RIGHT = 'R';
    const ALIGN_CENTER = 'C';
    const ALIGN_JUSTIFICATION = 'J';

    private $headerFunc;
    private $footerFunc;
    private $headerHeight;
    private $footerHeight;
    private $headerLimit = 0;

    private $xyPins = array();

    public function __construct($orientation='P', $unit='mm', $size='A4')
    {
        $this->x = 0.0;
        $this->y = 0.0;
        parent::__construct($orientation, $unit, $size);
    }

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

    const MEASURE_PATTERN = '/^(~?)([-+]?\\d+(?:\\.\\d+)?)(%?)([-+]?)$/';

    private static function MeasurePatternMatch(
        $measure,
        &$len=0.0,
        &$pct=false,
        &$rel=false,
        &$ref=''
    ) {
        $match = preg_match(self::MEASURE_PATTERN, $measure, $matches);
        if (!$match) return false;

        $rel = $matches[1] === '~';
        $len = $matches[2];
        $pct = $matches[3] === '%';
        $ref = $matches[4];

        return $match;
    }

    private static function CalcRelativeMeasure(
        float $value,
        float $current,
        float $margin,
        bool $rel=false,
        string $ref=''
    ) {
        if ($ref === '+') {
            return $current + $value;
        } elseif ($ref === '-') {
            return $current - $value;
        } elseif ($rel) {
            return $margin + $value;
        }
        return $value;
    }

    private static function CalcSize(
        float $len,
        float $totalSize,
        float $marginSize,
        bool $rel = false,
        bool $pct = false
    ) {
        if ($pct) {
            $wide = $rel ? $totalSize - $marginSize : $totalSize;
            $len = $wide * $len / 100;
        }
        return $len;
    }

    public function CalcWidth($w)
    {
        $match = static::MeasurePatternMatch($w, $len, $pct, $rel, $ref);

        if ($match) {
            $w = static::CalcSize($len, $this->w, $this->lMargin + $this->rMargin, $rel, $pct);
        }
        return $w;
    }
    public function CalcHeight($h)
    {
        $match = static::MeasurePatternMatch($h, $len, $pct, $rel, $ref);

        if ($match) {
            $h = static::CalcSize($len, $this->h, $this->tMargin + $this->bMargin, $rel, $pct);
        }
        return $h;
    }

    public function CalcX($x)
    {
        $match = static::MeasurePatternMatch($x, $len, $pct, $rel, $ref);

        if ($match) {
            $x = static::CalcSize($len, $this->w, $this->lMargin + $this->rMargin, $rel, $pct);
            $x = static::CalcRelativeMeasure($x, $this->x, $this->lMargin, $rel, $ref);
        }
        return $x;
    }

    public function CalcY($y)
    {
        $match = static::MeasurePatternMatch($y, $len, $pct, $rel, $ref);
        if ($match) {
            $y = static::CalcSize($len, $this->h, $this->tMargin + $this->bMargin, $rel, $pct);
            $y = static::CalcRelativeMeasure($y, $this->y, $this->tMargin, $rel, $ref);
        }
        return $y;
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
            'x'=> isset($x) ? $this->CalcX($x) : $this->x,
            'y'=> isset($y) ? $this->CalcY($y) : $this->y
        );
    }
    public function GetPinX($coordName)
    {
        if (!array_key_exists($coordName, $this->xyPins)) {
            $this->Error("Unknown Pin coordinates '{$coordName}'");
        }
        return $this->xyPins[$coordName]['x'];
    }
    public function GetPinY($coordName)
    {
        if (!array_key_exists($coordName, $this->xyPins)) {
            $this->Error("Unknown Pin coordinates '{$coordName}'");
        }
        return $this->xyPins[$coordName]['y'];
    }
    public function MoveToPin($coordName, $axis = 'XY', $offset = 0, $offsetY = 0)
    {
        $axis = strtoupper($axis);
        if ($axis == 'Y') {
            $offsetY = $this->CalcY($offset);
            $offset = 0;
        } else {
            $offset = $this->CalcX($offset);
            $offsetY = $this->CalcY($offsetY);
        }
        if (strpos($axis, 'X') !== false) {
            $this->x = $this->GetPinX($coordName) + $offset;
        }
        if (strpos($axis, 'Y') !== false) {
            $this->y = $this->GetPinY($coordName) + $offsetY;
        }
    }
    public function OffsetX($offset)
    {
        $offset = $this->CalcX($offset);
        $this->x += $offset;
    }
    public function OffsetY($offset)
    {
        $offset = $this->CalcY($offset);
        $this->y += $offset;
    }
    public function OffsetXY($offsetX, $offsetY)
    {
        $this->OffsetX($offsetX);
        $this->OffsetY($offsetY);
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
            $txt = iconv('UTF-8','ISO-8859-1//TRANSLIT', $txt);
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
            $txt = iconv('UTF-8','ISO-8859-1//TRANSLIT', $txt);
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

    #region Barcode
    protected $barcoders = [];
    /**
     * Puts an ASCII characters string with barcode format code128.
     *
     * @param float|string $x
     * @param float|string $y
     * @param float|string $w
     * @param float|string $h
     * @param string $code
     * @return void
     */
    public function barcode128(string $code, $w, $h, $x = '0+', $y = '0+')
    {
        if (!array_key_exists('code128', $this->barcoders)) {
            $this->barcoders['code128'] = new Code128($this);
        }
        $barcoder = $this->barcoders['code128'];

        $x = $this->CalcX($x);
        $y = $this->CalcY($y);
        $w = $this->CalcWidth($w);
        $h = $this->CalcHeight($h);
        $barcoder->Draw($x, $y, $code, $w, $h);
    }
    public function barcode39(string $code, $w, $h, $x = '0+', $y = '0+')
    {
        if (!array_key_exists('code39', $this->barcoders)) {
            $this->barcoders['code39'] = new Code39($this);
        }
        $barcoder = $this->barcoders['code39'];

        $x = $this->CalcX($x);
        $y = $this->CalcY($y);
        $w = $this->CalcWidth($w);
        $h = $this->CalcHeight($h);
        $barcoder->Draw($x, $y, $code, $w, $h);
    }
    #endregion
}