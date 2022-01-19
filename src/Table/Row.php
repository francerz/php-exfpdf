<?php

namespace Francerz\ExFPDF\Table;

use Francerz\ExFPDF\ExFPDF;
use Iterator;
use phpDocumentor\Reflection\Types\This;

class Row
{
    private $pdf;
    private $table;

    private $drawed = false;
    private $isFirst = false;
    private $isLast = false;

    private $startPage;
    private $endPage;
    private $startY;
    private $startX;
    private $bottom = 0;
    private $cell = 0;
    private $cellsMeta = [];
    private $x;

    public function __construct(ExFPDF $pdf, Table $table)
    {
        $this->pdf = $pdf;
        $this->table = $table;
        $this->startPage = $this->endPage = $pdf->GetPage();
        $this->startY = $pdf->GetY();
        $this->startX = $this->x = $pdf->GetX();
    }

    public function SetIsFirst($first = true)
    {
        $this->isFirst = $first;
    }

    public function SetIsLast($last = true)
    {
        $this->isLast = $last;
    }

    public function Cell($txt, $align = 'J', $fill = false, $colspan = 1, $rowspan = 1)
    {
        // Sets starting point
        $this->pdf->SetPage($this->startPage);
        $this->pdf->SetXY($this->x, $this->startY);

        // Calcs width and border stops
        $w = $this->table->GetCellWidth($this->cell, $colspan);
        $this->cellsMeta[] = new CellMeta($this->x, $this->startY, $w);
        $this->x += $w;
        $this->cell += $colspan;
        
        // Draws the cell
        $this->pdf->MultiCell($w, null, $txt, 0, $align, $fill);

        // Updates bottom and endPage
        $page = $this->pdf->GetPage();
        $bottom = $this->pdf->GetY();
        if ($page == $this->endPage) {
            $this->bottom = $bottom > $this->bottom ? $bottom : $this->bottom;
        } elseif ($page > $this->endPage) {
            $this->bottom = $bottom;
            $this->endPage = $page;
        }

        $this->pdf->SetPage($this->endPage);
        $this->pdf->SetXY($this->startX, $this->bottom);
    }

    public function CellSpan($fill=false, $colspan=1)
    {
        $this->Cell('', 'J', $fill, $colspan);
    }

    public function Ln($height)
    {
        $bottom = $this->bottom + $height;
        $this->bottom = $bottom > $this->bottom ? $bottom : $this->bottom;
    }

    private function DrawTopLine($page, $x1, $x2, $y)
    {
        $border = $this->table->GetOuterBorder();
        if (stripos($border, 'T') !== false) {
            $this->pdf->SetPage($page);
            $this->pdf->Line($x1, $y, $x2, $y);
        }
    }

    private function DrawBottomLine($page, $x1, $x2, $y)
    {
        $border = $this->isLast ?
            $this->table->GetOuterBorder() :
            $this->table->GetInnerBorder();
        if (stripos($border,'B') !== false) {
            $this->pdf->SetPage($page);
            $this->pdf->Line($x1, $y, $x2, $y);
        }
    }

    private function DrawLeftLine($page, $y1, $y2, $x)
    {
        $this->pdf->SetPage($page);
        $this->pdf->Line($x, $y1, $x, $y2);
    }

    private function DrawRightLine($page, $y1, $y2, $x)
    {
        $this->pdf->SetPage($page);
        $this->pdf->Line($x, $y1, $x, $y2);
    }

    public function DrawBorders()
    {
        $this->drawed = true;
        $lastCell = end($this->cellsMeta);
        $lastCell->SetIsLast();
        $firstCell = reset($this->cellsMeta);
        $firstCell->SetIsFirst();

        // Draws row top line
        if ($this->isFirst) {
            $this->DrawTopLine(
                $this->startPage,
                $firstCell->GetLeft(),
                $lastCell->GetRight(),
                $firstCell->GetTop()
            );
        }
        // Draws row bottom line
        $this->DrawBottomLine(
            $this->endPage,
            $firstCell->GetLeft(),
            $lastCell->GetRight(),
            $this->bottom
        );
        
        if ($this->startPage == $this->endPage) {
            foreach ($this->cellsMeta as $c) {
                $this->DrawLeftLine(
                    $this->startPage,
                    $this->startY,
                    $this->bottom,
                    $c->GetLeft()
                );
            }
            $this->DrawRightLine(
                $this->startPage,
                $this->startY,
                $this->bottom,
                $lastCell->GetRight()
            );
        } else {
            $bottom = $this->pdf->GetFooterTop();
            foreach ($this->cellsMeta as $c) {
                $this->DrawLeftLine(
                    $this->startPage,
                    $this->startY,
                    $bottom,
                    $c->GetLeft()
                );
            }
            $this->DrawRightLine(
                $this->startPage,
                $this->startY,
                $bottom,
                $lastCell->GetRight()
            );
            $this->DrawBottomLine($this->startPage, $firstCell->GetLeft(), $lastCell->GetRight(), $bottom);

            for ($p = $this->startPage + 1; $p < $this->endPage; $p++) {
                $top = $this->pdf->GetHeaderBottom();
                foreach ($this->cellsMeta as $c) {
                    $this->DrawLeftLine($p, $top, $bottom, $c->GetLeft());
                }
                $this->DrawRightLine($p, $top, $bottom, $lastCell->GetRight());
                $this->DrawTopLine($p, $firstCell->GetLeft(), $lastCell->GetRight(), $top);
                $this->DrawBottomLine($p, $firstCell->GetLeft(), $lastCell->GetRight(), $bottom);
            }

            $this->pdf->SetPage($this->endPage);
            $top = $this->pdf->GetHeaderBottom();
            $this->DrawTopLine($this->endPage, $firstCell->GetLeft(), $lastCell->GetRight(), $top);
            foreach ($this->cellsMeta as $c) {
                $this->DrawLeftLine($this->endPage, $top, $this->bottom, $c->GetLeft());
            }
            $this->DrawRightLine($this->endPage, $top, $this->bottom, $c->GetRight());
        }
    }
}
