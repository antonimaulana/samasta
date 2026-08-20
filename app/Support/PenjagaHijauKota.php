<?php

namespace App\Support;

class PenjagaHijauKota
{
    /**
     * @return list<array{image: string, title: string, caption: string, tag: string}>
     */
    public static function gallery(): array
    {
        return [
            [
                'image' => 'images/penjaga-hijau/penjaga-menanam.png',
                'title' => 'Menanam Bibit',
                'caption' => 'Tim pertamanan menanam bibit di ruang hijau kota agar Batam semakin asri.',
                'tag' => 'Penghijauan',
            ],
            [
                'image' => 'images/penjaga-hijau/penjaga-memangkas.png',
                'title' => 'Merapikan Tanaman',
                'caption' => 'Pemangkasan rutin menjaga taman tetap rapi, aman, dan nyaman dipakai warga.',
                'tag' => 'Pemeliharaan',
            ],
            [
                'image' => 'images/penjaga-hijau/penjaga-merawat.png',
                'title' => 'Merawat Area Hijau',
                'caption' => 'Petugas lapangan merawat vegetasi dengan alat pertamanan setiap hari.',
                'tag' => 'Satgas Lapangan',
            ],
            [
                'image' => 'images/penjaga-hijau/penjaga-bibit.png',
                'title' => 'Pemangkasan Pohon',
                'caption' => 'Tim armada merapikan cabang pohon dengan crane agar vegetasi tetap aman dan rapi.',
                'tag' => 'Armada Lapangan',
            ],
        ];
    }
}
