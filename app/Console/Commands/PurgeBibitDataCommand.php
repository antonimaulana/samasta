<?php

namespace App\Console\Commands;

use App\Models\Bibit;
use App\Models\BibitKeluar;
use App\Models\BibitKeluarBatch;
use App\Models\BibitMasuk;
use App\Models\BibitMasukBatch;
use App\Models\BibitStokBuktiFoto;
use App\Support\OperationalAlertService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class PurgeBibitDataCommand extends Command
{
    protected $signature = 'bibit:purge
                            {--force : Jalankan tanpa konfirmasi}';

    protected $description = 'Hapus semua data master bibit, stok masuk/keluar, batch, dan foto bukti (siap import ulang)';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('Semua data bibit akan dihapus permanen. Lanjutkan?', false)) {
            $this->warn('Dibatalkan.');

            return self::SUCCESS;
        }

        $counts = [
            'bibits' => Bibit::query()->count(),
            'masuk' => BibitMasuk::query()->count(),
            'keluar' => BibitKeluar::query()->count(),
            'masuk_batch' => BibitMasukBatch::query()->count(),
            'keluar_batch' => BibitKeluarBatch::query()->count(),
            'foto' => BibitStokBuktiFoto::query()->count(),
        ];

        $this->info('Menghapus data bibit…');
        foreach ($counts as $label => $count) {
            $this->line("  • {$label}: {$count} baris");
        }

        $paths = BibitStokBuktiFoto::query()->pluck('path')->filter()->all();
        if (Schema::hasColumn('bibit_keluar_batches', 'foto')) {
            foreach (BibitKeluarBatch::query()->whereNotNull('foto')->pluck('foto') as $legacy) {
                $paths[] = $legacy;
            }
        }
        foreach (BibitKeluarBatch::query()->whereNotNull('dokumen_pendukung')->pluck('dokumen_pendukung') as $doc) {
            $paths[] = $doc;
        }

        DB::transaction(function (): void {
            BibitKeluar::query()->delete();
            BibitKeluarBatch::query()->delete();
            BibitMasuk::query()->delete();
            BibitMasukBatch::query()->delete();
            BibitStokBuktiFoto::query()->delete();
            Bibit::query()->delete();
        });

        $disk = Storage::disk('public');
        foreach (array_unique(array_filter($paths)) as $path) {
            $normalized = str_replace('\\', '/', (string) $path);
            if ($disk->exists($normalized)) {
                $disk->delete($normalized);
            }
        }

        OperationalAlertService::clearCache();

        $this->info('Data bibit dikosongkan. Silakan import ulang master CSV.');

        return self::SUCCESS;
    }
}
