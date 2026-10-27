<?php

namespace App\Support;

class PenjagaHijauKota
{
    /**
     * @return list<array{image: string, title: string, caption: string, tag: string, focus?: string, photo_modifier?: string}>
     */
    public static function gallery(): array
    {
        return [
            [
                'image' => 'images/penjaga-hijau/penjaga-memangkas.jpg',
                'title' => 'Merapikan Semak',
                'caption' => 'Petugas merapikan tanaman semak agar taman tetap rapi dan aman bagi pengunjung.',
                'tag' => 'Pemeliharaan',
                'focus' => 'center 18%',
            ],
            [
                'image' => 'images/penjaga-hijau/penjaga-rumput.jpg',
                'title' => 'Merawat Rumput',
                'caption' => 'Pemotongan rumput rutin menjaga area hijau tetap hijau, bersih, dan nyaman.',
                'tag' => 'Satgas Lapangan',
                'focus' => 'center 28%',
            ],
            [
                'image' => 'images/penjaga-hijau/penjaga-semak.jpg',
                'title' => 'Pemangkasan Vegetasi',
                'caption' => 'Tim lapangan membentuk vegetasi dengan alat pertamanan di ruang terbuka hijau.',
                'tag' => 'Operasional',
                'focus' => 'center 24%',
            ],
            [
                'image' => 'images/penjaga-hijau/penjaga-taman.jpg',
                'title' => 'Menanam Bibit',
                'caption' => 'Tim pertamanan menyiapkan dan merawat bibit di nursery agar siap ditanam di ruang hijau kota.',
                'tag' => 'Penghijauan',
                'focus' => '50% 38%',
                'photo_modifier' => 'penjaga-hijau__photo--nursery',
            ],
        ];
    }
}
