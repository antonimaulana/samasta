<?php

namespace Tests\Feature;

use App\Models\AlatSaranaOperasional;
use App\Models\User;
use Database\Seeders\TimPelaksanaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlatSaranaOperasionalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TimPelaksanaSeeder::class);
    }

    public function test_admin_can_manage_alat_sarana_operasional(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('admin.alat-sarana-operasionals.index'))
            ->assertOk()
            ->assertSee('Alat/Sarana Operasional');

        $this->actingAs($admin)
            ->post(route('admin.alat-sarana-operasionals.store'), [
                'nama' => 'Mesin Pemotong Rumput',
                'jenis' => 'Alat Mesin',
                'jumlah' => 3,
                'peruntukan' => 'Tim Wilayah 1',
                'kondisi' => 'Baik',
                'keterangan' => 'Disimpan di gudang tim 1',
            ])
            ->assertRedirect(route('admin.alat-sarana-operasionals.index'));

        $item = AlatSaranaOperasional::query()->where('nama', 'Mesin Pemotong Rumput')->first();
        $this->assertNotNull($item);

        $this->actingAs($admin)
            ->put(route('admin.alat-sarana-operasionals.update', $item), [
                'nama' => 'Mesin Pemotong Rumput',
                'jenis' => 'Alat Mesin',
                'jumlah' => 4,
                'peruntukan' => 'Tim Wilayah 1',
                'kondisi' => 'Rusak Ringan',
                'keterangan' => 'Satu unit perlu servis',
            ])
            ->assertRedirect(route('admin.alat-sarana-operasionals.index'));

        $this->assertDatabaseHas('alat_sarana_operasionals', [
            'id' => $item->id,
            'jumlah' => 4,
            'kondisi' => 'Rusak Ringan',
        ]);
    }

    public function test_viewer_can_view_index_but_cannot_create(): void
    {
        $viewer = User::factory()->create(['role' => User::ROLE_VIEWER]);

        $this->actingAs($viewer)
            ->get(route('admin.alat-sarana-operasionals.index'))
            ->assertOk();

        $this->actingAs($viewer)
            ->post(route('admin.alat-sarana-operasionals.store'), [
                'nama' => 'Test',
                'jenis' => 'Alat Manual',
                'jumlah' => 1,
                'peruntukan' => 'Tim Wilayah 1',
                'kondisi' => 'Baik',
            ])
            ->assertForbidden();
    }
}
