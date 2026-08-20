<?php

namespace App\Console\Commands;

use App\Models\Taman;
use App\Support\TamanWilayahAssigner;
use Illuminate\Console\Command;

class AssignKelurahanFromCoordinates extends Command
{
    protected $signature = 'tamans:assign-kelurahan-from-coordinates
                            {--force : Timpa kelurahan yang sudah ada}
                            {--dry-run : Tampilkan hasil tanpa menyimpan}';

    protected $description = 'Isi kelurahan_id taman berdasarkan koordinat latitude/longitude';

    public function handle(TamanWilayahAssigner $assigner): int
    {
        $force = (bool) $this->option('force');
        $dryRun = (bool) $this->option('dry-run');

        $query = Taman::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('latitude', '!=', '')
            ->where('longitude', '!=', '');

        if (! $force) {
            $query->whereNull('kelurahan_id');
        }

        $tamans = $query->get();
        $updated = 0;
        $skipped = 0;

        foreach ($tamans as $taman) {
            $payload = $assigner->apply([
                'latitude' => $taman->latitude,
                'longitude' => $taman->longitude,
                'kelurahan_id' => $force ? null : $taman->kelurahan_id,
            ], overwriteExisting: $force);

            $newKelurahanId = $payload['kelurahan_id'] ?? null;

            if (! $newKelurahanId || ($taman->kelurahan_id === $newKelurahanId && ! $force)) {
                $skipped++;
                $this->line("  - {$taman->nama_taman}: tidak berubah");

                continue;
            }

            if (! $dryRun) {
                $taman->update(['kelurahan_id' => $newKelurahanId]);
            }

            $updated++;
            $this->info("  ✓ {$taman->nama_taman}: kelurahan_id → {$newKelurahanId}");
        }

        $this->newLine();
        $this->info(($dryRun ? '[Dry run] ' : '')."Selesai. Diperbarui: {$updated}, dilewati: {$skipped}.");

        return self::SUCCESS;
    }
}
