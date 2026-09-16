<?php

namespace Tests\Feature;

use App\Models\Kelurahan;
use App\Models\Pemangkasan;
use App\Models\PemangkasanProgres;
use App\Models\Taman;
use App\Models\User;
use Database\Seeders\TimPelaksanaSeeder;
use Database\Seeders\WilayahBatamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EvaluasiOperasionalPermohonanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(WilayahBatamSeeder::class);
        $this->seed(TimPelaksanaSeeder::class);
    }

    public function test_admin_can_view_operasional_permohonan_evaluasi_page(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $kelurahanId = Kelurahan::query()->value('id');
        $timWilayah = \App\Models\PemeliharaanTaman::timNames()[0];

        $taman = Taman::create([
            'nama_taman' => 'Taman Evaluasi Permohonan',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 1000,
            'alamat' => 'Alamat evaluasi permohonan',
            'deskripsi' => 'Deskripsi evaluasi.',
        ]);

        $permohonan = Pemangkasan::create([
            'jenis_layanan' => 'Pemangkasan Pohon',
            'taman_id' => $taman->id,
            'lokasi_pohon' => $taman->nama_taman,
            'asal' => 'Warga',
            'penanggungjawab' => 'Budi',
            'kontak_permohonan' => '081234567890',
            'tanggal_permohonan' => now()->subDays(3)->toDateString(),
            'kategori' => 'Laporan Masyarakat',
            'kondisi_sebelum' => '',
            'foto_sebelum' => 'permohonan/foto-sebelum.jpg',
            'tanggal_eksekusi' => now()->subDays(2)->toDateString(),
            'tanggal_akhir_jadwal' => now()->toDateString(),
            'pelaksana' => [$timWilayah],
            'status' => 'Selesai',
            'tanggal_penyelesaian' => now()->toDateString(),
            'foto_sesudah' => 'permohonan/foto-sesudah.jpg',
        ]);

        PemangkasanProgres::create([
            'pemangkasan_id' => $permohonan->id,
            'tanggal' => now()->format('Y-m-d').' 09:00:00',
            'hari_ke' => 1,
            'jumlah_personil' => 4,
            'foto_saat_1' => 'progres/foto-saat.jpg',
            'catatan' => 'Progres evaluasi permohonan',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.evaluasi.operasional-permohonan.index'))
            ->assertOk()
            ->assertSee('Evaluasi Operasional Permohonan')
            ->assertSee('Pemangkasan Pohon')
            ->assertSee('Rekap per Jenis Layanan')
            ->assertSee('Taman Evaluasi Permohonan');
    }

    public function test_admin_can_export_operasional_permohonan_evaluasi_pdf(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('PHP GD extension is required for PDF export.');
        }

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('admin.evaluasi.operasional-permohonan.export-pdf'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
