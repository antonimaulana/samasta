<?php

namespace Tests\Unit;

use App\Models\Kelurahan;
use App\Models\Pemangkasan;
use App\Models\PemangkasanProgres;
use App\Models\Taman;
use App\Support\PemangkasanSchedule;
use Database\Seeders\WilayahBatamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PemangkasanScheduleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WilayahBatamSeeder::class);
    }

    public function test_total_hari_counts_inclusive_date_range(): void
    {
        $this->assertSame(1, PemangkasanSchedule::totalHari('2026-08-28', '2026-08-28'));
        $this->assertSame(3, PemangkasanSchedule::totalHari('2026-08-28', '2026-08-30'));
    }

    public function test_hari_ke_is_one_based_from_schedule_start(): void
    {
        $this->assertSame(1, PemangkasanSchedule::hariKe('2026-08-28', '2026-08-28'));
        $this->assertSame(3, PemangkasanSchedule::hariKe('2026-08-28', '2026-08-30'));
    }

    public function test_recalculate_updates_progress_percentage_from_daily_entries(): void
    {
        $taman = Taman::create([
            'nama_taman' => 'Taman Jadwal Test',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => Kelurahan::query()->value('id'),
            'luasan' => 500,
            'alamat' => 'Alamat test',
            'deskripsi' => 'Deskripsi test.',
        ]);

        $permohonan = Pemangkasan::create([
            'jenis_layanan' => 'Pemangkasan Pohon',
            'taman_id' => $taman->id,
            'lokasi_pohon' => $taman->nama_taman,
            'asal' => 'Warga',
            'penanggungjawab' => 'Budi',
            'kontak_permohonan' => '081234567890',
            'tanggal_permohonan' => '2026-08-20',
            'kategori' => 'Laporan Masyarakat',
            'kondisi_sebelum' => '',
            'tanggal_eksekusi' => '2026-08-28',
            'tanggal_akhir_jadwal' => '2026-08-30',
            'total_hari' => 3,
            'pelaksana' => ['Tim Wilayah 1'],
            'status' => 'Diproses',
        ]);

        PemangkasanProgres::create([
            'pemangkasan_id' => $permohonan->id,
            'tanggal' => '2026-08-28',
            'hari_ke' => 1,
            'jumlah_personil' => 4,
        ]);

        PemangkasanProgres::create([
            'pemangkasan_id' => $permohonan->id,
            'tanggal' => '2026-08-29',
            'hari_ke' => 2,
            'jumlah_personil' => 5,
        ]);

        PemangkasanSchedule::recalculate($permohonan->fresh());

        $this->assertDatabaseHas('pemangkasans', [
            'id' => $permohonan->id,
            'total_hari' => 3,
            'hari_tercapai' => 2,
            'persentase_progres' => 67,
        ]);
    }

    public function test_tanggal_within_schedule_respects_end_date(): void
    {
        $taman = Taman::create([
            'nama_taman' => 'Taman Jadwal Test',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => Kelurahan::query()->value('id'),
            'luasan' => 500,
            'alamat' => 'Alamat test',
            'deskripsi' => 'Deskripsi test.',
        ]);

        $permohonan = Pemangkasan::create([
            'jenis_layanan' => 'Pemangkasan Pohon',
            'taman_id' => $taman->id,
            'lokasi_pohon' => $taman->nama_taman,
            'asal' => 'Warga',
            'penanggungjawab' => 'Budi',
            'kontak_permohonan' => '081234567890',
            'tanggal_permohonan' => '2026-08-20',
            'kategori' => 'Laporan Masyarakat',
            'kondisi_sebelum' => '',
            'tanggal_eksekusi' => '2026-08-28',
            'tanggal_akhir_jadwal' => '2026-08-30',
            'total_hari' => 3,
            'pelaksana' => ['Tim Wilayah 1'],
            'status' => 'Diproses',
        ]);

        $this->assertTrue(PemangkasanSchedule::tanggalWithinSchedule($permohonan, '2026-08-29'));
        $this->assertFalse(PemangkasanSchedule::tanggalWithinSchedule($permohonan, '2026-08-31'));
    }
}
