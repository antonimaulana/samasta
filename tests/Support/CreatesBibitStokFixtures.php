<?php

namespace Tests\Support;

use App\Models\Bibit;
use App\Models\BibitMasuk;
use App\Models\BibitMasukBatch;
use App\Support\OperasionalPelaksanaanTime;

trait CreatesBibitStokFixtures
{
    protected function createBibitMasukLine(Bibit $bibit, int $jumlah, ?int $sisaStok = null, array $overrides = []): BibitMasuk
    {
        $tanggal = OperasionalPelaksanaanTime::normalizeInput($overrides['tanggal_masuk'] ?? now()->format('Y-m-d H:i:s'));

        $batch = BibitMasukBatch::create([
            'tanggal_masuk' => $tanggal,
        ]);

        return BibitMasuk::create([
            'bibit_masuk_batch_id' => $batch->id,
            'bibit_id' => $bibit->id,
            'jumlah' => $jumlah,
            'sisa_stok' => $sisaStok ?? $jumlah,
            'sumber' => $overrides['sumber'] ?? 'Produksi',
            'status_siap_tanam' => $overrides['status_siap_tanam'] ?? true,
        ]);
    }
}
