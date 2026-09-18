<?php

namespace App\Support;

use App\Models\Taman;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use RuntimeException;
use ZipArchive;

class ApplicationBackup
{
    /**
     * @return list<string> Path file backup yang dibuat
     */
    public function run(bool $includeStorage = true, ?string $label = null): array
    {
        $directory = $this->ensureBackupDirectory();
        $stamp = Carbon::now('Asia/Jakarta')->format('Y-m-d_His');
        $suffix = $label !== null && $label !== ''
            ? preg_replace('/[^a-zA-Z0-9_-]+/', '-', $label) ?? 'custom'
            : 'auto';
        $prefix = "simtaman_{$stamp}_{$suffix}";

        $created = [];
        $created[] = $this->backupDatabase($directory, $prefix);
        $created[] = $this->exportTamansJson($directory, $prefix);

        if ($includeStorage) {
            $created[] = $this->archivePublicUploads($directory, $prefix);
        }

        $this->purgeOldBackups($directory);

        return array_values(array_filter($created));
    }

    public function ensureBackupDirectory(): string
    {
        $directory = rtrim((string) config('backup.path'), DIRECTORY_SEPARATOR);

        if ($directory === '') {
            throw new RuntimeException('BACKUP_PATH / config backup.path tidak valid.');
        }

        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0750, true);
        }

        if (! is_writable($directory)) {
            throw new RuntimeException("Direktori backup tidak dapat ditulis: {$directory}");
        }

        return $directory;
    }

    private function backupDatabase(string $directory, string $prefix): string
    {
        $connection = (string) config('database.default');
        $target = $directory.DIRECTORY_SEPARATOR."{$prefix}_database";

        if ($connection === 'sqlite') {
            $source = (string) config('database.connections.sqlite.database');

            $path = "{$target}.sqlite";

            if ($source === ':memory:' || str_contains($source, ':memory:')) {
                $sqlitePath = str_replace('\\', '/', $path);
                $quoted = str_replace("'", "''", $sqlitePath);
                DB::connection()->getPdo()->exec("VACUUM INTO '{$quoted}'");

                return $path;
            }

            if ($source === '' || ! is_file($source)) {
                throw new RuntimeException('File SQLite tidak ditemukan.');
            }

            if (! copy($source, $path)) {
                throw new RuntimeException('Gagal menyalin database SQLite.');
            }

            return $path;
        }

        if ($connection !== 'mysql' && $connection !== 'mariadb') {
            throw new RuntimeException("Backup otomatis belum mendukung driver: {$connection}");
        }

        $config = config("database.connections.{$connection}");
        $path = "{$target}.sql.gz";
        $binary = (string) config('backup.mysqldump_binary', 'mysqldump');

        $password = (string) ($config['password'] ?? '');

        $dump = Process::timeout(600)
            ->env($password !== '' ? ['MYSQL_PWD' => $password] : [])
            ->run([
                $binary,
                '--host='.($config['host'] ?? '127.0.0.1'),
                '--port='.($config['port'] ?? '3306'),
                '--user='.($config['username'] ?? 'root'),
                '--single-transaction',
                '--quick',
                '--lock-tables=false',
                $config['database'] ?? '',
            ]);

        if (! $dump->successful()) {
            throw new RuntimeException('mysqldump gagal: '.$dump->errorOutput());
        }

        $gz = gzencode($dump->output(), 6);
        if ($gz === false) {
            throw new RuntimeException('Gagal mengompresi dump database.');
        }

        file_put_contents($path, $gz);

        return $path;
    }

    private function exportTamansJson(string $directory, string $prefix): string
    {
        $path = $directory.DIRECTORY_SEPARATOR."{$prefix}_tamans.json";

        $payload = Taman::query()
            ->with(['images:id,taman_id,path_foto', 'kelurahan:id,nama,kecamatan_id'])
            ->orderBy('id')
            ->get()
            ->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        file_put_contents($path, $payload);

        return $path;
    }

    private function archivePublicUploads(string $directory, string $prefix): ?string
    {
        $root = storage_path('app/public');

        if (! File::isDirectory($root)) {
            return null;
        }

        $path = $directory.DIRECTORY_SEPARATOR."{$prefix}_storage-public.zip";
        $zip = new ZipArchive;

        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Gagal membuat arsip ZIP upload.');
        }

        $files = File::allFiles($root);
        if ($files === []) {
            $zip->addFromString('.gitkeep', '');
        } else {
            foreach ($files as $file) {
                $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
                $zip->addFile($file->getPathname(), $relative);
            }
        }

        $zip->close();

        return $path;
    }

    private function purgeOldBackups(string $directory): void
    {
        $keepDays = max(1, (int) config('backup.keep_days', 30));
        $cutoff = Carbon::now('Asia/Jakarta')->subDays($keepDays)->getTimestamp();

        foreach (File::files($directory) as $file) {
            if ($file->getMTime() < $cutoff) {
                File::delete($file->getPathname());
            }
        }
    }
}
