<?php

namespace Tests\Feature;

use App\Models\TimPelaksana;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengawasRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengawas_can_view_but_not_create_taman(): void
    {
        $tim = TimPelaksana::create([
            'nama' => 'Tim Wilayah 1',
            'memiliki_wilayah_kerja' => true,
            'aktif' => true,
            'urutan' => 1,
        ]);

        $pengawas = User::factory()->create(['role' => User::ROLE_PENGAWAS]);
        $pengawas->timPelaksanas()->sync([$tim->id]);

        $this->actingAs($pengawas)
            ->get(route('admin.tamans.index'))
            ->assertOk();

        $this->actingAs($pengawas)
            ->get(route('admin.tamans.create'))
            ->assertForbidden();

        $this->actingAs($pengawas)
            ->get(route('lapangan.index'))
            ->assertOk();
    }

    public function test_pengawas_has_lapangan_input_permission(): void
    {
        $pengawas = User::factory()->create(['role' => User::ROLE_PENGAWAS]);

        $this->assertTrue($pengawas->canInputLapangan());
        $this->assertFalse($pengawas->canWrite());
    }

    public function test_pengawas_login_redirects_to_lapangan(): void
    {
        User::factory()->create([
            'role' => User::ROLE_PENGAWAS,
            'email' => 'pengawas-login@test.local',
        ]);

        $this->post(route('login'), [
            'email' => 'pengawas-login@test.local',
            'password' => 'password',
        ])->assertRedirect(route('lapangan.index'));
    }

    public function test_role_labels_match_organizational_names(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_OPERATOR]);

        $this->assertSame('Admin', $user->roleLabel());
        $this->assertSame('Pimpinan', User::ROLES[User::ROLE_VIEWER]);
        $this->assertSame('Pengawas', User::ROLES[User::ROLE_PENGAWAS]);
    }
}
