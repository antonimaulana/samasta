<?php

namespace App\Support;

use App\Models\KotaProfile;
use App\Models\Pejabat;
use App\Models\RthKategori;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class KontenBerandaCache
{
    public static function forgetAll(): void
    {
        Cache::forget('konten_beranda_pejabats');
        Cache::forget('konten_beranda_visi_misi');
        Cache::forget('konten_beranda_rth_kategoris');
        Cache::forget('taman_map_points');
    }

    /**
     * @return list<array{name: string, jabatan: string, image: string, accent_bg: string, accent_ring: string, photo_class?: string, photo_frame_class?: string}>
     */
    public static function pejabats(): array
    {
        if (! Schema::hasTable('pejabats') || Pejabat::query()->where('is_published', true)->doesntExist()) {
            return PemerintahKotaBatam::pimpinanStatic();
        }

        return Cache::remember('konten_beranda_pejabats', now()->addMinutes(30), function () {
            return Pejabat::query()
                ->where('is_published', true)
                ->orderBy('urutan')
                ->orderBy('nama')
                ->get()
                ->map(fn (Pejabat $pejabat) => $pejabat->toCardArray())
                ->all();
        });
    }

    /**
     * @return array{visi: string, misi: string}
     */
    public static function visiMisi(): array
    {
        if (! Schema::hasTable('kota_profiles')) {
            return PemerintahKotaBatam::visiMisiStatic();
        }

        return Cache::remember('konten_beranda_visi_misi', now()->addMinutes(30), function () {
            $profile = KotaProfile::current();

            if (blank($profile->visi) && blank($profile->misi)) {
                return PemerintahKotaBatam::visiMisiStatic();
            }

            return [
                'visi' => $profile->visi ?? '',
                'misi' => $profile->misi ?? '',
            ];
        });
    }

    /**
     * @return list<array{nama: string, luas: int, lokasi: int, icon: string, ringkas: string}>
     */
    public static function rthKategoris(): array
    {
        if (! Schema::hasTable('rth_kategoris') || RthKategori::query()->where('is_published', true)->doesntExist()) {
            return RthKotaBatam::kategoriStatic();
        }

        return Cache::remember('konten_beranda_rth_kategoris', now()->addMinutes(30), function () {
            return RthKategori::query()
                ->where('is_published', true)
                ->orderBy('urutan')
                ->orderBy('nama')
                ->get()
                ->map(fn (RthKategori $kategori) => $kategori->toSummaryArray())
                ->all();
        });
    }
}
