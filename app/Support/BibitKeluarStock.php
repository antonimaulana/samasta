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

    public function kurangiStokMasuk(Bibit $bibit, int $jumlah, ?string $sumber = null): void
    {
        $sisa = $jumlah;

        $masuks = $this->masukQueryForBibit($bibit->id)
            ->where('bibit_masuks.sisa_stok', '>', 0)
            ->when(filled($sumber), fn ($q) => $q->where('bibit_masuks.sumber', $sumber))
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
            $message = filled($sumber)
                ? "Stok sumber {$sumber} tidak mencukupi untuk pengeluaran ini."
                : 'Stok batch tidak mencukupi untuk pengeluaran ini.';

            throw ValidationException::withMessages([
                'jumlah' => $message,
            ]);
        }
    }

    public function kembalikanStokMasuk(Bibit $bibit, int $jumlah, ?string $sumber = null): void
    {
        $sisa = $jumlah;

        $masuks = $this->masukQueryForBibit($bibit->id)
            ->whereColumn('bibit_masuks.sisa_stok', '<', 'bibit_masuks.jumlah')
            ->when(filled($sumber), fn ($q) => $q->where('bibit_masuks.sumber', $sumber))
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
                ->when(filled($sumber), fn ($q) => $q->where('bibit_masuks.sumber', $sumber))
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
     * @param  array<int, array{bibit_id: int, jumlah: int, sumber?: string|null}>  $items
     * @param  array<string, int>  $stokBonusByLineKey  kunci "{bibit_id}|{sumber}"
     */
    public function assertStokCukup(array $items, ?array $stokBonusByLineKey = null): void
    {
        $bonus = $stokBonusByLineKey ?? [];

        foreach ($items as $index => $item) {
            $bibitId = (int) $item['bibit_id'];
            $jumlah = (int) $item['jumlah'];
            $sumber = $item['sumber'] ?? null;
            $bibit = Bibit::query()->with('masuks')->find($bibitId);

            if (! $bibit) {
                continue;
            }

            $lineKey = $this->lineKey($bibitId, $sumber);

            if (filled($sumber)) {
                $tersedia = ($bibit->stokPerSumber()[$sumber] ?? 0) + (int) ($bonus[$lineKey] ?? 0);
                $label = "{$bibit->nama_tanaman} (sumber {$sumber})";
            } else {
                $tersedia = $bibit->stok_tersedia + (int) ($bonus[$lineKey] ?? $bonus[(string) $bibitId] ?? 0);
                $label = $bibit->nama_tanaman;
            }

            if ($jumlah > $tersedia) {
                throw ValidationException::withMessages([
                    "items.{$index}.jumlah" => "Jumlah keluar ({$jumlah}) melebihi stok tersedia ({$tersedia}) untuk {$label}.",
                ]);
            }
        }
    }

    public function lineKey(int $bibitId, ?string $sumber): string
    {
        return $bibitId.'|'.($sumber ?? '');
    }
}
