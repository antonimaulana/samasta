<?php

namespace Tests\Feature;

use App\Models\Pejabat;
use App\Models\User;
use App\Support\KontenBerandaCache;
use App\Support\PemerintahKotaBatam;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KontenBerandaTest extends TestCase
{
    use RefreshDatabase;

    public function test_pejabat_cms_data_is_used_on_homepage(): void
    {
        $this->seed(\Database\Seeders\KontenBerandaSeeder::class);

        Pejabat::query()->where('nama', 'H. Amsakar Ahmad')->update([
            'jabatan' => 'Walikota Batam (CMS)',
        ]);

        KontenBerandaCache::forgetAll();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Walikota Batam (CMS)');
    }

    public function test_admin_can_open_pejabat_index(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('admin.pejabats.index'))
            ->assertOk()
            ->assertSee('Pejabat Beranda');
    }

    public function test_static_fallback_when_no_pejabat_records(): void
    {
        $this->assertNotEmpty(PemerintahKotaBatam::pimpinan());
    }
}
