<?php

namespace App\Console\Commands;

use App\Support\ApplicationBackup;
use Illuminate\Console\Command;
use Throwable;

class BackupApplicationData extends Command
{
    protected $signature = 'simtaman:backup
                            {--full : Sertakan arsip foto/upload (storage/app/public)}
                            {--label= : Suffix nama file (mis. sebelum-deploy, pemutakhiran)}';

    protected $description = 'Backup database, snapshot JSON data taman, dan (opsional) upload publik';

    public function handle(ApplicationBackup $backup): int
    {
        $includeStorage = (bool) $this->option('full');
        $label = $this->option('label');

        try {
            $files = $backup->run($includeStorage, is_string($label) ? $label : null);
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('Backup selesai:');
        foreach ($files as $file) {
            $this->line('  • '.$file);
        }

        return self::SUCCESS;
    }
}
