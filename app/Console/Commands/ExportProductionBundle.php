<?php

namespace App\Console\Commands;

use App\Support\ProductionDataBundle;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ExportProductionBundle extends Command
{
    protected $signature = 'simtaman:export-production-bundle
                            {--output= : Path file ZIP (default: storage/app/publish/simtaman-publish-YYYY-mm-dd_His.zip)}';

    protected $description = 'Export data lokal (SQLite/MySQL) + foto + build frontend untuk naik ke server live';

    public function handle(ProductionDataBundle $bundle): int
    {
        $output = $this->option('output')
            ?: storage_path('app/publish/simtaman-publish-'.Carbon::now('Asia/Jakarta')->format('Y-m-d_His').'.zip');

        $this->warn('Pastikan kode terbaru sudah di-commit; bundle ini hanya data & file upload/build.');

        try {
            $path = $bundle->export($output);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('Bundle siap di-upload ke VPS:');
        $this->line('  '.$path);

        return self::SUCCESS;
    }
}
