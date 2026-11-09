<?php

namespace Tests\Unit;

use App\Support\PdfExport;
use Tests\TestCase;

class PdfExportDispositionTest extends TestCase
{
    public function test_pdf_responses_use_inline_content_disposition(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('PHP GD extension is required for PDF export.');
        }

        $html = '<html><body><p>uji</p></body></html>';

        foreach ([PdfExport::download($html, 'test.pdf'), PdfExport::inline($html, 'test.pdf')] as $response) {
            $this->assertStringStartsWith('inline;', (string) $response->headers->get('content-disposition'));
        }
    }
}
