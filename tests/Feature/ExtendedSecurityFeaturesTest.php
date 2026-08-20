<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\AduanMasyarakat;
use App\Models\Taman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExtendedSecurityFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_check_aduan_status_by_nomor(): void
    {
        $aduan = AduanMasyarakat::create([
            'nomor_aduan' => 'ADU-20260717-0001',
            'lokasi' => 'Taman Test',
            'jenis_aduan' => 'Kondisi Taman Rusak',
            'deskripsi' => 'Deskripsi aduan yang cukup panjang untuk validasi.',
            'nama_pelapor' => 'Pelapor',
            'kontak_pelapor' => '081234567890',
            'status' => 'Diproses',
            'catatan_admin' => 'Tim sedang menuju lokasi.',
        ]);

        $this->post(route('aduan.check.submit'), [
            'nomor_aduan' => $aduan->nomor_aduan,
            'kontak_verifikasi' => '7890',
        ])
            ->assertOk()
            ->assertSee('Diproses')
            ->assertSee('Tim sedang menuju lokasi.')
            ->assertDontSee('Pelapor');
    }

    public function test_check_status_hides_not_found_details(): void
    {
        $this->post(route('aduan.check.submit'), [
            'nomor_aduan' => 'ADU-20260717-9999',
            'kontak_verifikasi' => '1234',
        ])
            ->assertOk()
            ->assertSee('tidak ditemukan');
    }

    public function test_admin_mutation_creates_activity_log(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->post(route('admin.tamans.store'), [
                'nama_taman' => 'Taman Log Test',
                'kategori' => 'Taman Kota',
                'luasan' => 500,
                'alamat' => 'Alamat log test',
                'deskripsi' => 'Deskripsi taman untuk log aktivitas.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'action' => 'admin.tamans.store',
            'method' => 'POST',
        ]);
    }

    public function test_taman_policy_blocks_viewer_delete(): void
    {
        $viewer = User::factory()->create(['role' => User::ROLE_VIEWER]);
        $taman = Taman::create([
            'nama_taman' => 'Taman Policy Test',
            'kategori' => 'Taman Kota',
            'luasan' => 1000,
            'alamat' => 'Alamat test',
            'deskripsi' => 'Deskripsi test',
        ]);

        $this->actingAs($viewer)
            ->delete(route('admin.tamans.destroy', $taman))
            ->assertForbidden();
    }
}
