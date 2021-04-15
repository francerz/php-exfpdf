<?php

use Francerz\ExFPDF\ExFPDF;
use PHPUnit\Framework\TestCase;

class CalcMethodsTest extends TestCase
{
    public function testPdfInstance()
    {
        $pdf = new ExFPDF('P', 'mm', [100, 100]);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(false, 10);

        $this->assertTrue(true);

        return $pdf;
    }

    /**
     * @depends testPdfInstance
     */
    public function testCalcXMethod(ExFPDF $pdf)
    {

        // Checks if starting position is 0
        $this->assertEquals(0, $pdf->GetX());

        // Using raw values will give exact same position value.
        $this->assertEquals(10, $pdf->CalcX(10));
        $this->assertEquals(10, $pdf->CalcX('10'));

        // Using the ~ symbol will add left-margin + given value.
        $this->assertEquals(10, $pdf->CalcX('~0'));
        $this->assertEquals(20, $pdf->CalcX('~10'));
        $this->assertEquals(0, $pdf->CalcX('~-10'));

        // Using percentages will calculate based on page size.
        $this->assertEquals(0, $pdf->CalcX('0%'));
        $this->assertEquals(50, $pdf->CalcX('50%'));
        $this->assertEquals(100, $pdf->CalcX('100%'));

        // Using the ~ symbol and percentage will calculate based
        // on the page margins. (left=10{10}, right=10{90})
        $this->assertEquals(10, $pdf->CalcX('~0%'));
        $this->assertEquals(50, $pdf->CalcX('~50%'));
        $this->assertEquals(90, $pdf->CalcX('~100%'));

        $pdf->SetX(30);
        $this->assertEquals(0, $pdf->CalcX('30-'));
        $this->assertEquals(60, $pdf->CalcX('30+'));
        $this->assertEquals(20, $pdf->CalcX('10%-'));
        $this->assertEquals(40, $pdf->CalcX('10%+'));
        
        // Makes no sense and (~) is ignored.
        $this->assertEquals(50, $pdf->CalcX('~20+')); 
        $this->assertEquals(10, $pdf->CalcX('~20-'));

        // Using (~) with percentage has more sense.
        $this->assertEquals(46, $pdf->CalcX('~20%+'));   //100-10-10=80; 80*0.2=16; 30+16= 46
        $this->assertEquals(14, $pdf->CalcX('~20%-'));   //100-10-10=80; 80*0.2=16; 30-16= 14
        $this->assertEquals(110, $pdf->CalcX('~100%+')); //100-10-10=80; 80*1.0=80; 30+80=110
    }
    
    /**
     * @depends testPdfInstance
     */
    public function testCalcYMethod(ExFPDF $pdf)
    {

        // Checks if starting position is 0
        $this->assertEquals(0, $pdf->GetY());

        // Using raw values will give exact same position value.
        $this->assertEquals(10, $pdf->CalcY(10));
        $this->assertEquals(10, $pdf->CalcY('10'));

        // Using the ~ symbol will add left-margin + given value.
        $this->assertEquals(10, $pdf->CalcY('~0'));
        $this->assertEquals(20, $pdf->CalcY('~10'));
        $this->assertEquals(0, $pdf->CalcY('~-10'));

        // Using percentages will calculate based on page size.
        $this->assertEquals(0, $pdf->CalcY('0%'));
        $this->assertEquals(50, $pdf->CalcY('50%'));
        $this->assertEquals(100, $pdf->CalcY('100%'));

        // Using the ~ symbol and percentage will calculate based
        // on the page margins. (top=10{10}, bottom=10{90})
        $this->assertEquals(10, $pdf->CalcY('~0%'));
        $this->assertEquals(50, $pdf->CalcY('~50%'));
        $this->assertEquals(90, $pdf->CalcY('~100%'));

        $pdf->SetY(30);
        $this->assertEquals(0, $pdf->CalcY('30-'));
        $this->assertEquals(60, $pdf->CalcY('30+'));
        $this->assertEquals(20, $pdf->CalcY('10%-'));
        $this->assertEquals(40, $pdf->CalcY('10%+'));
        
        // Makes no sense and (~) is ignored.
        $this->assertEquals(50, $pdf->CalcY('~20+')); 
        $this->assertEquals(10, $pdf->CalcY('~20-'));

        // Using (~) with percentage has more sense.
        $this->assertEquals(46, $pdf->CalcY('~20%+'));   //100-10-10=80; 80*0.2=16; 30+16= 46
        $this->assertEquals(14, $pdf->CalcY('~20%-'));   //100-10-10=80; 80*0.2=16; 30-16= 14
        $this->assertEquals(110, $pdf->CalcY('~100%+')); //100-10-10=80; 80*1.0=80; 30+80=110
    }

    /**
     * @depends testPdfInstance
     */
    public function testCalcWidthMethod(ExFPDF $pdf)
    {
        $this->assertEquals(10, $pdf->CalcWidth(10));
        $this->assertEquals(90, $pdf->CalcWidth(90));

        $this->assertEquals(10, $pdf->CalcWidth('10%'));
        $this->assertEquals(50, $pdf->CalcWidth('50%'));

        $this->assertEquals(16, $pdf->CalcWidth('~20%'));
        $this->assertEquals(80, $pdf->CalcWidth('~100%'));
    }

    /**
     * @depends testPdfInstance
     */
    public function testCalcHeightMethod(ExFPDF $pdf)
    {
        $this->assertEquals(10, $pdf->CalcHeight(10));
        $this->assertEquals(90, $pdf->CalcHeight(90));

        $this->assertEquals(10, $pdf->CalcHeight('10%'));
        $this->assertEquals(50, $pdf->CalcHeight('50%'));

        $this->assertEquals(16, $pdf->CalcHeight('~20%'));
        $this->assertEquals(80, $pdf->CalcHeight('~100%'));

    }
}