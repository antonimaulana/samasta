<?php

namespace App\Console\Commands;

use App\Support\ProductionDataBundle;
use Illuminate\Console\Command;

class ImportProductionBundle extends Command
{
    protected $signature = 'simtaman:import-production-bundle
                            {path : Path file ZIP bundle dari laptop}
                            {--force : Ganti data production dengan isi bundle (wajib untuk import)}';

    protected $description = 'Import bundle publish ke server live (MySQL + storage + build)';

    public function handle(ProductionDataBundle $bundle): int
    {
        if (! $this->option('force')) {
            $this->error('Tambahkan --force untuk mengganti data di server ini.');

            return self::FAILURE;
        }

        if (! app()->environment('production')) {
            if (! $this->confirm('APP_ENV bukan production. Lanjutkan import?', false)) {
                return self::FAILURE;
            }
        }

        $path = (string) $this->argument('path');

        try {
            $bundle->import($path, true);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('Import bundle selesai. Jalankan: php artisan config:cache && php artisan storage:link');

        return self::SUCCESS;
    }
}
