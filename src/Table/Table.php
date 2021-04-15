<?php

namespace Francerz\ExFPDF\Table;

use Exception;
use Francerz\ExFPDF\ExFPDF;

class Table
{
    private $pdf;
    private $widths;
    private $drawed = false;
    private $outerBorder = 'TBLR';
    private $innerBorder = 'TBLR';

    private $rows = [];

    public function __construct(ExFPDF $pdf, array $widths)
    {
        $this->pdf = $pdf;
        $this->SetColumnWidths($widths);
    }

    private function SetColumnWidths(array $widths)
    {
        $kWidths = [];
        foreach ($widths as $w) {
            $kWidths[] = $this->pdf->CalcWidth($w);
        }
        $this->widths = $kWidths;
    }

    public function SetOuterBorder(string $border = 'TBLR')
    {
        $this->outerBorder = $border;
    }

    public function GetOuterBorder()
    {
        return $this->outerBorder;
    }

    public function SetInnerBorder(string $border = 'TBLR')
    {
        $this->innerBorder = $border;
    }

    public function GetInnerBorder()
    {
        return $this->innerBorder;
    }

    public function AddRow()
    {
        if ($this->drawed) {
            throw new Exception("Cannot add row because table is already drawned");
        }
        return $this->rows[] = new Row($this->pdf, $this);
    }

    public function GetCellWidth(int $column, int $colspan = 1)
    {
        if (!isset($this->widths[$column])) {
            return 0;
        }

        if ($colspan === 1) {
            return $this->widths[$column];
        }

        $result = 0;
        $lastcol = $column + $colspan;
        for ($i = $column; $i < $lastcol; $i++) {
            $result += $this->widths[$i] ?? 0;
        }
        return $result;
    }

    public function DrawBorders()
    {
        $this->drawed = true;
        $row = end($this->rows);
        $row->SetIsLast();
        $row = reset($this->rows);
        $row->SetIsFirst();
        foreach ($this->rows as $k => $row) {
            $row->DrawBorders();
        }
    }
}