<?php

namespace Tests\Feature;

use App\Models\Kelurahan;
use App\Models\PemeliharaanTaman;
use App\Models\Petugas;
use App\Models\Taman;
use App\Models\TimPelaksana;
use App\Models\User;
use Database\Seeders\TimPelaksanaSeeder;
use Database\Seeders\WilayahBatamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PetugasPelaksanaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(WilayahBatamSeeder::class);
        $this->seed(TimPelaksanaSeeder::class);
    }

    public function test_admin_can_manage_petugas_for_team(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $team = TimPelaksana::query()->where('nama', 'Tim Wilayah 1')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.tim-pelaksanas.store'), [
                'nama' => 'Tim Uji Petugas',
                'nama_pengawas' => 'Pengawas Uji',
                'memiliki_wilayah_kerja' => '0',
                'members' => [
                    ['nama' => 'Petugas Baru', 'jabatan' => 'Operator', 'nip' => '197208172025211085'],
                ],
            ])
            ->assertRedirect(route('admin.tim-pelaksanas.index'));

        $newTeam = TimPelaksana::query()->where('nama', 'Tim Uji Petugas')->firstOrFail();

        $this->assertDatabaseHas('petugas', [
            'tim_pelaksana_id' => $newTeam->id,
            'nama' => 'Petugas Baru',
            'nip' => '197208172025211085',
        ]);
    }

    public function test_roster_endpoint_returns_active_petugas(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($admin)
            ->getJson(route('admin.tim-pelaksanas.roster', ['tim' => 'Tim Wilayah 1']));

        $response->assertOk()
            ->assertJsonStructure(['petugas' => [['id', 'nama', 'is_inti', 'is_pengawas', 'tim']]]);

        $this->assertNotEmpty($response->json('petugas'));
    }

    public function test_roster_endpoint_operasional_wilayah_includes_all_wilayah_teams(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($admin)
            ->getJson(route('admin.tim-pelaksanas.roster', ['scope' => 'operasional-wilayah']));

        $response->assertOk();
        $teams = collect($response->json('petugas'))->pluck('tim')->unique()->sort()->values()->all();

        $this->assertContains('Tim Wilayah 1', $teams);
        $this->assertContains('Tim Wilayah 2', $teams);
    }

    public function test_pemeliharaan_store_accepts_petugas_from_other_wilayah_team(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $team1 = TimPelaksana::query()->where('nama', 'Tim Wilayah 1')->firstOrFail();
        $petugasTeam2 = Petugas::query()
            ->whereHas('timPelaksana', fn ($q) => $q->where('nama', 'Tim Wilayah 2'))
            ->orderBy('id')
            ->value('id');
        $this->assertNotNull($petugasTeam2);

        $kelurahan = $team1->kelurahans()->first() ?? Kelurahan::query()->first();

        $payload = [
            'tanggal' => now()->format('Y-m-d\TH:i'),
            'tim' => 'Tim Wilayah 1',
            'taman_id' => Taman::create([
                'nama_taman' => 'Taman Lintas Tim',
                'kategori' => 'Taman Kota',
                'kelurahan_id' => $kelurahan->id,
                'luasan' => 1000,
                'alamat' => 'Alamat lintas tim',
                'deskripsi' => 'Deskripsi lintas tim.',
            ])->id,
            'petugas_ids' => [$petugasTeam2],
            'uraian_pekerjaan' => 'Uji petugas lintas wilayah',
        ];

        foreach (PemeliharaanTaman::FOTO_FIELDS as $field => $label) {
            $payload[$field] = UploadedFile::fake()->image($field.'.jpg');
        }

        $this->actingAs($admin)
            ->post(route('admin.pemeliharaan-tamans.store'), $payload)
            ->assertRedirect();

        $record = PemeliharaanTaman::query()->latest('id')->first();
        $this->assertNotNull($record);
        $this->assertTrue($record->petugas->contains('id', $petugasTeam2));
    }

    public function test_pemeliharaan_store_syncs_selected_petugas(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $team = TimPelaksana::query()->where('nama', 'Tim Wilayah 1')->firstOrFail();
        $petugas = Petugas::query()->where('tim_pelaksana_id', $team->id)->take(2)->pluck('id')->all();
        $kelurahan = Kelurahan::query()->first();

        $payload = [
            'tanggal' => now()->format('Y-m-d\TH:i'),
            'tim' => 'Tim Wilayah 1',
            'taman_id' => Taman::create([
                'nama_taman' => 'Taman Petugas Test',
                'kategori' => 'Taman Kota',
                'kelurahan_id' => $kelurahan->id,
                'luasan' => 1000,
                'alamat' => 'Alamat petugas test',
                'deskripsi' => 'Deskripsi petugas test.',
            ])->id,
            'petugas_ids' => $petugas,
            'uraian_pekerjaan' => 'Uji petugas pelaksana',
        ];

        foreach (PemeliharaanTaman::FOTO_FIELDS as $field => $label) {
            $payload[$field] = UploadedFile::fake()->image($field.'.jpg');
        }

        $this->actingAs($admin)
            ->post(route('admin.pemeliharaan-tamans.store'), $payload)
            ->assertRedirect();

        $record = PemeliharaanTaman::query()->latest('id')->first();

        $this->assertNotNull($record);
        $this->assertSame(count($petugas), (int) $record->jumlah_personil);
        $this->assertCount(count($petugas), $record->petugas);
    }
}
