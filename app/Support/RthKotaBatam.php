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

                'nama' => 'RTH Taman Kota',

                'luas' => 156_216,

                'lokasi' => 11,

                'icon' => '🏙️',

                'ringkas' => 'Taman kota utama yang menjadi pusat rekreasi masyarakat.',

            ],

            [

                'nama' => 'RTH Taman Lingkungan',

                'luas' => 23_191,

                'lokasi' => 70,

                'icon' => '🌳',

                'ringkas' => 'Ruangs hijau skala lingkungan di berbagai permukiman.',

            ],

            [

                'nama' => 'RTH Jalur Hijau Jalan',

                'luas' => 1_690_920,

                'lokasi' => 119,

                'icon' => '🛣️',

                'ringkas' => 'Jalur hijau sepanjang ruas jalan untuk kesegaran urban.',

            ],

            [

                'nama' => 'Kebun Raya Batam',

                'luas' => 300_000,

                'lokasi' => 1,

                'icon' => '🌺',

                'ringkas' => 'Kawasan konservasi dan edukasi keanekaragaman flora.',

            ],

            [

                'nama' => 'TPU',

                'luas' => 387_727,

                'lokasi' => 3,

                'icon' => '🕊️',

                'ringkas' => 'Tempat pemakaman umum dengan penataan ruang terbuka hijau.',

            ],

        ];

    }

}


