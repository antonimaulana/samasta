<?php

namespace Tests\Feature;

use App\Models\Kelurahan;
use App\Models\Taman;
use App\Models\TimPelaksana;
use App\Models\User;
use App\Support\TimPelaksanaResolver;
use Database\Seeders\TimPelaksanaSeeder;
use Database\Seeders\WilayahBatamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimPelaksanaWilayahTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(WilayahBatamSeeder::class);
        $this->seed(TimPelaksanaSeeder::class);
    }

    public function test_admin_can_view_tim_pelaksana_index(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('admin.tim-pelaksanas.index'))
            ->assertOk()
            ->assertSee('Tim Wilayah 1')
            ->assertSee('Maryono')
            ->assertSee('Munasir');
    }

    public function test_resolver_suggests_team_for_taman_kelurahan(): void
    {
        $kelurahan = Kelurahan::whereHas('kecamatan', fn ($q) => $q->where('nama', 'Batam Kota'))
            ->where('nama', 'Belian')
            ->first();

        $team = TimPelaksana::where('nama', 'Tim Wilayah 1')->first();
        $team->kelurahans()->sync([$kelurahan->id]);

        $taman = Taman::create([
            'nama_taman' => 'Taman Resolver Test',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahan->id,
            'luasan' => 1000,
            'alamat' => 'Alamat test',
            'deskripsi' => 'Deskripsi test taman resolver.',
        ]);

        $teams = app(TimPelaksanaResolver::class)->forTamanId($taman->id);

        $this->assertSame(['Tim Wilayah 1'], $teams);
    }

    public function test_suggest_endpoint_returns_teams(): void
    {
        $operator = User::factory()->create(['role' => User::ROLE_OPERATOR]);

        $team = TimPelaksana::where('nama', 'Tim Wilayah 2')->first();
        $kelurahan = $team->kelurahans()->first();

        $this->assertNotNull($kelurahan, 'Tim Wilayah 2 harus punya kelurahan dari seeder.');

        $taman = Taman::create([
            'nama_taman' => 'Taman Suggest API',
            'kategori' => 'Taman Lingkungan',
            'kelurahan_id' => $kelurahan->id,
            'luasan' => 800,
            'alamat' => 'Alamat suggest',
            'deskripsi' => 'Deskripsi taman suggest api test.',
        ]);

        $this->actingAs($operator)
            ->getJson(route('admin.tim-pelaksanas.suggest', ['taman_id' => $taman->id]))
            ->assertOk()
            ->assertJson([
                'teams' => ['Tim Wilayah 2'],
                'has_mapping' => true,
            ]);
    }

    public function test_admin_can_reassign_kelurahan_from_another_team(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $team1 = TimPelaksana::where('nama', 'Tim Wilayah 1')->first();
        $team2 = TimPelaksana::where('nama', 'Tim Wilayah 2')->first();
        $kelurahan = $team2->kelurahans()->first();

        $this->assertNotNull($kelurahan);

        $this->actingAs($admin)
            ->put(route('admin.tim-pelaksanas.wilayah.update', $team1), [
                'kelurahan_ids' => [$kelurahan->id],
            ])
            ->assertRedirect(route('admin.tim-pelaksanas.index'));

        $this->assertTrue($team1->fresh()->kelurahans->contains('id', $kelurahan->id));
        $this->assertFalse($team2->fresh()->kelurahans->contains('id', $kelurahan->id));
    }
}
