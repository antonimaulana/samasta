<?php

namespace Database\Seeders;

use App\Models\Taman;
use Illuminate\Database\Seeder;

class TamanSeeder extends Seeder
{
    public function run(): void
    {
        $tamans = [
            [
                'nama_taman' => 'Taman Majapahit',
                'kategori' => 'Taman Kota',
                'luasan' => 25000,
                'alamat' => 'Jl. Raja Haji Fisabilillah, Batam Center, Kota Batam',
                'latitude' => 1.1083,
                'longitude' => 104.0305,
                'deskripsi' => 'Taman ikonik di pusat Batam dengan area hijau luas, cocok untuk rekreasi keluarga dan olahraga pagi.',
                'fasilitas' => ['Area jogging', 'Tempat duduk', 'Lampu taman', 'Toilet umum'],
            ],
            [
                'nama_taman' => 'Taman Berlian',
                'kategori' => 'Taman Lingkungan',
                'luasan' => 3500,
                'alamat' => 'Jl. Berlian, Nagoya, Lubuk Baja, Kota Batam',
                'latitude' => 1.1478,
                'longitude' => 104.0123,
                'deskripsi' => 'Taman di kawasan Nagoya yang menjadi ruang publik favorit warga untuk bersantai di sore hari.',
                'fasilitas' => ['Area bermain anak', 'Tempat duduk', 'Pohon rindang'],
            ],
            [
                'nama_taman' => 'Taman KDA',
                'kategori' => 'Taman Kota',
                'luasan' => 18000,
                'alamat' => 'Jl. Engku Putri, Batam Center, Kota Batam',
                'latitude' => 1.1156,
                'longitude' => 104.0389,
                'deskripsi' => 'Taman dengan pemandangan danau buatan, sering digunakan untuk acara komunitas dan pameran lokal.',
                'fasilitas' => ['Area piknik', 'Jogging track', 'Kolam ikan', 'Area event'],
            ],
            [
                'nama_taman' => 'Taman Golden City',
                'kategori' => 'Taman Lingkungan',
                'luasan' => 4200,
                'alamat' => 'Golden City, Batam Center, Kota Batam',
                'latitude' => 1.1210,
                'longitude' => 104.0450,
                'deskripsi' => 'Taman perumahan terbuka yang nyaman dengan landscape modern dan area bermain untuk anak-anak.',
                'fasilitas' => ['Playground', 'Area fitness outdoor', 'Tempat duduk', 'Lampu taman'],
            ],
            [
                'nama_taman' => 'Taman Sukajadi',
                'kategori' => 'Taman Lingkungan',
                'luasan' => 2800,
                'alamat' => 'Sukajadi, Batam Kota, Kota Batam',
                'latitude' => 1.0890,
                'longitude' => 104.0210,
                'deskripsi' => 'Ruangan hijau di tengah permukiman padat yang memberikan udara segar dan tempat berkumpul warga.',
                'fasilitas' => ['Lapangan kecil', 'Tempat duduk', 'Pohon peneduh'],
            ],
            [
                'nama_taman' => 'Taman Cemara Asri',
                'kategori' => 'Taman Kota',
                'luasan' => 12000,
                'alamat' => 'Batam Center, Kota Batam',
                'latitude' => 1.1125,
                'longitude' => 104.0340,
                'deskripsi' => 'Taman kota dengan vegetasi cemara dan area hijau terpelihara, tersertifikasi RBRA.',
                'fasilitas' => ['Jogging track', 'Tempat duduk', 'Area piknik', 'Lampu taman'],
            ],
            [
                'nama_taman' => 'Dataran Engku Putri',
                'kategori' => 'Taman Kota',
                'luasan' => 15000,
                'alamat' => 'Jl. Engku Putri, Batam Center, Kota Batam',
                'latitude' => 1.1168,
                'longitude' => 104.0375,
                'deskripsi' => 'Ruangan terbuka hijau di pusat Batam yang menjadi destinasi favorit warga untuk rekreasi dan bersantai.',
                'fasilitas' => ['Area piknik', 'Jogging track', 'Tempat duduk', 'Pohon rindang'],
            ],
        ];

        foreach ($tamans as $taman) {
            Taman::firstOrCreate(
                ['nama_taman' => $taman['nama_taman']],
                $taman
            );
        }
    }
}
