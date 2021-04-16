<?php
namespace Francerz\ExFPDF;

use FPDF;
use Francerz\ExFPDF\Barcode\Code128;
use Francerz\ExFPDF\Barcode\Code39;
use Francerz\ExFPDF\Table\Table;
use Francerz\Http\Utils\HttpFactoryManager;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class ExFPDF extends FPDF
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
    private $footerLimit = null;

    private $srcEncoding = null;
    private $pdfEncoding = 'ISO-8859-1//TRANSLIT';
    private $lineHeight = 1.0;

    private $closeMulticell = true;

    private $xyPins = array();

    public function __construct($orientation='P', $unit='mm', $size='A4')
    {
        $this->x = 0.0;
        $this->y = 0.0;
        parent::__construct($orientation, $unit, $size);
    }

    public function SetHeader(?callable $headerFunc, $headerHeight = null)
    {
        $this->headerFunc = $headerFunc;
        $this->headerHeight = $headerHeight;
    }
    public function SetFooter(?callable $footerFunc, $footerHeight = null)
    {
        $this->footerFunc = $footerFunc;
        $this->footerHeight = $footerHeight;
        if (isset($footerHeight)) {
            $this->bMargin = $footerHeight;
        }
    }

    public function BackStyles(&$family, &$style, &$fontsize, &$lw, &$dc, &$fc, &$tc, &$cf)
    {
        $family = $this->FontFamily;
        $style = $this->FontStyle.($this->underline ? 'U' : '');
        $fontsize = $this->FontSizePt;
        $lw = $this->LineWidth;
        $dc = $this->DrawColor;
        $fc = $this->FillColor;
        $tc = $this->TextColor;
        $cf = $this->ColorFlag;
    }

    public function RestoreStyles($family, $style, $fontsize, $lw, $dc, $fc, $tc, $cf)
    {
        $this->SetFont($family, $style, $fontsize);
        $this->DrawColor = $dc;
        $this->_out($dc);
        $this->FillColor = $fc;
        $this->_out($fc);
        $this->TextColor = $tc;
        $this->ColorFlag = $cf;
        $this->LineWidth = $lw;
        $this->_out(sprintf('%.2F w', $lw * $this->k));
    }

    public function Header()
    {
        if (!isset($this->headerFunc)) {
            return;
        }
        $this->BackStyles($family, $style, $fontsize, $lw, $dc, $fc, $tc, $cf);
        call_user_func($this->headerFunc, $this);
        $this->RestoreStyles($family, $style, $fontsize, $lw, $dc, $fc, $tc, $cf);
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
        $this->BackStyles($family, $style, $fontsize, $lw, $dc, $fc, $tc, $cf);
        call_user_func($this->footerFunc, $this);
        $this->RestoreStyles($family, $style, $fontsize, $lw, $dc, $fc, $tc, $cf);
    }

    public function GetHeaderBottom()
    {
        if (isset($this->headerHeight)) {
            return $this->tMargin + $this->headerHeight;
        }
        return $this->headerLimit;
    }
    public function GetFooterTop()
    {
        if (isset($this->footerHeight)) {
            return $this->h - $this->footerHeight;
        }
        if (isset($this->footerLimit)) {
            return $this->footerLimit;
        }
        return $this->h - $this->bMargin;
    }

    public function GetMarginTop()
    {
        return $this->tMargin;
    }

    public function GetMarginBottom()
    {
        return $this->bMargin;
    }

    public function GetBottom()
    {
        return $this->h - $this->bMargin;
    }

    public function AddPage($orientation = '', $size = '', $rotation = 0)
    {
        $this->headerLimit = 0;
        parent::AddPage($orientation, $size, $rotation);
        if (isset($this->headerFunc)) {
            $this->SetY($this->GetHeaderBottom());
        }
    }

    public function SetSourceEncoding($encoding)
    {
        $this->srcEncoding = $encoding;
    }

    public function SetLineHeight($lineHeight)
    {
        $this->lineHeight = $lineHeight;
    }

    public function SetFontStyle($style)
    {
        $this->setFont($this->FontFamily, $style, $this->FontSizePt);
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

    private function hex2rgb($hexStr, &$r, &$g, &$b)
    {
        $hexStr = preg_replace('/[^0-9A-Fa-f]/', '', $r);
        if (strlen($hexStr) == 6) {
            $colorVal = hexdec($hexStr);
            $r = 0xFF & ($colorVal >> 0x10);
            $g = 0xFF & ($colorVal >> 0x8);
            $b = 0xFF & $colorVal;
        } elseif (strlen($hexStr) == 3) {
            $r = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
            $g = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
            $b = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
        }
    }

    public function SetFillColor($r, $g = null, $b = null)
    {
        if (is_string($r)) {
            $this->hex2rgb($r, $r, $g, $b);
        }
        parent::SetFillColor($r, $g, $b);
    }

    public function SetTextColor($r, $g = null, $b = null)
    {
        if (is_string($r)) {
            $this->hex2rgb($r, $r, $g, $b);
        }
        parent::SetTextColor($r, $g, $b);
    }

    public function SetDrawColor($r, $g = null, $b = null)
    {
        if (is_string($r)) {
            $this->hex2rgb($r, $r, $g, $b);
        }
        parent::SetDrawColor($r, $g, $b);
    }

    /**
     * Adds a Text Cell on current position
     *
     * @param integer $w Cell width
     * @param integer $h Cell height (leave it null for autodetection based on line height).
     * @param string $txt Cell text content
     * @param integer $border Border width (1=ALL, 0=NONE, T=Top, B=Bottom, L=Left, R=Right)
     * @param integer $ln Line break (0=Right, 1=New Line, 2=Below)
     * @param string $align Text alignment (L=Left, R=Right, C=Center, J=Justified)
     * @param boolean $fill Cell will be filled with Fill Color
     * @param string $link Hyperlink for clickable cell
     * @return void
     */
    public function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
    {
        if (isset($this->srcEncoding) && is_string($txt)) {
            $txt = iconv($this->srcEncoding, $this->pdfEncoding, $txt);
        }
        if (is_null($h)) {
            $h = $this->FontSize * $this->lineHeight;
        }
        $w = $this->CalcWidth($w);
        $h = $this->CalcHeight($h);

        if ($this->InHeader) {
            $this->headerLimit = max($this->headerLimit, $this->y + $h);
        }
        if ($this->InFooter) {
            $this->footerLimit = min($this->footerLimit, $this->y);
        }

        parent::Cell($w, $h, $txt, $border, $ln, $align, $fill, $link);
    }
    /**
     * @deprecated 1.0.2 This method is deprecated in favor of SetSourceEncoding
     */
    public function CellUTF8($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
    {
        if (!isset($this->srcEncoding) && is_string($txt)) {
            $txt = iconv('UTF-8', $this->pdfEncoding, $txt);
        }
        $this->Cell($w, $h, $txt, $border, $ln, $align, $fill, $link);
    }

    public function CellRight($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='', $margin=0)
    {
        $x0 = $this->x;
        $x = $this->CalcWidth($w) * -1 - $this->rMargin - $margin;
        $this->SetX($x);
        $this->Cell($w, $h, $txt, $border, $ln, $align, $fill, $link);
        $this->x = $x0;
    }

    public function TextBlock($txt, $align = '')
    {
        $this->MultiCell('~100%', $this->FontSize * $this->lineHeight, $txt, 0, $align, false);
        $this->SetX('~0');
    }

    /**
     * Adds a Multiline cell in current position
     *
     * @param integer $w Cell Width
     * @param integer $h Cell Height (null=Automatic height based on line height)
     * @param string $txt Cell text content
     * @param integer $border Cell border (1=All, 0=None, T=Top, L=Left, B=Bottom, R=Right)
     * @param string $align Text content alignment (L=Left, R=Right, C=Center, J=Justify)
     * @param boolean $fill Cell must be filled with Fill Color
     * @return void
     */
    public function MultiCell($w, $h, $txt, $border = 0, $align = 'J', $fill = false)
    {
        if (is_null($h)) {
            $h = $this->FontSize * $this->lineHeight;
        }
        $w = $this->CalcWidth($w);
        $h = $this->CalcHeight($h);

        $startPage = $this->page;
        $startX = $this->x;
        $startY = $this->y;
        parent::MultiCell($w, $h, $txt, $border, $align, $fill);
        $endPage = $this->page;
        $endX = $this->x + $w;
        $endY = $this->y;
        
        if ($startPage != $endPage) {
            $border = $border == 1 ? 'TLBR' : $border;
            // has top
            if (strpos($border, 'T') !== false) {
                for ($p = $startPage + 1; $p <= $endPage; $p++) {
                    $this->SetPage($p);
                    $y = $this->GetHeaderBottom();
                    $this->Line($startX, $y, $endX, $y);
                }
            }
            // has bottom
            if (strpos($border, 'B') !== false) {
                $this->SetPage($startPage);
                $y = floor(($this->GetFooterTop() - $startY) / $h) * $h + $startY;
                $this->Line($startX, $y, $endX, $y);

                for ($p = $startPage+1; $p < $endPage; $p++) {
                    $this->SetPage($p);
                    $y = $this->GetHeaderBottom();
                    $y = floor(($this->GetFooterTop() - $y) / $h) * $h + $y;
                    $this->Line($startX, $y, $endX, $y);
                }
            }
            $this->SetPage($endPage);
            $this->SetY($endY);
        }
    }
    /**
     * @deprecated 1.0.2 This method is deprecated in favor of SetSourceEncoding
     */
    public function MultiCellUTF8($w, $h, $txt, $border = 0, $align = 'J', $fill = false)
    {
        if (!isset($this->srcEncoding) && is_string($txt)) {
            $txt = iconv('UTF-8', $this->pdfEncoding, $txt);
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

    public function Line($x1, $y1, $x2, $y2)
    {
        $x1 = $this->CalcX($x1);
        $x2 = $this->CalcX($x2);
        $y1 = $this->CalcY($y1);
        $y2 = $this->CalcY($y2);
        parent::Line($x1, $y1, $x2, $y2);
    }
    
    public function Rect($x, $y, $w, $h, $style = '')
    {
        $x = $this->CalcX($x);
        $y = $this->CalcY($y);
        $w = $this->CalcWidth($w);
        $h = $this->CalcHeight($h);
        parent::Rect($x, $y, $w, $h, $style);
    }

    public function Ln($h = null)
    {
        $h = $this->CalcHeight($h);
        if ($this->InHeader) {
            $this->headerLimit = max($this->headerLimit, $this->y + $h);
        }
        parent::Ln($h);
    }

    #region Barcode
    protected $barcoders = [];

    private function lnHandling($ln, $x, $y, $w, $h)
    {
        switch ($ln) {
            case static::LN_RIGHT:
                $this->x = $x + $w;
                $this->y = $y;
                break;
            case static::LN_BELOW:
                $this->x = $x;
                $this->y = $y + $h;
                break;
            case static::LN_NEW_LINE:
                $this->Ln($h);
                break;
        }
    }

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
    public function Barcode128(string $code, $w, $h, $x = '0+', $y = '0+', $ln = ExFPDF::LN_RIGHT)
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
        $this->lnHandling($ln, $x, $y, $w, $h);
    }

    public function Barcode39(string $code, $w, $h, $x = '0+', $y = '0+', $ln = ExFPDF::LN_RIGHT)
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
        $this->lnHandling($ln, $x, $y, $w, $h);
    }
    #endregion

    public function OutputPsr7(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        $name,
        $inline = true,
        $isUTF8 = false
    ) {
        $this->Close();

        $filename = $this->_httpencode('filename', $name, $isUTF8);
        $disposition = $inline ? 'inline' : 'attachment';

        return $responseFactory->createResponse()
            ->withHeader('Content-Type', 'application/pdf')
            ->withHeader('Content-Disposition', "{$disposition}; $filename")
            ->withHeader('Cache-Control', ['private','max-age=0','must-revalidate'])
            ->withHeader('Pragma', 'public')
            ->withBody($streamFactory->createStream($this->buffer));
    }

    public function OutputPsr7WithManager(HttpFactoryManager $hfm, $name, $inline = true, $isUTF8 = false)
    {
        return $this->OutputPsr7($hfm->getResponseFactory(), $hfm->getStreamFactory(), $name, $inline, $isUTF8);
    }

    public function SetPage($page)
    {
        $this->page = $page;
    }

    public function GetPage()
    {
        return $this->page;
    }

    public function CreateTable($widths)
    {
        $table = new Table($this, $widths);
        return $table;
    }

    protected function _beginpage($orientation, $size, $rotation)
    {
        $page = array_key_exists($this->page+1, $this->pages) ? $this->pages[$this->page+1] : '';
        parent::_beginpage($orientation, $size, $rotation);
        $this->pages[$this->page] = $page;
    }
}