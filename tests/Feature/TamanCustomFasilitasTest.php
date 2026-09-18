<?php

namespace Tests\Feature;

use App\Models\Taman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TamanCustomFasilitasTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_custom_fasilitas_name(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->post(route('admin.tamans.store'), [
                'nama_taman' => 'Taman Fasilitas Kustom',
                'kategori' => 'Taman Kota',
                'fasilitas_items' => [
                    [
                        'nama' => Taman::FASILITAS_CUSTOM_VALUE,
                        'nama_custom' => 'Skatepark',
                        'kondisi' => 'Baik',
                    ],
                ],
            ])
            ->assertRedirect(route('admin.tamans.index'));

        $taman = Taman::query()->where('nama_taman', 'Taman Fasilitas Kustom')->first();

        $this->assertNotNull($taman);
        $this->assertSame([
            ['nama' => 'Skatepark', 'kondisi' => 'Baik'],
        ], $taman->fasilitas_items);
    }
}
