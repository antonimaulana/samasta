<?php

namespace App\Support;

use App\Models\Bibit;
use App\Models\BibitMasuk;
use Illuminate\Validation\ValidationException;

class BibitKeluarStock
{
    public function kurangiStokMasuk(Bibit $bibit, int $jumlah): void
    {
        $sisa = $jumlah;

        $masuks = BibitMasuk::where('bibit_id', $bibit->id)
            ->where('sisa_stok', '>', 0)
            ->orderBy('tanggal_masuk')
            ->orderBy('id')
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

        $masuks = BibitMasuk::where('bibit_id', $bibit->id)
            ->whereColumn('sisa_stok', '<', 'jumlah')
            ->orderByDesc('tanggal_masuk')
            ->orderByDesc('id')
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
            $masuk = BibitMasuk::where('bibit_id', $bibit->id)
                ->orderByDesc('tanggal_masuk')
                ->orderByDesc('id')
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
