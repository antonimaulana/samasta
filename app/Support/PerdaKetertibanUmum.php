<?php

namespace App\Support;

class PerdaKetertibanUmum
{
    public const PERDA_UTAMA = 'Perda Kota Batam Nomor 16 Tahun 2007 tentang Ketertiban Umum';

    public const PERDA_PEMBARUAN = 'Perda Kota Batam Nomor 9 Tahun 2021';

    public static function pengantar(): string
    {
        return 'Ruang terbuka hijau, jalur hijau, dan taman kota dilindungi aturan ketertiban umum agar tetap hijau, rapi, aman, dan nyaman bagi seluruh warga. Ringkasan berikut disusun agar mudah dipahami — rincian lengkap ada di Perda dan peraturan turunannya.';
    }

    /**
     * @return list<array{icon: string, jenis: string, title: string, text: string, ringkas: string}>
     */
    public static function aturanPertamanan(): array
    {
        return [
            [
                'icon' => '🌳',
                'jenis' => 'Larangan',
                'title' => 'Jangan rusak tanaman & fasilitas taman',
                'text' => 'Setiap orang dilarang merusak, menebang, mencabut, atau memindahkan tanaman dan pohon di jalur hijau, taman kota, dan fasilitas umum tanpa izin resmi dari instansi berwenang.',
                'ringkas' => 'Tanaman dan fasilitas taman milik bersama — lindungi, jangan rusak atau ambil tanpa izin.',
            ],
            [
                'icon' => '🏗️',
                'jenis' => 'Larangan',
                'title' => 'Jangan alih fungsi lahan hijau',
                'text' => 'Dilarang mendirikan bangunan liar—misalnya kios, lapak PKL, atau hunian sementara—di atas lahan yang diperuntukkan sebagai taman kota atau jalur hijau.',
                'ringkas' => 'Taman dan jalur hijau bukan untuk bangunan atau jualan sembarangan.',
            ],
            [
                'icon' => '🗑️',
                'jenis' => 'Kewajiban',
                'title' => 'Jaga kebersihan area hijau',
                'text' => 'Dilarang membuang sampah, limbah, atau material bangunan ke area taman yang dapat merusak ekosistem dan keindahan tanaman.',
                'ringkas' => 'Buang sampah pada tempatnya; jangan mencemari taman.',
            ],
            [
                'icon' => '📋',
                'jenis' => 'Ketertiban',
                'title' => 'Aktivitas komersial butuh izin',
                'text' => 'Pemanfaatan taman untuk kegiatan komersial—seperti baliho/reklame atau acara komersial—wajib memiliki izin resmi agar tidak mengganggu tata ruang dan kenyamanan pejalan kaki.',
                'ringkas' => 'Acara atau promosi di taman hanya dengan izin resmi Pemda.',
            ],
        ];
    }

    /**
     * @return list<array{icon: string, title: string, text: string}>
     *
     * @deprecated Use aturanPertamanan() for full content; kept for backward-compatible callers.
     */
    public static function laranganTaman(): array
    {
        return array_map(static fn (array $item): array => [
            'icon' => $item['icon'],
            'title' => $item['title'],
            'text' => $item['ringkas'],
        ], self::aturanPertamanan());
    }

    /**
     * @return list<string>
     */
    public static function sanksi(): array
    {
        return [
            'Teguran lisan atau tertulis',
            'Kerja sosial',
            'Denda administratif',
            'Penghentian kegiatan',
            'Pembongkaran bangunan liar',
        ];
    }

    /**
     * @return list<string>
     */
    public static function sumberAcuan(): array
    {
        return [
            'JDIH Pemerintah Kota Batam (Perda Ketertiban Umum)',
            'Peraturan dan kebijakan Disperakimtan terkait RTH dan pertamanan',
        ];
    }
}
