<?php

namespace Tests\Feature;

use App\Models\Kelurahan;
use App\Models\Pemangkasan;
use App\Models\PemangkasanProgres;
use App\Models\Taman;
use App\Models\User;
use App\Support\OperasionalPelaksanaanTime;
use Database\Seeders\WilayahBatamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PemangkasanProgresAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WilayahBatamSeeder::class);
    }

    public function test_admin_can_open_permohonan_detail_page(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $permohonan = $this->createPermohonan();

        PemangkasanProgres::create([
            'pemangkasan_id' => $permohonan->id,
            'tanggal' => OperasionalPelaksanaanTime::normalizeInput(now()->format('Y-m-d H:i:s')),
            'hari_ke' => 1,
            'jumlah_personil' => 2,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.pemangkasans.show', $permohonan))
            ->assertOk()
            ->assertSee('Cetak PDF', false)
            ->assertSee('Progres Harian', false);
    }

    public function test_admin_can_delete_daily_progress_entry(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $permohonan = $this->createPermohonan();

        $entry = PemangkasanProgres::create([
            'pemangkasan_id' => $permohonan->id,
            'tanggal' => OperasionalPelaksanaanTime::normalizeInput(now()->format('Y-m-d H:i:s')),
            'hari_ke' => 1,
            'jumlah_personil' => 2,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.pemangkasans.progres.destroy', [
                'pemangkasan' => $permohonan,
                'pemangkasanProgres' => $entry,
            ]))
            ->assertRedirect(route('admin.pemangkasans.show', $permohonan))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('pemangkasan_progres', ['id' => $entry->id]);
        $this->assertDatabaseHas('pemangkasans', [
            'id' => $permohonan->id,
            'status' => 'Rencana',
        ]);
    }

    public function test_operator_cannot_delete_daily_progress_entry(): void
    {
        $operator = User::factory()->create(['role' => User::ROLE_OPERATOR]);
        $permohonan = $this->createPermohonan();

        $entry = PemangkasanProgres::create([
            'pemangkasan_id' => $permohonan->id,
            'tanggal' => OperasionalPelaksanaanTime::normalizeInput(now()->format('Y-m-d H:i:s')),
            'hari_ke' => 1,
            'jumlah_personil' => 2,
        ]);

        $this->actingAs($operator)
            ->delete(route('admin.pemangkasans.progres.destroy', [
                'pemangkasan' => $permohonan,
                'pemangkasanProgres' => $entry,
            ]))
            ->assertForbidden();

        $this->assertDatabaseHas('pemangkasan_progres', ['id' => $entry->id]);
    }

    public function test_index_shows_schedule_adjustment_labels(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $permohonan = $this->createPermohonan([
            'tanggal_eksekusi' => now()->addDay()->toDateString(),
            'tanggal_akhir_jadwal' => now()->addDays(3)->toDateString(),
            'tanggal_akhir_jadwal_rencana' => now()->addDays(3)->toDateString(),
            'status' => 'Diproses',
        ]);

        PemangkasanProgres::create([
            'pemangkasan_id' => $permohonan->id,
            'tanggal' => OperasionalPelaksanaanTime::normalizeInput(now()->format('Y-m-d H:i:s')),
            'hari_ke' => 1,
            'jumlah_personil' => 2,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.pemangkasans.index'))
            ->assertOk()
            ->assertSee('Mulai lebih awal');
    }

    private function createPermohonan(array $overrides = []): Pemangkasan
    {
        $taman = Taman::create([
            'nama_taman' => 'Taman Progres Admin Test',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => Kelurahan::query()->value('id'),
            'luasan' => 500,
            'alamat' => 'Alamat test',
            'deskripsi' => 'Deskripsi test progres admin.',
        ]);

        return Pemangkasan::create(array_merge([
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
            'tanggal_akhir_jadwal_rencana' => now()->toDateString(),
            'total_hari' => 1,
            'pelaksana' => ['Tim Wilayah 1'],
            'status' => 'Diproses',
        ], $overrides));
    }
}
