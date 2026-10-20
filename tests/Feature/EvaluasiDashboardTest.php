<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EvaluasiDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_unified_evaluasi_dashboard(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('admin.evaluasi.index'))
            ->assertOk()
            ->assertSee('Ringkasan evaluasi')
            ->assertSee('Pemeliharaan per Taman')
            ->assertSee('Kelengkapan Data');
    }

    public function test_evaluasi_dashboard_accepts_rentang_preset(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('admin.evaluasi.index', ['rentang' => '3bulan']))
            ->assertOk()
            ->assertSee('3 bulan terakhir');
    }
}
