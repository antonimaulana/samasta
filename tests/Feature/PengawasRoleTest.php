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
    }

    public function test_role_labels_match_organizational_names(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_OPERATOR]);

        $this->assertSame('Admin', $user->roleLabel());
        $this->assertSame('Pimpinan', User::ROLES[User::ROLE_VIEWER]);
        $this->assertSame('Pengawas', User::ROLES[User::ROLE_PENGAWAS]);
    }
}
