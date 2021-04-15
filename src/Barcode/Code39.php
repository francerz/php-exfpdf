<?php

namespace Francerz\ExFPDF\Barcode;

class Code39
{
    protected $fpdf;

    protected $barChar;

    public function __construct(\FPDF $fpdf)
    {
        $this->fpdf = $fpdf;

        $barChar['0'] = 'nnnwwnwnn';
        $barChar['1'] = 'wnnwnnnnw';
        $barChar['2'] = 'nnwwnnnnw';
        $barChar['3'] = 'wnwwnnnnn';
        $barChar['4'] = 'nnnwwnnnw';
        $barChar['5'] = 'wnnwwnnnn';
        $barChar['6'] = 'nnwwwnnnn';
        $barChar['7'] = 'nnnwnnwnw';
        $barChar['8'] = 'wnnwnnwnn';
        $barChar['9'] = 'nnwwnnwnn';
        $barChar['A'] = 'wnnnnwnnw';
        $barChar['B'] = 'nnwnnwnnw';
        $barChar['C'] = 'wnwnnwnnn';
        $barChar['D'] = 'nnnnwwnnw';
        $barChar['E'] = 'wnnnwwnnn';
        $barChar['F'] = 'nnwnwwnnn';
        $barChar['G'] = 'nnnnnwwnw';
        $barChar['H'] = 'wnnnnwwnn';
        $barChar['I'] = 'nnwnnwwnn';
        $barChar['J'] = 'nnnnwwwnn';
        $barChar['K'] = 'wnnnnnnww';
        $barChar['L'] = 'nnwnnnnww';
        $barChar['M'] = 'wnwnnnnwn';
        $barChar['N'] = 'nnnnwnnww';
        $barChar['O'] = 'wnnnwnnwn'; 
        $barChar['P'] = 'nnwnwnnwn';
        $barChar['Q'] = 'nnnnnnwww';
        $barChar['R'] = 'wnnnnnwwn';
        $barChar['S'] = 'nnwnnnwwn';
        $barChar['T'] = 'nnnnwnwwn';
        $barChar['U'] = 'wwnnnnnnw';
        $barChar['V'] = 'nwwnnnnnw';
        $barChar['W'] = 'wwwnnnnnn';
        $barChar['X'] = 'nwnnwnnnw';
        $barChar['Y'] = 'wwnnwnnnn';
        $barChar['Z'] = 'nwwnwnnnn';
        $barChar['-'] = 'nwnnnnwnw';
        $barChar['.'] = 'wwnnnnwnn';
        $barChar[' '] = 'nwwnnnwnn';
        $barChar['*'] = 'nwnnwnwnn';
        $barChar['$'] = 'nwnwnwnnn';
        $barChar['/'] = 'nwnwnnnwn';
        $barChar['+'] = 'nwnnnwnwn';
        $barChar['%'] = 'nnnwnwnwn';

        $this->barChar = $barChar;
    }

    public function Draw(float $x, float $y, string $code, float $w, float $h)
    {
        // total width
        // divided by $code length + 2 '*'
        // divided by 16 (each char is 15 units wide + 1 gap)
        $baseline = $w / (strlen($code) + 2) / 16;

        $wide = $baseline * 3;
        $narrow = $baseline; 
        $gap = $baseline;

        $code = '*'.strtoupper($code).'*';
        for ($i = 0; $i < strlen($code); $i++) {
            $char = $code[$i];
            if (!isset($this->barChar[$char])) {
                $this->fpdf->Error('Invalid character in barcode: '.$char);
            }
            $seq = $this->barChar[$char];
            for ($bar = 0; $bar < 9; $bar++) {
                $lineWidth = $wide;
                if ($seq[$bar] == 'n') {
                    $lineWidth = $narrow;
                }
                if ($bar % 2 == 0) {
                    $this->fpdf->Rect($x, $y, $lineWidth, $h, 'F');
                }
                $x += $lineWidth;
            }
            $x += $gap;
        }
    }
}
