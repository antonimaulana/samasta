<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SyncBrandingEnvCommand extends Command
{
    protected $signature = 'simtaman:sync-branding-env';

    protected $description = 'Set APP_NAME/APP_FULL_NAME SIMTAMAN di .env production dan refresh config cache';

    public function handle(): int
    {
        $path = base_path('.env');

        if (! is_file($path)) {
            $this->error('.env tidak ditemukan.');

            return self::FAILURE;
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            $this->error('Gagal membaca .env');

            return self::FAILURE;
        }

        $replacements = [
            '/^APP_NAME=.*/m' => 'APP_NAME="SIMTAMAN"',
            '/^APP_FULL_NAME=.*/m' => 'APP_FULL_NAME="Sistem Informasi Manajemen Pertamanan"',
        ];

        foreach ($replacements as $pattern => $line) {
            if (preg_match($pattern, $contents)) {
                $contents = preg_replace($pattern, $line, $contents) ?? $contents;
            } else {
                $contents .= PHP_EOL.$line;
            }
        }

        file_put_contents($path, $contents);

        $this->call('config:clear');
        $this->call('config:cache');
        $this->call('view:clear');

        $this->info('Branding: '.config('app.name').' — '.config('app.full_name'));

        return self::SUCCESS;
    }
}
