<?php

use Francerz\ExFPDF\ExFPDF;
use Francerz\Http\HttpFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class OutputPsr7Test extends TestCase
{
    public function testOutputPsr7()
    {
        $exfpdf = new ExFPDF();
        // ... Lines PDF creation
        $response = $exfpdf->OutputPsr7WithManager(HttpFactory::getManager(), 'my-file.pdf');

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertStringEndsWith('filename="my-file.pdf"', $response->getHeaderLine('content-disposition'));
        $this->assertStringStartsWith('%PDF', (string)$response->getBody());
        $this->assertStringEndsWith("%%EOF\n", (string)$response->getBody());
    }
}