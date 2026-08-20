<?php

namespace App\Support;

class WilayahKotaBatam
{
    /**
     * Data resmi 12 kecamatan & 64 kelurahan Kota Batam (Kemendagri).
     *
     * @return list<array{kode: string, nama: string, kelurahan: list<string>}>
     */
    public static function kecamatan(): array
    {
        return [
            ['kode' => '21.71.10', 'nama' => 'Batam Kota', 'kelurahan' => ['Baloi Permai', 'Belian', 'Sukajadi', 'Sungai Panas', 'Taman Baloi', 'Teluk Tering']],
            ['kode' => '21.71.12', 'nama' => 'Batu Aji', 'kelurahan' => ['Bukit Tempayan', 'Buliang', 'Kibing', 'Tanjung Uncang']],
            ['kode' => '21.71.02', 'nama' => 'Batu Ampar', 'kelurahan' => ['Batu Merah', 'Kampung Seraya', 'Sungai Jodoh', 'Tanjung Sengkuang']],
            ['kode' => '21.71.01', 'nama' => 'Belakang Padang', 'kelurahan' => ['Kasu', 'Pecong', 'Pemping', 'Pulau Terong', 'Sekanak Raya', 'Tanjung Sari']],
            ['kode' => '21.71.09', 'nama' => 'Bengkong', 'kelurahan' => ['Bengkong Indah', 'Bengkong Laut', 'Sadai', 'Tanjung Buntung']],
            ['kode' => '21.71.05', 'nama' => 'Bulang', 'kelurahan' => ['Batu Legong', 'Bulang Lintang', 'Pantai Gelam', 'Pulau Buluh', 'Setokok', 'Temoyong']],
            ['kode' => '21.71.08', 'nama' => 'Galang', 'kelurahan' => ['Air Raja', 'Galang Baru', 'Karas', 'Pulau Abang', 'Rempang Cate', 'Sembulang', 'Sijantung', 'Subang Mas']],
            ['kode' => '21.71.06', 'nama' => 'Lubuk Baja', 'kelurahan' => ['Baloi Indah', 'Batu Selicin', 'Kampung Pelita', 'Lubuk Baja Kota', 'Tanjung Uma']],
            ['kode' => '21.71.04', 'nama' => 'Nongsa', 'kelurahan' => ['Batu Besar', 'Kabil', 'Ngenang', 'Sambau']],
            ['kode' => '21.71.11', 'nama' => 'Sagulung', 'kelurahan' => ['Sagulung Kota', 'Sungai Binti', 'Sungai Langkai', 'Sungai Lekop', 'Sungai Pelunggut', 'Tembesi']],
            ['kode' => '21.71.07', 'nama' => 'Sei Beduk', 'kelurahan' => ['Duriangkang', 'Mangsang', 'Muka Kuning', 'Tanjung Piayu']],
            ['kode' => '21.71.03', 'nama' => 'Sekupang', 'kelurahan' => ['Patam Lestari', 'Sungai Harapan', 'Tanjung Pinggir', 'Tanjung Riau', 'Tiban Baru', 'Tiban Indah', 'Tiban Lama']],
        ];
    }
}
