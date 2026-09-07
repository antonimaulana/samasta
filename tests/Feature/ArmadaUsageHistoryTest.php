<?php

namespace Tests\Feature;

use App\Models\AlatSaranaOperasional;
use App\Models\Kelurahan;
use App\Models\Pemangkasan;
use App\Models\PemangkasanProgres;
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

        $progres = PemangkasanProgres::create([
            'pemangkasan_id' => $permohonan->id,
            'tanggal' => '2026-08-25',
            'hari_ke' => 1,
            'jumlah_personil' => 5,
            'catatan' => 'Pemangkasan selesai.',
        ]);

        $progres->armadas()->create([
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

    public function test_admin_permohonan_store_ignores_armada_input(): void
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

        $this->assertDatabaseCount('pemangkasan_progres_armadas', 0);
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

        $this->assertDatabaseCount('pemangkasan_progres_armadas', 0);
    }

    public function test_lapangan_progress_saves_armada_per_entry_and_next_form_starts_empty(): void
    {
        $this->seed(TimPelaksanaSeeder::class);

        $armada = AlatSaranaOperasional::create([
            'nama' => 'Dump Truck Gamma',
            'jenis' => 'Dump Truck',
            'no_plat' => 'BP 9999 XY',
            'sopir' => 'Misriwahyudi',
            'jumlah' => 1,
            'peruntukan' => PemeliharaanTaman::TIM_ARMADA,
            'kondisi' => 'Baik',
        ]);

        $kelurahanId = Kelurahan::query()->value('id');
        $taman = Taman::create([
            'nama_taman' => 'Taman Armada Progres',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 1000,
            'alamat' => 'Alamat',
            'deskripsi' => 'Deskripsi.',
        ]);

        $permohonan = Pemangkasan::create([
            'jenis_layanan' => 'Pemangkasan Pohon',
            'taman_id' => $taman->id,
            'lokasi_pohon' => $taman->nama_taman,
            'asal' => 'Warga',
            'penanggungjawab' => 'Andi',
            'kontak_permohonan' => '081234567890',
            'tanggal_permohonan' => '2026-08-20',
            'kategori' => 'Laporan Masyarakat',
            'kondisi_sebelum' => '',
            'tanggal_eksekusi' => '2026-08-28',
            'tanggal_akhir_jadwal' => '2026-08-29',
            'pelaksana' => [PemeliharaanTaman::TIM_ARMADA],
            'status' => 'Rencana',
        ]);

        $this->post(route('lapangan.unlock.store'), ['pin' => '1234'])
            ->assertRedirect(route('lapangan.index'));

        $fotoPayload = [];
        foreach (array_keys(PemeliharaanTaman::FOTO_FIELDS) as $field) {
            $fotoPayload[$field] = \Illuminate\Http\UploadedFile::fake()->image($field.'.jpg');
        }

        $this->post(route('lapangan.permohonan.update', $permohonan), array_merge([
            'status' => 'Diproses',
            'tanggal_progres' => '2026-08-28T08:00',
            'jumlah_personil' => 4,
            'armada' => [
                ['alat_sarana_operasional_id' => $armada->id, 'sopir' => 'Darmani'],
            ],
        ], $fotoPayload))->assertRedirect();

        $entry = PemangkasanProgres::query()->where('pemangkasan_id', $permohonan->id)->first();
        $this->assertNotNull($entry);
        $this->assertDatabaseHas('pemangkasan_progres_armadas', [
            'pemangkasan_progres_id' => $entry->id,
            'alat_sarana_operasional_id' => $armada->id,
            'sopir' => 'Darmani',
        ]);

        $this->get(route('lapangan.permohonan.edit', $permohonan))
            ->assertOk()
            ->assertDontSee('value="'.$armada->id.'" selected', false);

        $this->post(route('lapangan.permohonan.update', $permohonan), array_merge([
            'status' => 'Diproses',
            'tanggal_progres' => '2026-08-29T09:00',
            'jumlah_personil' => 3,
        ], $fotoPayload))->assertRedirect();

        $this->assertDatabaseCount('pemangkasan_progres_armadas', 1);
        $this->assertDatabaseHas('pemangkasan_progres', [
            'pemangkasan_id' => $permohonan->id,
            'jumlah_personil' => 3,
        ]);
    }
}
