<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewerScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewer_can_access_dashboard(): void
    {
        $viewer = User::factory()->create(['role' => User::ROLE_VIEWER]);

        $this->actingAs($viewer)
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_viewer_can_access_pemangkasan_index(): void
    {
        $viewer = User::factory()->create(['role' => User::ROLE_VIEWER]);

        $this->actingAs($viewer)
            ->get(route('admin.pemangkasans.index'))
            ->assertOk();
    }

    public function test_viewer_is_redirected_from_bibit_masuk(): void
    {
        $viewer = User::factory()->create(['role' => User::ROLE_VIEWER]);

        $this->actingAs($viewer)
            ->get(route('admin.bibit-masuks.index'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_viewer_is_redirected_from_user_management(): void
    {
        $viewer = User::factory()->create(['role' => User::ROLE_VIEWER]);

        $this->actingAs($viewer)
            ->get(route('admin.users.index'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_viewer_cannot_post_to_admin_routes(): void
    {
        $viewer = User::factory()->create(['role' => User::ROLE_VIEWER]);

        $this->actingAs($viewer)
            ->post(route('admin.tamans.store'), [
                'nama_taman' => 'Taman Test',
                'kategori' => 'Taman Kota',
                'luasan' => 1000,
                'alamat' => 'Alamat test',
                'deskripsi' => 'Deskripsi test',
            ])
            ->assertForbidden();
    }
}
