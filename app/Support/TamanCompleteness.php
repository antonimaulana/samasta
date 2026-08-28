<?php

namespace App\Support;

use App\Models\Taman;

class TamanCompleteness
{
    /**
     * @return list<array{key: string, label: string, filled: bool}>
     */
    public function breakdown(Taman $taman): array
    {
        return collect($this->checks($taman))
            ->map(fn (array $check) => [
                'key' => $check['key'],
                'label' => $check['label'],
                'filled' => $check['filled'],
            ])
            ->values()
            ->all();
    }

    public function score(Taman $taman): int
    {
        $checks = $this->checks($taman);

        if ($checks === []) {
            return 0;
        }

        $filled = collect($checks)->where('filled', true)->count();

        return (int) round(($filled / count($checks)) * 100);
    }

    public function labelFor(Taman $taman): string
    {
        return $this->statusFor($taman);
    }

    public function statusFor(Taman $taman): string
    {
        return $this->score($taman) === 100
            ? Taman::STATUS_DATA_LENGKAP
            : Taman::STATUS_DATA_BELUM_LENGKAP;
    }

    public function labelFromScore(int $score): string
    {
        return $score === 100
            ? Taman::STATUS_DATA_LENGKAP
            : Taman::STATUS_DATA_BELUM_LENGKAP;
    }

    /**
     * @return list<array{key: string, label: string, filled: bool}>
     */
    private function checks(Taman $taman): array
    {
        $taman->loadMissing('images', 'kelurahan');

        $hasGallery = $taman->images->isNotEmpty() || filled($taman->foto);
        $hasFasilitas = count($taman->fasilitas_items) > 0;

        return [
            ['key' => 'nama_taman', 'label' => 'Nama taman', 'filled' => filled($taman->nama_taman)],
            ['key' => 'kategori', 'label' => 'Kategori', 'filled' => filled($taman->kategori)],
            ['key' => 'deskripsi', 'label' => 'Deskripsi', 'filled' => filled(trim(strip_tags((string) $taman->deskripsi)))],
            ['key' => 'alamat', 'label' => 'Alamat', 'filled' => filled(trim((string) $taman->alamat))],
            ['key' => 'kelurahan', 'label' => 'Kelurahan / kecamatan', 'filled' => filled($taman->kelurahan_id)],
            ['key' => 'koordinat', 'label' => 'Latitude & longitude', 'filled' => filled($taman->latitude) && filled($taman->longitude)],
            ['key' => 'luasan', 'label' => 'Luasan (M²)', 'filled' => (int) $taman->luasan > 0],
            ['key' => 'galeri', 'label' => 'Galeri foto', 'filled' => $hasGallery],
            ['key' => 'fasilitas', 'label' => 'Fasilitas & kondisi', 'filled' => $hasFasilitas],
            ['key' => 'tahun_pembangunan', 'label' => 'Tahun pembangunan', 'filled' => filled($taman->tahun_pembangunan)],
            ['key' => 'nilai_pembangunan', 'label' => 'Nilai pembangunan', 'filled' => filled($taman->nilai_pembangunan)],
            ['key' => 'kontraktor', 'label' => 'Kontraktor', 'filled' => filled($taman->kontraktor)],
            ['key' => 'konsultan_perencana', 'label' => 'Konsultan perencana', 'filled' => filled($taman->konsultan_perencana)],
        ];
    }
}
