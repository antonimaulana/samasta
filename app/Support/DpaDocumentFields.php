<?php

namespace App\Support;

use App\Models\DpaPaketPekerjaan;

class DpaDocumentFields
{
    /** @var array<string, list<array{name: string, label: string, type: string, required?: bool, readonly?: bool}>> */
    public const FIELDS = [
        'usulan_pengadaan' => [
            ['name' => 'nomor_permohonan', 'label' => 'Nomor Permohonan', 'type' => 'text', 'required' => true],
            ['name' => 'jumlah_lampiran', 'label' => 'Jumlah Lampiran (lembar)', 'type' => 'number', 'required' => true],
            ['name' => 'metode_pengadaan', 'label' => 'Metode Pengadaan', 'type' => 'text', 'required' => true, 'readonly' => true],
            ['name' => 'nama_kegiatan', 'label' => 'Nama Kegiatan', 'type' => 'text', 'required' => true, 'readonly' => true],
            ['name' => 'nama_sub_kegiatan', 'label' => 'Nama Sub Kegiatan', 'type' => 'text', 'required' => true, 'readonly' => true],
            ['name' => 'tahun_anggaran', 'label' => 'Tahun Anggaran', 'type' => 'text', 'required' => true, 'readonly' => true],
            ['name' => 'pagu_anggaran', 'label' => 'Pagu Anggaran (Rp)', 'type' => 'number', 'required' => true, 'readonly' => true],
            ['name' => 'kode_rekening', 'label' => 'Kode Rekening', 'type' => 'text', 'required' => true, 'readonly' => true],
            ['name' => 'nama_pekerjaan', 'label' => 'Nama Pekerjaan', 'type' => 'text', 'required' => true, 'readonly' => true],
            ['name' => 'kode_rup', 'label' => 'Kode RUP/SiRUP', 'type' => 'text', 'readonly' => true],
            ['name' => 'lokasi', 'label' => 'Lokasi', 'type' => 'text', 'required' => true],
            ['name' => 'tanggal_pelaksanaan', 'label' => 'Waktu Pelaksanaan', 'type' => 'text', 'required' => true, 'readonly' => true],
            ['name' => 'jenis_kontrak', 'label' => 'Jenis Kontrak', 'type' => 'text', 'required' => true],
            ['name' => 'tanggal_permohonan', 'label' => 'Tanggal Permohonan', 'type' => 'date', 'required' => true],
            ['name' => 'sub_kegiatan', 'label' => 'Sub Kegiatan (tanda tangan PPK)', 'type' => 'text', 'required' => true, 'readonly' => true],
            ['name' => 'nama_ppk', 'label' => 'Nama PPK', 'type' => 'text', 'required' => true],
            ['name' => 'nip_ppk', 'label' => 'NIP PPK', 'type' => 'text', 'required' => true],
        ],
        'hps' => [
            ['name' => 'nomor', 'label' => 'Nomor HPS', 'type' => 'text', 'required' => true],
            ['name' => 'tanggal', 'label' => 'Tanggal', 'type' => 'date', 'required' => true],
            ['name' => 'pejabat_nama', 'label' => 'Nama Pejabat', 'type' => 'text', 'required' => true],
            ['name' => 'pejabat_jabatan', 'label' => 'Jabatan Pejabat', 'type' => 'text', 'required' => true],
        ],
        'spesifikasi_teknis' => [
            ['name' => 'nomor', 'label' => 'Nomor Dokumen', 'type' => 'text', 'required' => true],
            ['name' => 'tanggal', 'label' => 'Tanggal', 'type' => 'date', 'required' => true],
            ['name' => 'ruang_lingkup', 'label' => 'Ruang Lingkup Pekerjaan', 'type' => 'textarea', 'required' => true],
            ['name' => 'spesifikasi', 'label' => 'Spesifikasi Teknis', 'type' => 'textarea', 'required' => true],
            ['name' => 'ketentuan', 'label' => 'Ketentuan Lain', 'type' => 'textarea'],
        ],
        'spk' => [
            ['name' => 'nomor', 'label' => 'Nomor SPK/Kontrak', 'type' => 'text', 'required' => true],
            ['name' => 'tanggal', 'label' => 'Tanggal Kontrak', 'type' => 'date', 'required' => true],
            ['name' => 'nilai_kontrak', 'label' => 'Nilai Kontrak (Rp)', 'type' => 'number', 'required' => true],
            ['name' => 'tanggal_mulai', 'label' => 'Tanggal Mulai', 'type' => 'date', 'required' => true],
            ['name' => 'tanggal_selesai', 'label' => 'Tanggal Selesai', 'type' => 'date', 'required' => true],
            ['name' => 'pejabat_nama', 'label' => 'Nama Pejabat', 'type' => 'text', 'required' => true],
            ['name' => 'pejabat_jabatan', 'label' => 'Jabatan Pejabat', 'type' => 'text', 'required' => true],
        ],
        'ba_pemeriksaan' => [
            ['name' => 'nomor', 'label' => 'Nomor BA', 'type' => 'text', 'required' => true],
            ['name' => 'tanggal', 'label' => 'Tanggal', 'type' => 'date', 'required' => true],
            ['name' => 'hasil_pemeriksaan', 'label' => 'Hasil Pemeriksaan', 'type' => 'textarea', 'required' => true],
            ['name' => 'catatan', 'label' => 'Catatan', 'type' => 'textarea'],
            ['name' => 'pejabat_nama', 'label' => 'Nama Pejabat', 'type' => 'text', 'required' => true],
            ['name' => 'pejabat_jabatan', 'label' => 'Jabatan Pejabat', 'type' => 'text', 'required' => true],
        ],
        'ba_penyelesaian' => [
            ['name' => 'nomor', 'label' => 'Nomor BA', 'type' => 'text', 'required' => true],
            ['name' => 'tanggal', 'label' => 'Tanggal', 'type' => 'date', 'required' => true],
            ['name' => 'uraian_pekerjaan', 'label' => 'Uraian Pekerjaan', 'type' => 'textarea', 'required' => true],
            ['name' => 'pejabat_nama', 'label' => 'Nama Pejabat', 'type' => 'text', 'required' => true],
            ['name' => 'pejabat_jabatan', 'label' => 'Jabatan Pejabat', 'type' => 'text', 'required' => true],
        ],
        'ba_serah_terima' => [
            ['name' => 'nomor', 'label' => 'Nomor BA', 'type' => 'text', 'required' => true],
            ['name' => 'tanggal', 'label' => 'Tanggal', 'type' => 'date', 'required' => true],
            ['name' => 'barang_diserahkan', 'label' => 'Barang/Hasil Diserahkan', 'type' => 'textarea', 'required' => true],
            ['name' => 'pejabat_nama', 'label' => 'Nama Pejabat', 'type' => 'text', 'required' => true],
            ['name' => 'pejabat_jabatan', 'label' => 'Jabatan Pejabat', 'type' => 'text', 'required' => true],
        ],
        'ba_pembayaran' => [
            ['name' => 'nomor', 'label' => 'Nomor BA', 'type' => 'text', 'required' => true],
            ['name' => 'tanggal', 'label' => 'Tanggal', 'type' => 'date', 'required' => true],
            ['name' => 'jumlah_pembayaran', 'label' => 'Jumlah Pembayaran (Rp)', 'type' => 'number', 'required' => true],
            ['name' => 'terbilang', 'label' => 'Terbilang', 'type' => 'text', 'required' => true],
            ['name' => 'pejabat_nama', 'label' => 'Nama Pejabat', 'type' => 'text', 'required' => true],
            ['name' => 'pejabat_jabatan', 'label' => 'Jabatan Pejabat', 'type' => 'text', 'required' => true],
        ],
        'kwitansi' => [
            ['name' => 'nomor', 'label' => 'Nomor Kwitansi', 'type' => 'text', 'required' => true],
            ['name' => 'tanggal', 'label' => 'Tanggal', 'type' => 'date', 'required' => true],
            ['name' => 'jumlah', 'label' => 'Jumlah (Rp)', 'type' => 'number', 'required' => true],
            ['name' => 'terbilang', 'label' => 'Terbilang', 'type' => 'text', 'required' => true],
            ['name' => 'keperluan', 'label' => 'Keperluan', 'type' => 'textarea', 'required' => true],
        ],
        'output_pekerjaan' => [
            ['name' => 'nomor', 'label' => 'Nomor Dokumen', 'type' => 'text', 'required' => true],
            ['name' => 'tanggal', 'label' => 'Tanggal', 'type' => 'date', 'required' => true],
            ['name' => 'ringkasan_hasil', 'label' => 'Ringkasan Hasil Pekerjaan', 'type' => 'textarea', 'required' => true],
            ['name' => 'pejabat_nama', 'label' => 'Nama Pejabat', 'type' => 'text', 'required' => true],
            ['name' => 'pejabat_jabatan', 'label' => 'Jabatan Pejabat', 'type' => 'text', 'required' => true],
        ],
    ];

    /** @var list<string> */
    public const ITEM_TABLE_KODES = ['hps', 'spk'];

    /**
     * @return list<array{name: string, label: string, type: string, required?: bool, readonly?: bool}>
     */
    public static function forKode(string $kode): array
    {
        return self::FIELDS[$kode] ?? [];
    }

    public static function usesItemTable(string $kode): bool
    {
        return in_array($kode, self::ITEM_TABLE_KODES, true);
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaultValues(DpaPaketPekerjaan $paket, string $kode): array
    {
        if ($kode !== 'usulan_pengadaan') {
            return [];
        }

        $paket->loadMissing('dpa.tahunAnggaran');
        $subKegiatanLabel = $paket->dpa->subKegiatanLabel();

        return [
            'jumlah_lampiran' => 4,
            'metode_pengadaan' => $paket->metode_pemilihan ?: $paket->jenis_pengadaan ?: '—',
            'nama_kegiatan' => DpaMonitoring::KEGIATAN_UTAMA,
            'nama_sub_kegiatan' => $subKegiatanLabel,
            'sub_kegiatan' => $subKegiatanLabel,
            'tahun_anggaran' => (string) ($paket->dpa->tahunAnggaran->tahun ?? now()->year),
            'pagu_anggaran' => $paket->pagu_anggaran,
            'kode_rekening' => $paket->nomor_rekening ?: '—',
            'nama_pekerjaan' => $paket->nama_paket,
            'kode_rup' => $paket->kode_rup ?: '—',
            'tanggal_pelaksanaan' => $paket->masa_pelaksanaan ?: '—',
            'jenis_kontrak' => $paket->jenis_pengadaan ?: '',
            'tanggal_permohonan' => now()->toDateString(),
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public static function mergeDefaults(DpaPaketPekerjaan $paket, string $kode, array $input): array
    {
        return array_merge(self::defaultValues($paket, $kode), $input);
    }

    /**
     * @return array<string, list<string>>
     */
    public static function validationRules(string $kode): array
    {
        $rules = [];

        foreach (self::forKode($kode) as $field) {
            $fieldRules = [];

            if ($field['required'] ?? false) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            $fieldRules[] = match ($field['type']) {
                'date' => 'date',
                'number' => 'integer',
                'textarea' => 'string',
                default => 'string',
            };

            if ($field['type'] === 'text') {
                $fieldRules[] = 'max:255';
            }

            if ($field['type'] === 'number') {
                $fieldRules[] = 'min:0';
            }

            $rules['fields.'.$field['name']] = $fieldRules;
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public static function validationAttributes(string $kode): array
    {
        $attributes = [];

        foreach (self::forKode($kode) as $field) {
            $attributes['fields.'.$field['name']] = strtolower($field['label']);
        }

        return $attributes;
    }
}
