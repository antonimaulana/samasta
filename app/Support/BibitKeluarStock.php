<?php

namespace App\Support;

use App\Models\Bibit;
use App\Models\BibitMasuk;
use Illuminate\Validation\ValidationException;

class BibitKeluarStock
{
    /**
     * @return \Illuminate\Database\Eloquent\Builder<BibitMasuk>
     */
    private function masukQueryForBibit(int $bibitId)
    {
        return BibitMasuk::query()
            ->where('bibit_masuks.bibit_id', $bibitId)
            ->join('bibit_masuk_batches', 'bibit_masuk_batches.id', '=', 'bibit_masuks.bibit_masuk_batch_id')
            ->select('bibit_masuks.*');
    }

    public function kurangiStokMasuk(Bibit $bibit, int $jumlah): void
    {
        $sisa = $jumlah;

        $masuks = $this->masukQueryForBibit($bibit->id)
            ->where('bibit_masuks.sisa_stok', '>', 0)
            ->orderBy('bibit_masuk_batches.tanggal_masuk')
            ->orderBy('bibit_masuks.id')
            ->lockForUpdate()
            ->get();

        foreach ($masuks as $masuk) {
            if ($sisa <= 0) {
                break;
            }

            $potong = min($sisa, $masuk->sisa_stok);
            $masuk->decrement('sisa_stok', $potong);
            $sisa -= $potong;
        }

        if ($sisa > 0) {
            throw ValidationException::withMessages([
                'jumlah' => 'Stok batch tidak mencukupi untuk pengeluaran ini.',
            ]);
        }
    }

    public function kembalikanStokMasuk(Bibit $bibit, int $jumlah): void
    {
        $sisa = $jumlah;

        $masuks = $this->masukQueryForBibit($bibit->id)
            ->whereColumn('bibit_masuks.sisa_stok', '<', 'bibit_masuks.jumlah')
            ->orderByDesc('bibit_masuk_batches.tanggal_masuk')
            ->orderByDesc('bibit_masuks.id')
            ->lockForUpdate()
            ->get();

        foreach ($masuks as $masuk) {
            if ($sisa <= 0) {
                break;
            }

            $kapasitas = $masuk->jumlah - $masuk->sisa_stok;
            $tambah = min($sisa, $kapasitas);
            $masuk->increment('sisa_stok', $tambah);
            $sisa -= $tambah;
        }

        if ($sisa > 0) {
            $masuk = BibitMasuk::query()
                ->where('bibit_masuks.bibit_id', $bibit->id)
                ->join('bibit_masuk_batches', 'bibit_masuk_batches.id', '=', 'bibit_masuks.bibit_masuk_batch_id')
                ->orderByDesc('bibit_masuk_batches.tanggal_masuk')
                ->orderByDesc('bibit_masuks.id')
                ->select('bibit_masuks.*')
                ->lockForUpdate()
                ->first();

            if ($masuk) {
                $masuk->increment('sisa_stok', $sisa);
                $masuk->increment('jumlah', $sisa);
            }
        }
    }

    /**
     * @param  array<int, array{bibit_id: int, jumlah: int}>  $items
     */
    public function assertStokCukup(array $items, ?array $stokBonusByBibitId = null): void
    {
        $bonus = $stokBonusByBibitId ?? [];

        foreach ($items as $index => $item) {
            $bibitId = (int) $item['bibit_id'];
            $jumlah = (int) $item['jumlah'];
            $bibit = Bibit::query()->find($bibitId);

            if (! $bibit) {
                continue;
            }

            $tersedia = $bibit->stok_tersedia + (int) ($bonus[$bibitId] ?? 0);

            if ($jumlah > $tersedia) {
                throw ValidationException::withMessages([
                    "items.{$index}.jumlah" => "Jumlah keluar ({$jumlah}) melebihi stok tersedia ({$tersedia}) untuk {$bibit->nama_tanaman}.",
                ]);
            }
        }
    }
}
