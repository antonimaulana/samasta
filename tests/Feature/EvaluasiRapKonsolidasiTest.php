<?php

namespace Tests\Feature;

use App\Models\Kelurahan;
use App\Models\Taman;
use App\Models\User;
use Database\Seeders\TimPelaksanaSeeder;
use Database\Seeders\WilayahBatamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EvaluasiRapKonsolidasiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['simtaman.features.rap_konsolidasi' => true]);

        $this->seed(WilayahBatamSeeder::class);
        $this->seed(TimPelaksanaSeeder::class);
    }

    public function test_rap_konsolidasi_returns_404_when_feature_disabled(): void
    {
        config(['simtaman.features.rap_konsolidasi' => false]);

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('admin.evaluasi.rap-konsolidasi.index'))
            ->assertNotFound();
    }

    public function test_admin_can_view_rap_konsolidasi_page(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $kelurahanId = Kelurahan::query()->value('id');

        Taman::create([
            'nama_taman' => 'Taman RAP Konsolidasi',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 1000,
            'alamat' => 'Alamat rap',
            'deskripsi' => 'Deskripsi rap.',
            'data_verified_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.evaluasi.rap-konsolidasi.index'))
            ->assertOk()
            ->assertSee('Laporan RAP Konsolidasi')
            ->assertSee('Indeks Kinerja RAP')
            ->assertSee('Basis Data RTH')
            ->assertSee('Monitoring Operasional')
            ->assertSee('Partisipasi Masyarakat')
            ->assertSee('Prioritas Tindak Lanjut');
    }

    public function test_admin_can_export_rap_konsolidasi_pdf(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('PHP GD extension is required for PDF export.');
        }

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('admin.evaluasi.rap-konsolidasi.export-pdf'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
