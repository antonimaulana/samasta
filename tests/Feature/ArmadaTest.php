<?php

namespace Tests\Feature;

use App\Models\Armada;
use App\Models\PemeliharaanTaman;
use App\Models\User;
use Database\Seeders\TimPelaksanaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArmadaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TimPelaksanaSeeder::class);
    }

    public function test_admin_can_manage_armada(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('admin.armadas.index'))
            ->assertOk()
            ->assertSee('Armada')
            ->assertSee('Riwayat Penggunaan Armada Terbaru');

        $this->actingAs($admin)
            ->post(route('admin.armadas.store'), [
                'nama' => 'Dump Truck Unit 1',
                'jenis' => 'Dump Truck',
                'no_plat' => 'BP 1234 AB',
                'jumlah' => 1,
                'kondisi' => 'Baik',
                'keterangan' => 'Parkir gudang tim armada',
            ])
            ->assertRedirect(route('admin.armadas.index'));

        $item = Armada::query()->where('nama', 'Dump Truck Unit 1')->first();
        $this->assertNotNull($item);
        $this->assertSame(PemeliharaanTaman::TIM_ARMADA, $item->peruntukan);

        $this->actingAs($admin)
            ->put(route('admin.armadas.update', $item), [
                'nama' => 'Dump Truck Unit 1',
                'jenis' => 'Dump Truck',
                'no_plat' => 'BP 1234 AB',
                'jumlah' => 1,
                'kondisi' => 'Rusak Ringan',
                'keterangan' => 'Perlu servis ringan',
            ])
            ->assertRedirect(route('admin.armadas.index'));

        $this->assertDatabaseHas('armadas', [
            'id' => $item->id,
            'kondisi' => 'Rusak Ringan',
            'peruntukan' => PemeliharaanTaman::TIM_ARMADA,
        ]);
    }

    public function test_store_rejects_non_armada_jenis(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->post(route('admin.armadas.store'), [
                'nama' => 'Mesin Pemotong',
                'jenis' => 'Alat Mesin',
                'no_plat' => 'BP 9999 XX',
                'jumlah' => 1,
                'kondisi' => 'Baik',
            ])
            ->assertSessionHasErrors('jenis');
    }

    public function test_viewer_can_view_index_but_cannot_create(): void
    {
        $viewer = User::factory()->create(['role' => User::ROLE_VIEWER]);

        $this->actingAs($viewer)
            ->get(route('admin.armadas.index'))
            ->assertOk();

        $this->actingAs($viewer)
            ->post(route('admin.armadas.store'), [
                'nama' => 'Test',
                'jenis' => 'Dump Truck',
                'no_plat' => 'BP 0000 AA',
                'jumlah' => 1,
                'kondisi' => 'Baik',
            ])
            ->assertForbidden();
    }

    public function test_legacy_alat_sarana_url_redirects_to_armadas(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get('/admin/alat-sarana-operasionals')
            ->assertRedirect(route('admin.armadas.index'));
    }
}
