<?php

namespace App\Console\Commands;

use App\Models\Taman;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class PemutakhiranPreflightCommand extends Command
{
    protected $signature = 'simtaman:preflight-pemutakhiran';

    protected $description = 'Cek kesiapan server & modul pemutakhiran data taman sebelum kegiatan lapangan';

    public function handle(): int
    {
        $ok = true;
        $warn = fn (string $message) => $this->warn($message);
        $pass = fn (string $message) => $this->info('✓ '.$message);
        $fail = function (string $message) use (&$ok) {
            $this->error('✗ '.$message);
            $ok = false;
        };

        $this->line('=== Preflight pemutakhiran taman — '.now('Asia/Jakarta')->toDateTimeString().' ===');

        if (! app()->environment('production')) {
            $warn('APP_ENV bukan production — pastikan ini memang lingkungan uji/staging.');
        } else {
            if (config('app.debug')) {
                $fail('APP_DEBUG=true di production — set false sebelum go-live.');
            } else {
                $pass('APP_DEBUG=false');
            }
        }

        $url = (string) config('app.url');
        if ($url === '' || str_starts_with($url, 'http://localhost')) {
            $warn('APP_URL masih localhost — pastikan domain HTTPS production sudah benar.');
        } else {
            $pass('APP_URL='.$url);
        }

        try {
            DB::connection()->getPdo();
            $pass('Koneksi database OK ('.config('database.default').')');
        } catch (\Throwable $e) {
            $fail('Database tidak terhubung: '.$e->getMessage());
        }

        if (Schema::hasTable('migrations')) {
            $migrator = app('migrator');
            $files = $migrator->getMigrationFiles(database_path('migrations'));
            $ran = $migrator->getRepository()->getRan();
            $pending = array_diff(array_keys($files), $ran);
            if ($pending !== []) {
                $fail('Ada '.count($pending).' migrasi pending — jalankan: php artisan migrate --force');
            } else {
                $pass('Semua migrasi sudah dijalankan');
            }
        }

        $publicStorage = public_path('storage');
        if (! is_link($publicStorage) && ! is_dir($publicStorage)) {
            $fail('storage:link belum ada — foto taman tidak akan tampil. Jalankan: php artisan storage:link');
        } else {
            $pass('Symlink storage publik OK');
        }

        $uploadRoot = storage_path('app/public');
        if (! is_writable($uploadRoot)) {
            $fail('storage/app/public tidak writable — upload foto gagal.');
        } else {
            $pass('Direktori upload writable');
        }

        if (! Schema::hasTable('tamans')) {
            $fail('Tabel tamans tidak ada.');
        } else {
            $count = Taman::query()->count();
            $pass("Tabel tamans OK ({$count} record)");
        }

        $writers = User::query()
            ->whereIn('role', [User::ROLE_ADMIN, User::ROLE_OPERATOR])
            ->count();
        if ($writers === 0) {
            $fail('Tidak ada akun Administrator/Admin — buat akun untuk tim pemutakhiran.');
        } else {
            $pass("Akun bisa edit taman: {$writers}");
        }

        if (! config('simtaman.lapangan.pin')) {
            $warn('SIMTAMAN_LAPANGAN_PIN kosong — Input Lapangan hanya via login akun.');
        } else {
            $pass('PIN Input Lapangan dikonfigurasi');
        }

        if (! filter_var(config('wilayah.geocoder.enabled', true), FILTER_VALIDATE_BOOLEAN)) {
            $warn('Geocoder wilayah nonaktif — tag GPS tidak mengisi kelurahan otomatis.');
        } else {
            $pass('Geocoder wilayah aktif');
        }

        $backupPath = (string) config('backup.path');
        if (! File::isDirectory($backupPath)) {
            $warn("Folder backup belum ada ({$backupPath}) — akan dibuat saat simtaman:backup pertama.");
        } elseif (! is_writable($backupPath)) {
            $fail("Folder backup tidak writable: {$backupPath}");
        } else {
            $pass('Folder backup writable');
        }

        $this->newLine();
        if ($ok) {
            $this->info('Siap untuk kegiatan pemutakhiran (dari sisi aplikasi).');
            $this->line('Disarankan: php artisan simtaman:backup --full --label=sebelum-pemutakhiran');

            return self::SUCCESS;
        }

        $this->error('Perbaiki item gagal di atas sebelum go-live.');

        return self::FAILURE;
    }
}
