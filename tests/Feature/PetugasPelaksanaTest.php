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
            ->post(route('admin.tim-pelaksanas.petugas.store', $team), [
                'nama' => 'Petugas Baru',
                'jabatan' => 'Operator',
                'is_inti' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('petugas', [
            'tim_pelaksana_id' => $team->id,
            'nama' => 'Petugas Baru',
            'is_inti' => true,
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
