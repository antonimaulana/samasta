<?php

namespace App\Support;

use App\Models\Taman;

class RthKotaBatam
{
    /**
     * @return list<array{nama: string, luas: int, lokasi: int, icon: string, ringkas: string}>
     */
    public static function kategori(): array
    {
        return self::kategoriStatic();
    }

    /**
     * @return array{luas: int, lokasi: int}
     */
    public static function total(): array
    {

        $kategori = self::kategori();

        return [

            'luas' => array_sum(array_column($kategori, 'luas')),

            'lokasi' => array_sum(array_column($kategori, 'lokasi')),

        ];

    }

    /**
     * @return list<array{nama: string, luas: int, lokasi: int, icon: string, ringkas: string}>
     */
    public static function kategoriStatic(): array
    {

        return [

            [

                'kategori' => 'Taman Kota',

                'nama' => 'Taman Kota',

                'luas' => 156_216,

                'lokasi' => 11,

                'icon' => '🏙️',

                'ringkas' => 'Taman kota utama yang menjadi pusat rekreasi masyarakat.',

                'accent' => Taman::kategoriDisplayAccent('Taman Kota'),

            ],

            [

                'kategori' => 'Taman Lingkungan',

                'nama' => 'Taman Lingkungan',

                'luas' => 23_191,

                'lokasi' => 70,

                'icon' => '🌳',

                'ringkas' => 'Ruangs hijau skala lingkungan di berbagai permukiman.',

                'accent' => Taman::kategoriDisplayAccent('Taman Lingkungan'),

            ],

            [

                'kategori' => 'Jalur Hijau Jalan',

                'nama' => 'Jalur Hijau Jalan',

                'luas' => 1_690_920,

                'lokasi' => 119,

                'icon' => '🛣️',

                'ringkas' => 'Jalur hijau sepanjang ruas jalan untuk kesegaran urban.',

                'accent' => Taman::kategoriDisplayAccent('Jalur Hijau Jalan'),

            ],

            [

                'kategori' => 'Kebun Raya',

                'nama' => 'Kebun Raya',

                'luas' => 300_000,

                'lokasi' => 1,

                'icon' => '🌺',

                'ringkas' => 'Kawasan konservasi dan edukasi keanekaragaman flora.',

                'accent' => Taman::kategoriDisplayAccent('Kebun Raya'),

            ],

            [

                'kategori' => 'TPU',

                'nama' => 'TPU',

                'luas' => 387_727,

                'lokasi' => 3,

                'icon' => '🕊️',

                'ringkas' => 'Tempat pemakaman umum dengan penataan ruang terbuka hijau.',

                'accent' => Taman::kategoriDisplayAccent('TPU'),

            ],

        ];

    }

    /**
     * @return array{nama: string, icon: string, ringkas: string, accent: array{bg: string, chart: string}}
     */
    public static function metaForKategori(string $kategori): array
    {
        $match = collect(self::kategoriStatic())->firstWhere('kategori', $kategori);

        if ($match !== null) {
            return [
                'nama' => $match['nama'],
                'icon' => $match['icon'],
                'ringkas' => $match['ringkas'],
                'accent' => $match['accent'],
            ];
        }

        return [
            'nama' => $kategori,
            'icon' => '🌿',
            'ringkas' => 'Kategori RTH terdaftar di SIMTAMAN.',
            'accent' => Taman::kategoriDisplayAccent($kategori),
        ];
    }
}
