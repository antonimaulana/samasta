<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewer_cannot_open_create_form(): void
    {
        $viewer = User::factory()->create(['role' => User::ROLE_VIEWER]);

        $this->actingAs($viewer)
            ->get(route('admin.tamans.create'))
            ->assertForbidden();
    }

    public function test_viewer_cannot_create_taman(): void
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

    public function test_operator_can_create_taman_but_not_delete(): void
    {
        $operator = User::factory()->operatorTeams([], allWilayah: true)->create();

        $this->actingAs($operator)
            ->post(route('admin.tamans.store'), [
                'nama_taman' => 'Taman Operator',
                'kategori' => 'Taman Kota',
                'luasan' => 1000,
                'alamat' => 'Alamat operator',
                'deskripsi' => 'Deskripsi taman operator',
            ])
            ->assertRedirect(route('admin.tamans.index'));

        $tamanId = \App\Models\Taman::query()->value('id');
        $this->assertNotNull($tamanId);

        $this->actingAs($operator)
            ->delete(route('admin.tamans.destroy', $tamanId))
            ->assertForbidden();
    }

    public function test_aduan_success_requires_session(): void
    {
        $this->get(route('aduan.success'))
            ->assertRedirect(route('aduan.create'));
    }

    public function test_operator_can_access_ensiklopedia_but_not_sistem_or_dpa(): void
    {
        $operator = User::factory()->operatorTeams([], allWilayah: true)->create();

        $this->actingAs($operator)
            ->get(route('admin.ensiklopedia-kategoris.index'))
            ->assertOk();

        $this->actingAs($operator)
            ->get(route('admin.users.index'))
            ->assertForbidden();

        $this->actingAs($operator)
            ->get(route('admin.tim-pelaksanas.index'))
            ->assertForbidden();

        $this->actingAs($operator)
            ->get(route('admin.dpa.dashboard'))
            ->assertForbidden();
    }

    public function test_operator_cannot_bulk_import_bibit_or_taman(): void
    {
        $operator = User::factory()->operatorTeams([], allWilayah: true)->create();

        $this->actingAs($operator)
            ->get(route('admin.bibits.import'))
            ->assertForbidden();

        $this->actingAs($operator)
            ->get(route('admin.tamans.import'))
            ->assertForbidden();
    }

    public function test_operator_admin_nav_shows_ensiklopedia_not_sistem(): void
    {
        $operator = User::factory()->operatorTeams([], allWilayah: true)->create();

        $this->actingAs($operator)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Ensiklopedia')
            ->assertDontSee('Kelola Pengguna')
            ->assertDontSee('Monitoring DPA');
    }
}
