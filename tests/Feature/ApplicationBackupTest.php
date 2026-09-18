<?php

namespace Tests\Feature;

use App\Models\Taman;
use App\Support\ApplicationBackup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ApplicationBackupTest extends TestCase
{
    use RefreshDatabase;

    public function test_backup_creates_database_and_taman_json_snapshots(): void
    {
        while (DB::transactionLevel() > 0) {
            DB::commit();
        }

        $backupDir = storage_path('framework/testing/backups-'.uniqid());
        config(['backup.path' => $backupDir]);

        Taman::create([
            'nama_taman' => 'Taman Backup Test',
            'kategori' => 'Taman Kota',
            'luasan' => 1000,
            'alamat' => 'Jl. Test',
            'deskripsi' => 'Deskripsi.',
        ]);

        $files = app(ApplicationBackup::class)->run(false, 'test');

        $this->assertNotEmpty($files);

        $names = collect($files)->map(fn ($path) => basename($path))->all();
        $this->assertTrue(
            collect($names)->contains(fn ($name) => str_contains($name, '_database.sqlite')),
        );
        $this->assertTrue(
            collect($names)->contains(fn ($name) => str_contains($name, '_tamans.json')),
        );

        File::deleteDirectory($backupDir);
    }
}
