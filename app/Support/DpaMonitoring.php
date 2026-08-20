<?php

namespace App\Support;

class DpaMonitoring
{
    public const KEGIATAN_UTAMA = 'Kegiatan Bidang Pertamanan dan Pemakaman';

    public const TAHAP_PENGADAAN = 'pengadaan';

    public const TAHAP_KONTRAK = 'kontrak';

    public const TAHAP_SELESAI = 'selesai';

    /** @var list<string> */
    public const TAHAP = [
        self::TAHAP_PENGADAAN,
        self::TAHAP_KONTRAK,
        self::TAHAP_SELESAI,
    ];

    /** @var list<array{slug: string, label: string}> */
    public const SUB_KEGIATAN = [
        [
            'slug' => 'pengelolaan_keanekaragaman_hayati_lainnya',
            'label' => 'Pengelolaan Keanekaragaman Hayati Lainnya',
        ],
        [
            'slug' => 'pengelolaan_keanekaragaman_hayati_luar_kawasan_hutan',
            'label' => 'Pengelolaan Keanekaragaman Hayati di Luar Kawasan Hutan',
        ],
        [
            'slug' => 'pengelolaan_ruang_terbuka_hijau',
            'label' => 'Pengelolaan Ruang Terbuka Hijau',
        ],
    ];

    /** @var array<string, list<array{kode: string, label: string}>> */
    public const DOKUMEN_PER_TAHAP = [
        self::TAHAP_PENGADAAN => [
            ['kode' => 'usulan_pengadaan', 'label' => 'Usulan Pengadaan'],
            ['kode' => 'hps', 'label' => 'Harga Perkiraan Sendiri (HPS)'],
            ['kode' => 'spesifikasi_teknis', 'label' => 'Spesifikasi Teknis Pekerjaan'],
        ],
        self::TAHAP_KONTRAK => [
            ['kode' => 'spk', 'label' => 'SPK / Kontrak'],
            ['kode' => 'ba_pemeriksaan', 'label' => 'Berita Acara Pemeriksaan'],
            ['kode' => 'ba_penyelesaian', 'label' => 'Berita Acara Penyelesaian'],
            ['kode' => 'ba_serah_terima', 'label' => 'Berita Acara Serah Terima'],
            ['kode' => 'ba_pembayaran', 'label' => 'Berita Acara Pembayaran'],
            ['kode' => 'kwitansi', 'label' => 'Kwitansi'],
        ],
        self::TAHAP_SELESAI => [
            ['kode' => 'output_pekerjaan', 'label' => 'Output Hasil Pekerjaan'],
        ],
    ];

    public static function subKegiatanLabel(string $slug): string
    {
        foreach (self::SUB_KEGIATAN as $item) {
            if ($item['slug'] === $slug) {
                return $item['label'];
            }
        }

        return $slug;
    }

    public static function tahapLabel(string $tahap): string
    {
        return match ($tahap) {
            self::TAHAP_PENGADAAN => 'Pengadaan',
            self::TAHAP_KONTRAK => 'Kontrak',
            self::TAHAP_SELESAI => 'Selesai',
            default => ucfirst($tahap),
        };
    }

    public static function dokumenLabel(string $kode): string
    {
        foreach (self::DOKUMEN_PER_TAHAP as $documents) {
            foreach ($documents as $document) {
                if ($document['kode'] === $kode) {
                    return $document['label'];
                }
            }
        }

        return $kode;
    }

    /**
     * @return list<array{kode: string, label: string}>
     */
    public static function dokumenForTahap(string $tahap): array
    {
        return self::DOKUMEN_PER_TAHAP[$tahap] ?? [];
    }
}
