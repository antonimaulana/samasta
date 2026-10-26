<?php

namespace App\Support;

class FaqDefaults
{
    /**
     * @return array<string, mixed>
     */
    public static function settings(): array
    {
        return [
            'section_badge' => 'FAQ',
            'section_title' => 'Pertanyaan yang Sering Diajukan',
            'section_subtitle' => null,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function items(): array
    {
        return [
            [
                'is_active' => true,
                'urutan' => 10,
                'question' => 'Bagaimana cara melaporkan kondisi taman atau tanaman?',
                'answer' => 'Gunakan menu Masukan/Aduan di portal, lengkapi lokasi, uraian, dan foto jika ada. Tim Disperakimtan akan menindaklanjuti sesuai alur layanan.',
            ],
            [
                'is_active' => true,
                'urutan' => 20,
                'question' => 'Apa saja yang termasuk Ruang Terbuka Hijau (RTH) di Batam?',
                'answer' => 'Meliputi taman kota, taman lingkungan, jalur hijau, Kebun Raya Batam, dan ruang publik hijau lain yang dikelola Pemerintah Kota Batam.',
            ],
            [
                'is_active' => true,
                'urutan' => 30,
                'question' => 'Bagaimana mengajukan pemangkasan atau penanganan pohon?',
                'answer' => 'Ajukan permohonan layanan pertamanan melalui portal masukan. Petugas akan mengecek lokasi dan menjadwalkan tim sesuai wilayah kerja.',
            ],
            [
                'is_active' => true,
                'urutan' => 40,
                'question' => 'Di mana saya melihat data taman dan peta RTH?',
                'answer' => 'Buka halaman RTH Kota Batam untuk daftar lokasi, luasan, kategori, dan peta interaktif taman.',
            ],
        ];
    }
}
