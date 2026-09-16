<?php

namespace Tests\Feature;

use App\Models\AlatSaranaOperasional;
use App\Models\Kelurahan;
use App\Models\Pemangkasan;
use App\Models\PemangkasanProgres;
use App\Models\PemangkasanProgresArmada;
use App\Models\PemeliharaanTaman;
use App\Models\PemeliharaanTamanArmada;
use App\Models\Taman;
use App\Models\User;
use Database\Seeders\TimPelaksanaSeeder;
use Database\Seeders\WilayahBatamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EvaluasiArmadaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(WilayahBatamSeeder::class);
        $this->seed(TimPelaksanaSeeder::class);
    }

    public function test_admin_can_view_armada_evaluasi_page(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $kelurahanId = Kelurahan::query()->value('id');

        $armada = AlatSaranaOperasional::create([
            'nama' => 'Dump Truck Evaluasi',
            'jenis' => 'Dump Truck',
            'no_plat' => 'BP 1111 EV',
            'sopir' => 'Misriwahyudi',
            'jumlah' => 1,
            'peruntukan' => PemeliharaanTaman::TIM_ARMADA,
            'kondisi' => 'Baik',
        ]);

        $taman = Taman::create([
            'nama_taman' => 'Taman Armada Evaluasi',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 1000,
            'alamat' => 'Alamat armada evaluasi',
            'deskripsi' => 'Deskripsi.',
        ]);

        $pemeliharaan = PemeliharaanTaman::create([
            'tanggal' => now()->format('Y-m-d').' 09:00:00',
            'tim' => PemeliharaanTaman::TIM_ARMADA,
            'taman_id' => $taman->id,
            'lokasi_pelaksanaan' => $taman->nama_taman,
            'jumlah_personil' => 4,
            'uraian_pekerjaan' => 'Angkut sampah',
        ]);

        PemeliharaanTamanArmada::create([
            'pemeliharaan_taman_id' => $pemeliharaan->id,
            'alat_sarana_operasional_id' => $armada->id,
            'jenis_armada' => 'Dump Truck',
            'no_plat' => 'BP 1111 EV',
            'sopir' => 'Darmani',
            'urutan' => 0,
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
            'pelaksana' => [PemeliharaanTaman::TIM_ARMADA],
            'status' => 'Diproses',
        ]);

        $progres = PemangkasanProgres::create([
            'pemangkasan_id' => $permohonan->id,
            'tanggal' => now()->format('Y-m-d').' 10:00:00',
            'hari_ke' => 1,
            'jumlah_personil' => 3,
            'catatan' => 'Progres armada evaluasi',
        ]);

        PemangkasanProgresArmada::create([
            'pemangkasan_progres_id' => $progres->id,
            'alat_sarana_operasional_id' => $armada->id,
            'jenis_armada' => 'Dump Truck',
            'no_plat' => 'BP 1111 EV',
            'sopir' => 'Hisar',
            'urutan' => 0,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.evaluasi.armada.index'))
            ->assertOk()
            ->assertSee('Evaluasi Utilisasi Armada')
            ->assertSee('Dump Truck Evaluasi')
            ->assertSee('Darmani')
            ->assertSee('Hisar');
    }

    public function test_admin_can_export_armada_evaluasi_pdf(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('PHP GD extension is required for PDF export.');
        }

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('admin.evaluasi.armada.export-pdf'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
