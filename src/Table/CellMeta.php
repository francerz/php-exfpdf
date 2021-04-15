<?php

namespace Francerz\ExFPDF\Table;

class CellMeta
{
    private $x;
    private $y;
    private $w;

    private $isFirst = false;
    private $isLast = false;

    public function __construct($x, $y, $w)
    {
        $this->x = $x;
        $this->y = $y;
        $this->w = $w;
    }

    public function GetX()
    {
        return $this->x;
    }

    public function GetY()
    {
        return $this->y;
    }

    public function GetWidth()
    {
        return $this->w;
    }

    public function GetLeft()
    {
        return $this->x;
    }

    public function GetTop()
    {
        return $this->y;
    }
    
    public function GetRight()
    {
        return $this->x + $this->w;
    }

    public function SetIsFirst($isFirst = true)
    {
        $this->isFirst = $isFirst;
    }

    public function SetIsLast($isLast = true)
    {
        $this->isLast = $isLast;
    }

    public function GetIsFirst()
    {
        return $this->isFirst;
    }

    public function GetIsLast()
    {
        return $this->isLast;
    }
}