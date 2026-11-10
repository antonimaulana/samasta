<?php

namespace App\Console\Commands;

use App\Models\Taman;
use App\Support\TamanLuasan;
use Illuminate\Console\Command;

class QuantizeTamansLuasanCommand extends Command
{
    protected $signature = 'tamans:quantize-luasan
                            {--dry-run : Tampilkan perubahan tanpa menyimpan}';

    protected $description = 'Selaraskan kolom luasan dengan presisi simpan per kategori (perbaiki selisih agregat vs tampilan)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $changed = 0;

        Taman::query()
            ->orderBy('id')
            ->each(function (Taman $taman) use ($dryRun, &$changed): void {
                $before = (float) $taman->luasan;
                $after = TamanLuasan::quantizeStorage($before, $taman->kategori);

                if (abs($before - $after) < 0.0000001) {
                    return;
                }

                $changed++;

                $this->line(sprintf(
                    '#%d %s [%s]: %s → %s',
                    $taman->id,
                    $taman->nama_taman,
                    $taman->kategori,
                    $before,
                    $after
                ));

                if (! $dryRun) {
                    $taman->luasan = $after;
                    $taman->saveQuietly();
                }
            });

        if ($changed === 0) {
            $this->info('Semua luasan sudah selaras.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->info($dryRun
            ? "{$changed} baris akan diperbarui (dry-run)."
            : "{$changed} baris diperbarui.");

        return self::SUCCESS;
    }
}
