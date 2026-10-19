<?php

namespace Tests\Feature;

use App\Models\Kelurahan;
use App\Models\TimPelaksana;
use App\Models\User;
use Database\Seeders\WilayahBatamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimKerjaManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(WilayahBatamSeeder::class);
    }

    public function test_admin_can_create_tim_kerja_with_wilayah_and_members(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $kelurahanIds = Kelurahan::query()->limit(2)->pluck('id')->all();

        $this->actingAs($admin)
            ->post(route('admin.tim-pelaksanas.store'), [
                'nama' => 'Tim Wilayah Baru',
                'nama_pengawas' => 'Asep Budi',
                'memiliki_wilayah_kerja' => '1',
                'kelurahan_ids' => $kelurahanIds,
                'members' => [
                    [
                        'nama' => 'Asep Budi',
                        'nip' => '197208172025211085',
                        'jabatan' => 'Pengelola Umum Operasional',
                    ],
                    [
                        'nama' => 'Hendri Kurniawan',
                        'nip' => '198707052025211110',
                        'jabatan' => 'Pengelola Umum Operasional',
                    ],
                ],
            ])
            ->assertRedirect(route('admin.tim-pelaksanas.index'));

        $team = TimPelaksana::query()->where('nama', 'Tim Wilayah Baru')->firstOrFail();

        $this->assertSame('Asep Budi', $team->nama_pengawas);
        $this->assertCount(count($kelurahanIds), $team->kelurahans);
        $this->assertDatabaseHas('petugas', [
            'tim_pelaksana_id' => $team->id,
            'nip' => '197208172025211085',
            'is_pengawas' => true,
        ]);
        $this->assertDatabaseHas('petugas', [
            'tim_pelaksana_id' => $team->id,
            'nip' => '198707052025211110',
        ]);
    }

    public function test_create_form_is_accessible(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('admin.tim-pelaksanas.create'))
            ->assertOk()
            ->assertSee('Tambah Tim Kerja')
            ->assertSee('Anggota Kerja');
    }
}
