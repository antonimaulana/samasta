<?php

namespace Tests\Feature;

use App\Models\Kelurahan;
use App\Models\Pemangkasan;
use App\Models\PemangkasanProgres;
use App\Models\PemeliharaanTaman;
use App\Models\SurveyKepuasan;
use App\Models\Taman;
use App\Models\User;
use Database\Seeders\TimPelaksanaSeeder;
use Database\Seeders\WilayahBatamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EvaluasiKinerjaTimTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(WilayahBatamSeeder::class);
        $this->seed(TimPelaksanaSeeder::class);
    }

    public function test_admin_can_view_kinerja_tim_evaluasi_page(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $kelurahanId = Kelurahan::query()->value('id');
        $timWilayah = PemeliharaanTaman::timNames()[0];

        $taman = Taman::create([
            'nama_taman' => 'Taman Kinerja Tim Evaluasi',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 1000,
            'alamat' => 'Alamat evaluasi kinerja tim',
            'deskripsi' => 'Deskripsi evaluasi.',
        ]);

        PemeliharaanTaman::create([
            'tanggal' => now()->format('Y-m-d').' 08:00:00',
            'tim' => $timWilayah,
            'taman_id' => $taman->id,
            'lokasi_pelaksanaan' => $taman->nama_taman,
            'jumlah_personil' => 6,
            'uraian_pekerjaan' => 'Pembersihan rutin',
        ]);

        $permohonan = Pemangkasan::create([
            'jenis_layanan' => 'Pemangkasan Pohon',
            'taman_id' => $taman->id,
            'lokasi_pohon' => $taman->nama_taman,
            'asal' => 'Warga',
            'penanggungjawab' => 'Budi',
            'kontak_permohonan' => '081234567890',
            'tanggal_permohonan' => now()->toDateString(),
            'kategori' => 'Laporan Masyarakat',
            'kondisi_sebelum' => '',
            'tanggal_eksekusi' => now()->toDateString(),
            'tanggal_akhir_jadwal' => now()->toDateString(),
            'pelaksana' => [$timWilayah],
            'status' => 'Selesai',
            'tanggal_penyelesaian' => now()->toDateString(),
        ]);

        PemangkasanProgres::create([
            'pemangkasan_id' => $permohonan->id,
            'tanggal' => now()->format('Y-m-d').' 09:00:00',
            'hari_ke' => 1,
            'jumlah_personil' => 4,
            'catatan' => 'Progres evaluasi kinerja tim',
        ]);

        SurveyKepuasan::create([
            'kategori' => 'Operasional Pertamanan',
            'rating' => 5,
            'taman_id' => $taman->id,
            'saran' => 'Sangat baik',
            'nama' => 'Warga Evaluasi',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.evaluasi.kinerja-tim.index'))
            ->assertOk()
            ->assertSee('Evaluasi Kinerja Tim')
            ->assertSee($timWilayah)
            ->assertSee('Rekapitulasi Kinerja per Tim');
    }

    public function test_admin_can_export_kinerja_tim_evaluasi_pdf(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('PHP GD extension is required for PDF export.');
        }

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('admin.evaluasi.kinerja-tim.export-pdf'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
