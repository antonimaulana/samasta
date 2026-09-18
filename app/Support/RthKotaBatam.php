<?php

namespace App\Support;

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

                'nama' => 'RTH Taman Kota',

                'luas' => 156_216,

                'lokasi' => 11,

                'icon' => '🏙️',

                'ringkas' => 'Taman kota utama yang menjadi pusat rekreasi masyarakat.',

                'accent' => ['bg' => 'from-emerald-500 to-teal-600', 'chart' => 'rgba(16, 185, 129, 0.85)'],

            ],

            [

                'kategori' => 'Taman Lingkungan',

                'nama' => 'RTH Taman Lingkungan',

                'luas' => 23_191,

                'lokasi' => 70,

                'icon' => '🌳',

                'ringkas' => 'Ruangs hijau skala lingkungan di berbagai permukiman.',

                'accent' => ['bg' => 'from-lime-500 to-green-600', 'chart' => 'rgba(34, 197, 94, 0.85)'],

            ],

            [

                'kategori' => 'Jalur Hijau Jalan',

                'nama' => 'RTH Jalur Hijau Jalan',

                'luas' => 1_690_920,

                'lokasi' => 119,

                'icon' => '🛣️',

                'ringkas' => 'Jalur hijau sepanjang ruas jalan untuk kesegaran urban.',

                'accent' => ['bg' => 'from-teal-500 to-cyan-600', 'chart' => 'rgba(20, 184, 166, 0.85)'],

            ],

            [

                'kategori' => 'Kebun Raya',

                'nama' => 'Kebun Raya Batam',

                'luas' => 300_000,

                'lokasi' => 1,

                'icon' => '🌺',

                'ringkas' => 'Kawasan konservasi dan edukasi keanekaragaman flora.',

                'accent' => ['bg' => 'from-green-600 to-emerald-700', 'chart' => 'rgba(5, 150, 105, 0.85)'],

            ],

            [

                'kategori' => 'TPU',

                'nama' => 'TPU',

                'luas' => 387_727,

                'lokasi' => 3,

                'icon' => '🕊️',

                'ringkas' => 'Tempat pemakaman umum dengan penataan ruang terbuka hijau.',

                'accent' => ['bg' => 'from-slate-500 to-slate-600', 'chart' => 'rgba(100, 116, 139, 0.85)'],

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
            'accent' => ['bg' => 'from-emerald-500 to-teal-600', 'chart' => 'rgba(16, 185, 129, 0.85)'],
        ];
    }
}
