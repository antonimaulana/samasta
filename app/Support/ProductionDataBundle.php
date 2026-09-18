<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use ZipArchive;

class ProductionDataBundle
{
    /** @var list<string> */
    private const SKIP_TABLES = [
        'migrations',
        'cache',
        'cache_locks',
        'sessions',
        'jobs',
        'job_batches',
        'failed_jobs',
        'password_reset_tokens',
    ];

    public function export(string $outputZipPath): string
    {
        $directory = storage_path('app/publish/'.Carbon::now('Asia/Jakarta')->format('Y-m-d_His'));
        File::ensureDirectoryExists($directory);

        $sqlPath = $directory.DIRECTORY_SEPARATOR.'database.mysql.sql';
        $this->writeMysqlInsertDump($sqlPath);

        $publicZip = $directory.DIRECTORY_SEPARATOR.'storage-public.zip';
        $this->zipDirectory(storage_path('app/public'), $publicZip);

        $buildZip = $directory.DIRECTORY_SEPARATOR.'public-build.zip';
        if (File::isDirectory(public_path('build'))) {
            $this->zipDirectory(public_path('build'), $buildZip);
        }

        File::put($directory.DIRECTORY_SEPARATOR.'manifest.json', json_encode([
            'exported_at' => Carbon::now('Asia/Jakarta')->toIso8601String(),
            'app' => config('app.name'),
            'source_connection' => config('database.default'),
            'tables' => $this->exportableTables(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        File::ensureDirectoryExists(dirname($outputZipPath));
        $this->zipDirectory($directory, $outputZipPath);

        File::deleteDirectory($directory);

        return $outputZipPath;
    }

    public function import(string $zipPath, bool $replaceData): void
    {
        if (! is_file($zipPath)) {
            throw new RuntimeException("Bundle tidak ditemukan: {$zipPath}");
        }

        $work = storage_path('app/publish/import-'.uniqid());
        File::ensureDirectoryExists($work);

        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            throw new RuntimeException('Gagal membuka bundle ZIP.');
        }
        $zip->extractTo($work);
        $zip->close();

        $root = $this->resolveBundleRoot($work);
        $sqlFile = $root.DIRECTORY_SEPARATOR.'database.mysql.sql';

        if (! is_file($sqlFile)) {
            throw new RuntimeException('database.mysql.sql tidak ada di bundle.');
        }

        if ($replaceData) {
            $this->importSqlFile($sqlFile);
        }

        $storageZip = $root.DIRECTORY_SEPARATOR.'storage-public.zip';
        if (is_file($storageZip)) {
            $this->extractZipTo($storageZip, storage_path('app/public'));
        }

        $buildZip = $root.DIRECTORY_SEPARATOR.'public-build.zip';
        if (is_file($buildZip)) {
            $this->extractZipTo($buildZip, public_path('build'));
        }

        File::deleteDirectory($work);
    }

    private function resolveBundleRoot(string $work): string
    {
        $candidates = [
            $work,
            ...array_filter(glob($work.DIRECTORY_SEPARATOR.'*'), 'is_dir'),
        ];

        foreach ($candidates as $dir) {
            if (is_file($dir.DIRECTORY_SEPARATOR.'database.mysql.sql')) {
                return $dir;
            }
        }

        return $work;
    }

    /**
     * @return list<string>
     */
    private function exportableTables(): array
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $names = collect(DB::select("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name"))
                ->map(fn ($row) => $row->name)
                ->all();
        } else {
            $database = DB::connection()->getDatabaseName();
            $names = collect(DB::select(
                'SELECT TABLE_NAME as name FROM information_schema.tables WHERE table_schema = ? ORDER BY TABLE_NAME',
                [$database]
            ))->map(fn ($row) => $row->name)->all();
        }

        return collect($names)
            ->reject(fn (string $name) => str_starts_with($name, 'sqlite_'))
            ->reject(fn (string $name) => in_array($name, self::SKIP_TABLES, true))
            ->values()
            ->all();
    }

    private function writeMysqlInsertDump(string $path): void
    {
        $handle = fopen($path, 'wb');
        if ($handle === false) {
            throw new RuntimeException('Gagal membuat file SQL export.');
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\nSET NAMES utf8mb4;\n\n");

        foreach ($this->exportableTables() as $table) {
            fwrite($handle, "DELETE FROM `{$table}`;\n");

            DB::table($table)->orderBy(DB::raw('1'))->chunk(200, function ($rows) use ($handle, $table) {
                foreach ($rows as $row) {
                    $data = (array) $row;
                    $columns = array_keys($data);
                    $values = array_map(fn ($value) => $this->quoteValue($value), array_values($data));
                    $columnList = implode('`, `', $columns);
                    $valueList = implode(', ', $values);
                    fwrite($handle, "INSERT INTO `{$table}` (`{$columnList}`) VALUES ({$valueList});\n");
                }
            });

            fwrite($handle, "\n");
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);
    }

    private function quoteValue(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return "'".str_replace(["\\", "'"], ["\\\\", "\\'"], (string) $value)."'";
    }

    private function importSqlFile(string $path): void
    {
        $sql = file_get_contents($path);
        if ($sql === false) {
            throw new RuntimeException('Gagal membaca SQL bundle.');
        }

        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');

        foreach (preg_split('/;\s*\n/', $sql) as $statement) {
            $statement = trim($statement);
            if ($statement === '' || str_starts_with($statement, '--')) {
                continue;
            }
            DB::unprepared($statement);
        }

        DB::unprepared('SET FOREIGN_KEY_CHECKS=1');
    }

    private function zipDirectory(string $source, string $zipPath): void
    {
        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException("Gagal membuat ZIP: {$zipPath}");
        }

        if (! File::isDirectory($source)) {
            $zip->addFromString('.gitkeep', '');
            $zip->close();

            return;
        }

        foreach (File::allFiles($source) as $file) {
            $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($source) + 1));
            $zip->addFile($file->getPathname(), $relative);
        }

        $zip->close();
    }

    private function extractZipTo(string $zipPath, string $targetDirectory): void
    {
        File::ensureDirectoryExists($targetDirectory);
        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            throw new RuntimeException("Gagal membuka {$zipPath}");
        }
        $zip->extractTo($targetDirectory);
        $zip->close();
    }
}
