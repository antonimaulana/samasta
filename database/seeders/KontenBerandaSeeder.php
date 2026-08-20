<?php

namespace Database\Seeders;

use App\Models\KotaProfile;
use App\Models\Pejabat;
use App\Models\RthKategori;
use App\Support\KontenBerandaCache;
use App\Support\PemerintahKotaBatam;
use App\Support\RthKotaBatam;
use Illuminate\Database\Seeder;

class KontenBerandaSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PemerintahKotaBatam::pimpinanStatic() as $index => $item) {
            Pejabat::query()->updateOrCreate(
                ['nama' => $item['name']],
                [
                    'jabatan' => $item['jabatan'],
                    'image_path' => $item['image'],
                    'accent_bg' => $item['accent_bg'],
                    'accent_ring' => $item['accent_ring'],
                    'photo_class' => $item['photo_class'] ?? null,
                    'photo_frame_class' => $item['photo_frame_class'] ?? null,
                    'urutan' => $index,
                    'is_published' => true,
                ],
            );
        }

        foreach (RthKotaBatam::kategoriStatic() as $index => $item) {
            RthKategori::query()->updateOrCreate(
                ['nama' => $item['nama']],
                [
                    'luas' => $item['luas'],
                    'lokasi' => $item['lokasi'],
                    'icon' => $item['icon'],
                    'ringkas' => $item['ringkas'],
                    'urutan' => $index,
                    'is_published' => true,
                ],
            );
        }

        $visiMisi = PemerintahKotaBatam::visiMisiStatic();

        KotaProfile::query()->updateOrCreate(
            ['id' => 1],
            [
                'visi' => $visiMisi['visi'],
                'misi' => $visiMisi['misi'],
            ],
        );

        KontenBerandaCache::forgetAll();
    }
}
