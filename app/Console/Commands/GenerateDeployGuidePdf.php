<?php

namespace App\Console\Commands;

use App\Support\PdfExport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\View;

class GenerateDeployGuidePdf extends Command
{
    protected $signature = 'docs:deploy-guide-pdf
                            {--output=docs/PANDUAN-DEPLOY-BIZNET-CLOUDFLARE.pdf : Path file PDF output relatif ke base path}';

    protected $description = 'Generate PDF panduan deploy SIMTAMAN di VPS Biznet + Cloudflare';

    public function handle(): int
    {
        PdfExport::ensureGdLoaded();

        $relativePath = $this->option('output');
        $absolutePath = base_path($relativePath);
        $directory = dirname($absolutePath);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $html = View::make('docs.pdf.panduan-deploy-biznet-cloudflare', [
            'generatedAt' => now()->timezone('Asia/Jakarta')->format('d F Y, H:i').' WIB',
            'appName' => config('app.name', 'SIMTAMAN'),
            'appFullName' => config('app.full_name', 'Sistem Informasi Manajemen Pertamanan'),
        ])->render();

        file_put_contents($absolutePath, PdfExport::renderBinary($html));

        $this->info("PDF panduan deploy berhasil dibuat: {$relativePath}");

        return self::SUCCESS;
    }
}
