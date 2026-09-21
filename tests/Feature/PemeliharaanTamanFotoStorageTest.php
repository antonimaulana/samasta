<?php

namespace Tests\Feature;

use App\Models\PemeliharaanTaman;
use App\Models\User;
use Database\Seeders\TimPelaksanaSeeder;
use Database\Seeders\WilayahBatamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\PetugasTestHelpers;
use Tests\TestCase;

class PemeliharaanTamanFotoStorageTest extends TestCase
{
    use PetugasTestHelpers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(WilayahBatamSeeder::class);
        $this->seed(TimPelaksanaSeeder::class);
    }

    public function test_lapangan_recorder_persists_foto_files_on_public_disk(): void
    {
        Storage::fake('public');

        config([
            'simtaman.lapangan.pin' => '1234',
        ]);

        $this->post(route('lapangan.unlock.store'), ['pin' => '1234']);

        $payload = $this->withPetugasForTeam([
            'tanggal' => now()->format('Y-m-d\TH:i'),
            'lokasi_luar' => '1',
            'lokasi_pelaksanaan' => 'Jl. Test Foto',
        ], 'Tim Wilayah 1');

        foreach (array_keys(PemeliharaanTaman::FOTO_FIELDS) as $field) {
            $payload[$field] = UploadedFile::fake()->image($field.'.jpg');
        }

        $this->post(route('lapangan.pemeliharaan.store', 'wilayah-1'), $payload)
            ->assertRedirect(route('lapangan.index'));

        $record = PemeliharaanTaman::query()->latest('id')->first();
        $this->assertNotNull($record);

        foreach (PemeliharaanTaman::fotoFieldKeys() as $field) {
            $this->assertNotEmpty($record->{$field});
            Storage::disk('public')->assertExists($record->{$field});
            $this->assertNotNull($record->fotoUrl($field));
        }
    }

    public function test_foto_url_is_null_when_file_missing_on_disk(): void
    {
        Storage::fake('public');

        $record = PemeliharaanTaman::query()->create([
            'tanggal' => now(),
            'tim' => 'Tim Wilayah 1',
            'lokasi_pelaksanaan' => 'Test',
            'foto_sebelum_1' => 'pemeliharaan-taman/hilang.jpg',
            'foto_sebelum_2' => 'pemeliharaan-taman/h2.jpg',
            'foto_saat_1' => 'pemeliharaan-taman/s1.jpg',
            'foto_saat_2' => 'pemeliharaan-taman/s2.jpg',
            'foto_sesudah_1' => 'pemeliharaan-taman/ss1.jpg',
            'foto_sesudah_2' => 'pemeliharaan-taman/ss2.jpg',
        ]);

        $this->assertNull($record->fotoUrl('foto_sebelum_1'));
    }
}
