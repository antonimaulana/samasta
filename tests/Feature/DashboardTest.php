<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_renders_executive_metrics(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Operasional')
            ->assertSee('Dinas Perumahan, Kawasan Permukiman dan Pertamanan')
            ->assertSee('Bidang Pertamanan dan Pemakaman')
            ->assertSee('Operasional Baik')
            ->assertSee('Unduh Ringkasan')
            ->assertSee('Data Taman')
            ->assertSee('Kelola taman →')
            ->assertSee('Data Bibit')
            ->assertSee('Kelola bibit →')
            ->assertSee('Data Armada')
            ->assertSee('Kelola armada')
            ->assertSee('Data Petugas')
            ->assertSee('Operasional Pertamanan')
            ->assertSee('Pemeliharaan Rutin')
            ->assertSee('Operasional Permohonan')
            ->assertSee('Catatan Operasional')
            ->assertSee('Kepuasan Masyarakat')
            ->assertSee('Masukan Masyarakat')
            ->assertDontSee('Analisis Detail')
            ->assertDontSee('Antrian Kerja Hari Ini');
    }

    public function test_viewer_dashboard_matches_admin_layout(): void
    {
        $viewer = User::factory()->create(['role' => User::ROLE_VIEWER]);

        $this->actingAs($viewer)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Operasional')
            ->assertSee('Dinas Perumahan, Kawasan Permukiman dan Pertamanan')
            ->assertSee('Operasional Pertamanan')
            ->assertSee('Unduh Ringkasan')
            ->assertSee('Evaluasi Operasional')
            ->assertSee(config('app.name'))
            ->assertDontSee('id="admin-sidebar-toggle"', false)
            ->assertDontSee('id="admin-desktop-sidebar"', false)
            ->assertDontSee('id="admin-mobile-menu-toggle"', false);

        $this->actingAs($viewer)
            ->get(route('admin.evaluasi.index'))
            ->assertOk()
            ->assertSee('← Dashboard', false);

        $this->actingAs($viewer)
            ->get(route('admin.tim-pelaksanas.index'))
            ->assertOk()
            ->assertSee('Daftar tim kerja Bidang Pertamanan dan Pemakaman.');
    }
}
