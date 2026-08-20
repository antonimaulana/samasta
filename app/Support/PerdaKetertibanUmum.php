<?php

namespace App\Support;

class PerdaKetertibanUmum
{
    public const PERDA_UTAMA = 'Perda Kota Batam Nomor 16 Tahun 2007 tentang Ketertiban Umum';

    public const PERDA_PEMBARUAN = 'Perda Kota Batam Nomor 9 Tahun 2021';

    /**
     * @return list<array{icon: string, title: string, text: string}>
     */
    public static function laranganTaman(): array
    {
        return [
            [
                'icon' => '🌿',
                'title' => 'Lindungi Vegetasi & Fasilitas',
                'text' => 'Dilarang merusak tanaman, memetik bunga, membuang sampah, atau merusak fasilitas dan inventaris di taman serta jalur hijau.',
            ],
            [
                'icon' => '🚫',
                'title' => 'Tanpa Berjualan & Bermukim',
                'text' => 'Dilarang mendirikan bangunan liar, berjualan sembarangan, tidur, atau bermukim di area taman dan ruang terbuka hijau.',
            ],
            [
                'icon' => '🗑️',
                'title' => 'Jaga Kebersihan',
                'text' => 'Dilarang membuang sampah atau limbah di taman, fasilitas umum, dan jalur hijau agar fungsi ruang hijau tetap terjaga.',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function sanksi(): array
    {
        return [
            'Teguran lisan/tertulis',
            'Kerja sosial',
            'Denda administratif',
            'Penghentian kegiatan',
            'Pembongkaran bangunan liar',
        ];
    }
}
