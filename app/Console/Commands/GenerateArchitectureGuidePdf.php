<?php

namespace App\Console\Commands;

use App\Support\PdfExport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\View;

class GenerateArchitectureGuidePdf extends Command
{
    protected $signature = 'docs:architecture-pdf
                            {--output=docs/DOKUMENTASI-ARSITEKTUR-SAMASTA.pdf : Path file PDF output relatif ke base path}';

    protected $description = 'Generate PDF dokumentasi arsitektur aplikasi SAMASTA';

    public function handle(): int
    {
        PdfExport::ensureGdLoaded();

        $relativePath = $this->option('output');
        $absolutePath = base_path($relativePath);
        $directory = dirname($absolutePath);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $html = View::make('docs.pdf.dokumentasi-arsitektur', [
            'generatedAt' => now()->timezone('Asia/Jakarta')->format('d F Y, H:i').' WIB',
            'appName' => config('app.name', 'Samasta'),
        ])->render();

        file_put_contents($absolutePath, PdfExport::renderBinary($html));

        $this->info("PDF dokumentasi arsitektur berhasil dibuat: {$relativePath}");

        return self::SUCCESS;
    }
}
