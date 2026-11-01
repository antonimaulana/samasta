<?php

namespace Tests\Unit;

use App\Models\Pemangkasan;
use App\Models\PemangkasanProgres;
use App\Models\PemeliharaanTaman;
use App\Support\OperasionalKegiatanPdfFilename;
use PHPUnit\Framework\TestCase;

class OperasionalKegiatanPdfFilenameTest extends TestCase
{
    public function test_pemeliharaan_filename_ignores_uraian_and_uses_pemeliharaan_rutin(): void
    {
        $kinerja = new PemeliharaanTaman([
            'tim' => 'Tim Wilayah 1',
            'tanggal' => '2026-11-02',
            'uraian_pekerjaan' => 'Pembersihan sampah di lokasi',
            'persentase_progres' => 100,
        ]);

        $filename = OperasionalKegiatanPdfFilename::pemeliharaan($kinerja);

        $this->assertSame('wilayah 1_pemeliharaan rutin_2026-11-02_100%.pdf', $filename);
    }

    public function test_permohonan_progres_filename_uses_jenis_layanan_and_tim_tanpa_prefix_tim(): void
    {
        $permohonan = new Pemangkasan([
            'jenis_layanan' => 'Penanganan Pohon Tumbang',
            'pelaksana' => ['Tim Armada'],
            'total_hari' => 2,
        ]);

        $progres = new PemangkasanProgres([
            'tanggal' => '2026-11-02',
            'hari_ke' => 2,
        ]);

        $filename = OperasionalKegiatanPdfFilename::permohonanProgres($permohonan, $progres);

        $this->assertSame('armada_penanganan pohon tumbang_2026-11-02_100%.pdf', $filename);
    }

    public function test_permohonan_mini_garden_jenis_layanan(): void
    {
        $permohonan = new Pemangkasan([
            'jenis_layanan' => 'Pemasangan Mini Garden',
            'pelaksana' => ['Tim Wilayah 3'],
            'total_hari' => 1,
        ]);

        $progres = new PemangkasanProgres([
            'tanggal' => '2026-11-02',
            'hari_ke' => 1,
        ]);

        $filename = OperasionalKegiatanPdfFilename::permohonanProgres($permohonan, $progres);

        $this->assertSame('wilayah 3_pemasangan mini garden_2026-11-02_100%.pdf', $filename);
    }
}
