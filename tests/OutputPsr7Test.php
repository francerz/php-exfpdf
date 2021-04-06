<?php

use Francerz\EFPDF\EFPDF;
use Francerz\Http\HttpFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class OutputPsr7Test extends TestCase
{
    public function testOutputPsr7()
    {
        $efpdf = new EFPDF();
        // ... Lines PDF creation
        $response = $efpdf->OutputPsr7WithManager(HttpFactory::getManager(), 'my-file.pdf');

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertStringEndsWith('filename="my-file.pdf"', $response->getHeaderLine('content-disposition'));
        $this->assertStringStartsWith('%PDF', (string)$response->getBody());
        $this->assertStringEndsWith("%%EOF\n", (string)$response->getBody());
    }
}