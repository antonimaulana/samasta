<?php

namespace App\Support;

use App\Models\PemeliharaanTaman;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class LapanganPemeliharaanLokasiDuplicate
{
    /**
     * @return Collection<int, PemeliharaanTaman>
     */
    public function findSameDay(Carbon|string|null $tanggal, bool $lokasiLuar, ?int $tamanId, ?string $manualLokasi): Collection
    {
        $date = OperasionalPelaksanaanTime::datePart($tanggal);

        if (blank($date)) {
            return new Collection;
        }

        $query = PemeliharaanTaman::query()
            ->with('taman')
            ->whereDate('tanggal', $date);

        if ($lokasiLuar) {
            $normalized = $this->normalizeManualLokasi($manualLokasi);
            if ($normalized === '') {
                return new Collection;
            }

            $query->whereNull('taman_id')
                ->whereRaw('LOWER(TRIM(lokasi_pelaksanaan)) = ?', [$normalized]);
        } else {
            if (! $tamanId) {
                return new Collection;
            }

            $query->where('taman_id', $tamanId);
        }

        return $query->orderByDesc('created_at')->get();
    }

    public function hasSameDay(Carbon|string|null $tanggal, bool $lokasiLuar, ?int $tamanId, ?string $manualLokasi): bool
    {
        return $this->findSameDay($tanggal, $lokasiLuar, $tamanId, $manualLokasi)->isNotEmpty();
    }

    public function confirmMessage(Collection $existing): string
    {
        $count = $existing->count();
        $sample = $existing->first();
        $lokasi = $sample?->lokasi_pelaksanaan ?? 'lokasi ini';
        $waktu = $sample?->tanggal ? OperasionalPelaksanaanTime::display($sample->tanggal) : '';

        $base = 'Lokasi "'.$lokasi.'" sudah dicatat '.$count.' kali pada hari yang sama';

        if ($waktu !== '') {
            $base .= ' (contoh: '.$waktu.')';
        }

        return $base.'. Lanjutkan simpan data baru?';
    }

    private function normalizeManualLokasi(?string $value): string
    {
        return mb_strtolower(trim((string) $value));
    }
}
