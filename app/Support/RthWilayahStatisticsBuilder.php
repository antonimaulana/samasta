<?php

namespace App\Support;

use App\Models\Kecamatan;
use App\Models\Taman;
use Illuminate\Support\Collection;

class RthWilayahStatisticsBuilder
{
    /**
     * @return array{jumlah_taman: int, total_luasan: int, belum_wilayah: int}
     */
    public function totals(): array
    {
        $jumlah = Taman::count();
        $totalLuas = (int) Taman::sum('luasan');
        $belumWilayah = Taman::whereNull('kelurahan_id')->count();

        return [
            'jumlah_taman' => $jumlah,
            'total_luasan' => $totalLuas,
            'belum_wilayah' => $belumWilayah,
        ];
    }

    /**
     * @return Collection<int, object{kecamatan: string, jumlah_taman: int, total_luasan: int}>
     */
    public function perKecamatan(): Collection
    {
        return Taman::query()
            ->join('kelurahans', 'tamans.kelurahan_id', '=', 'kelurahans.id')
            ->join('kecamatans', 'kelurahans.kecamatan_id', '=', 'kecamatans.id')
            ->selectRaw('kecamatans.nama as kecamatan')
            ->selectRaw('COUNT(tamans.id) as jumlah_taman')
            ->selectRaw('COALESCE(SUM(tamans.luasan), 0) as total_luasan')
            ->groupBy('kecamatans.id', 'kecamatans.nama')
            ->orderBy('kecamatans.nama')
            ->get();
    }

    /**
     * @return Collection<int, object{kecamatan: string, kelurahan: string, jumlah_taman: int, total_luasan: int}>
     */
    public function perKelurahan(): Collection
    {
        return Taman::query()
            ->join('kelurahans', 'tamans.kelurahan_id', '=', 'kelurahans.id')
            ->join('kecamatans', 'kelurahans.kecamatan_id', '=', 'kecamatans.id')
            ->selectRaw('kecamatans.nama as kecamatan')
            ->selectRaw('kelurahans.nama as kelurahan')
            ->selectRaw('COUNT(tamans.id) as jumlah_taman')
            ->selectRaw('COALESCE(SUM(tamans.luasan), 0) as total_luasan')
            ->groupBy('kecamatans.nama', 'kelurahans.nama', 'kelurahans.id')
            ->orderBy('kecamatans.nama')
            ->orderBy('kelurahans.nama')
            ->get();
    }

    /**
     * @return list<array{kecamatan: string, kelurahan: list<array{kelurahan: string, jumlah_taman: int, total_luasan: int}>}>
     */
    public function perKelurahanGrouped(): array
    {
        return $this->perKelurahan()
            ->groupBy('kecamatan')
            ->map(fn (Collection $rows, string $kecamatan) => [
                'kecamatan' => $kecamatan,
                'jumlah_taman' => (int) $rows->sum('jumlah_taman'),
                'total_luasan' => (int) $rows->sum('total_luasan'),
                'kelurahan' => $rows->map(fn ($row) => [
                    'kelurahan' => $row->kelurahan,
                    'jumlah_taman' => (int) $row->jumlah_taman,
                    'total_luasan' => (int) $row->total_luasan,
                ])->values()->all(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{nama: string, jumlah_taman: int, total_luasan: int}>
     */
    public function perKategori(): array
    {
        return Taman::query()
            ->selectRaw('kategori as nama')
            ->selectRaw('COUNT(*) as jumlah_taman')
            ->selectRaw('COALESCE(SUM(luasan), 0) as total_luasan')
            ->groupBy('kategori')
            ->orderBy('kategori')
            ->get()
            ->map(fn ($row) => [
                'nama' => $row->nama,
                'jumlah_taman' => (int) $row->jumlah_taman,
                'total_luasan' => (int) $row->total_luasan,
            ])
            ->all();
    }

    /**
     * Kecamatan tanpa taman terdaftar (untuk kelengkapan peta wilayah).
     *
     * @return list<string>
     */
    public function kecamatanTanpaTaman(): array
    {
        $withTaman = $this->perKecamatan()->pluck('kecamatan')->all();

        return Kecamatan::query()
            ->orderBy('nama')
            ->pluck('nama')
            ->reject(fn (string $nama) => in_array($nama, $withTaman, true))
            ->values()
            ->all();
    }
}
