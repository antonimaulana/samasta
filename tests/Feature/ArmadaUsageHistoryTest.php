<?php

namespace Tests\Feature;

use App\Models\AlatSaranaOperasional;
use App\Models\Kelurahan;
use App\Models\Pemangkasan;
use App\Models\PemeliharaanTaman;
use App\Models\PemeliharaanTamanArmada;
use App\Models\Taman;
use App\Models\User;
use Database\Seeders\TimPelaksanaSeeder;
use Database\Seeders\WilayahBatamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArmadaUsageHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(WilayahBatamSeeder::class);
        $this->seed(TimPelaksanaSeeder::class);
    }

    public function test_armada_show_page_displays_usage_from_pemeliharaan_and_permohonan(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $kelurahanId = Kelurahan::query()->value('id');

        $armada = AlatSaranaOperasional::create([
            'nama' => 'Dump Truck Alpha',
            'jenis' => 'Dump Truck',
            'no_plat' => 'BP 1234 AB',
            'sopir' => 'Misriwahyudi',
            'jumlah' => 1,
            'peruntukan' => PemeliharaanTaman::TIM_ARMADA,
            'kondisi' => 'Baik',
        ]);

        $taman = Taman::create([
            'nama_taman' => 'Taman Riwayat Armada',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 1000,
            'alamat' => 'Alamat taman',
            'deskripsi' => 'Deskripsi.',
        ]);

        $pemeliharaan = PemeliharaanTaman::create([
            'tanggal' => '2026-08-20',
            'tim' => PemeliharaanTaman::TIM_ARMADA,
            'taman_id' => $taman->id,
            'lokasi_pelaksanaan' => $taman->nama_taman,
            'uraian_pekerjaan' => 'Pembersihan sampah',
        ]);

        PemeliharaanTamanArmada::create([
            'pemeliharaan_taman_id' => $pemeliharaan->id,
            'alat_sarana_operasional_id' => $armada->id,
            'jenis_armada' => 'Dump Truck',
            'no_plat' => 'BP 1234 AB',
            'sopir' => 'Darmani',
            'urutan' => 0,
        ]);

        $permohonan = Pemangkasan::create([
            'jenis_layanan' => 'Pemangkasan Pohon',
            'taman_id' => $taman->id,
            'lokasi_pohon' => $taman->nama_taman,
            'asal' => 'Dinas PU',
            'penanggungjawab' => 'Budi',
            'kontak_permohonan' => '081234567890',
            'tanggal_permohonan' => '2026-08-18',
            'kategori' => 'Instruksi',
            'kondisi_sebelum' => '',
            'tanggal_eksekusi' => '2026-08-25',
            'pelaksana' => [PemeliharaanTaman::TIM_ARMADA],
            'status' => 'Selesai',
        ]);

        $permohonan->armadas()->create([
            'alat_sarana_operasional_id' => $armada->id,
            'jenis_armada' => 'Dump Truck',
            'no_plat' => 'BP 1234 AB',
            'sopir' => 'Hisar',
            'urutan' => 0,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.alat-sarana-operasionals.show', $armada))
            ->assertOk()
            ->assertSee('Riwayat Penggunaan Armada')
            ->assertSee('Pemeliharaan Rutin')
            ->assertSee('Permohonan')
            ->assertSee('Pembersihan sampah')
            ->assertSee('Pemangkasan Pohon')
            ->assertSee('Darmani')
            ->assertSee('Hisar');
    }

    public function test_permohonan_with_tim_armada_stores_armada_usage(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $kelurahanId = Kelurahan::query()->value('id');

        $armada = AlatSaranaOperasional::create([
            'nama' => 'Crane Beta',
            'jenis' => 'Crane',
            'no_plat' => 'BP 5678 CD',
            'sopir' => 'Darmani',
            'jumlah' => 1,
            'peruntukan' => PemeliharaanTaman::TIM_ARMADA,
            'kondisi' => 'Baik',
        ]);

        $taman = Taman::create([
            'nama_taman' => 'Taman Permohonan Armada',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 1000,
            'alamat' => 'Alamat',
            'deskripsi' => 'Deskripsi.',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.pemangkasans.store'), [
                'jenis_layanan' => 'Penanganan Pohon Tumbang',
                'taman_id' => $taman->id,
                'asal' => 'Warga',
                'penanggungjawab' => 'Andi',
                'kontak_permohonan' => '081234567890',
                'tanggal_permohonan' => '2026-08-20',
                'kategori' => 'Laporan Masyarakat',
                'tanggal_eksekusi' => '2026-08-27',
                'tanggal_akhir_jadwal' => '2026-08-29',
                'pelaksana' => [PemeliharaanTaman::TIM_ARMADA],
                'status' => 'Rencana',
                'armada' => [
                    ['alat_sarana_operasional_id' => $armada->id, 'sopir' => 'Zazid'],
                ],
            ])
            ->assertRedirect(route('admin.pemangkasans.index'));

        $this->assertDatabaseHas('pemangkasan_armadas', [
            'alat_sarana_operasional_id' => $armada->id,
            'sopir' => 'Zazid',
        ]);
    }

    public function test_permohonan_with_tim_armada_can_be_saved_without_armada_rows(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $kelurahanId = Kelurahan::query()->value('id');

        $taman = Taman::create([
            'nama_taman' => 'Taman Tanpa Armada Wajib',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 1000,
            'alamat' => 'Alamat',
            'deskripsi' => 'Deskripsi.',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.pemangkasans.store'), [
                'jenis_layanan' => 'Penanganan Pohon Tumbang',
                'taman_id' => $taman->id,
                'asal' => 'Warga',
                'penanggungjawab' => 'Andi',
                'kontak_permohonan' => '081234567890',
                'tanggal_permohonan' => '2026-08-20',
                'kategori' => 'Laporan Masyarakat',
                'tanggal_eksekusi' => '2026-08-27',
                'tanggal_akhir_jadwal' => '2026-08-29',
                'pelaksana' => [PemeliharaanTaman::TIM_ARMADA],
                'status' => 'Rencana',
            ])
            ->assertRedirect(route('admin.pemangkasans.index'));

        $this->assertDatabaseHas('pemangkasans', [
            'taman_id' => $taman->id,
            'asal' => 'Warga',
        ]);

        $this->assertDatabaseCount('pemangkasan_armadas', 0);
    }
}
