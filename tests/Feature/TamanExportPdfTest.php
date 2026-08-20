<?php

namespace Tests\Feature;

use App\Models\Kelurahan;
use App\Models\Taman;
use App\Models\User;
use Database\Seeders\WilayahBatamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TamanExportPdfTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(WilayahBatamSeeder::class);
    }

    public function test_export_pdf_groups_taman_by_kategori_order(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $kelurahanId = Kelurahan::query()->value('id');

        Taman::create([
            'nama_taman' => 'Taman Z Lingkungan',
            'kategori' => 'Taman Lingkungan',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 800,
            'alamat' => 'Alamat lingkungan',
            'deskripsi' => 'Deskripsi lingkungan',
        ]);
        Taman::create([
            'nama_taman' => 'Taman A Kota',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 1200,
            'alamat' => 'Alamat kota',
            'deskripsi' => 'Deskripsi kota',
        ]);

        $grouped = Taman::groupByKategori(Taman::all());

        $this->assertSame(
            ['Taman Kota', 'Taman Lingkungan'],
            $grouped->keys()->all(),
        );

        $this->actingAs($admin)
            ->get(route('admin.tamans.export-pdf'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_viewer_can_export_taman_pdf(): void
    {
        $viewer = User::factory()->create(['role' => User::ROLE_VIEWER]);

        $this->actingAs($viewer)
            ->get(route('admin.tamans.export-pdf'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
