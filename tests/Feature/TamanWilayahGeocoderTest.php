<?php

namespace Tests\Feature;

use App\Models\Kelurahan;
use App\Models\Taman;
use App\Models\User;
use App\Support\KelurahanResolver;
use App\Support\TamanWilayahAssigner;
use Database\Seeders\WilayahBatamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TamanWilayahGeocoderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(WilayahBatamSeeder::class);
    }

    public function test_resolve_wilayah_endpoint_returns_kelurahan_from_coordinates(): void
    {
        Http::fake([
            'nominatim.openstreetmap.org/*' => Http::response([
                'address' => [
                    'village' => 'Belian',
                    'city_district' => 'Batam Kota',
                ],
            ]),
        ]);

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->getJson(route('admin.tamans.resolve-wilayah', [
                'latitude' => -1.08286,
                'longitude' => 104.03050,
            ]))
            ->assertOk()
            ->assertJsonPath('resolved', true)
            ->assertJsonPath('label', 'Batam Kota — Belian');
    }

    public function test_store_auto_assigns_kelurahan_when_coordinates_provided(): void
    {
        Http::fake([
            'nominatim.openstreetmap.org/*' => Http::response([
                'address' => [
                    'suburb' => 'Belian',
                    'city_district' => 'Batam Kota',
                ],
            ]),
        ]);

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $belian = Kelurahan::whereHas('kecamatan', fn ($q) => $q->where('nama', 'Batam Kota'))
            ->where('nama', 'Belian')
            ->first();

        $this->actingAs($admin)
            ->post(route('admin.tamans.store'), [
                'nama_taman' => 'Taman Auto Wilayah',
                'kategori' => 'Taman Kota',
                'luasan' => 1500,
                'alamat' => 'Jl. Auto Wilayah',
                'latitude' => '-1.08286000',
                'longitude' => '104.03050000',
                'deskripsi' => 'Deskripsi taman auto wilayah dari koordinat.',
            ])
            ->assertRedirect(route('admin.tamans.index'));

        $this->assertDatabaseHas('tamans', [
            'nama_taman' => 'Taman Auto Wilayah',
            'kelurahan_id' => $belian->id,
        ]);
    }

    public function test_csv_import_can_resolve_wilayah_from_coordinates_only(): void
    {
        Http::fake([
            'nominatim.openstreetmap.org/*' => Http::response([
                'address' => [
                    'village' => 'Tanjung Uma',
                    'city_district' => 'Lubuk Baja',
                ],
            ]),
        ]);

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $csv = implode("\n", [
            'nama_taman,kategori,kecamatan,kelurahan,luasan,alamat,latitude,longitude,deskripsi,fasilitas',
            'Taman Koordinat Saja,Taman Kota,,,900,Jl. Koordinat,-1.082860,104.030500,Deskripsi dari koordinat saja,',
        ]);

        $file = UploadedFile::fake()->createWithContent('tamans.csv', $csv);

        $this->actingAs($admin)
            ->post(route('admin.tamans.import.store'), ['file' => $file])
            ->assertRedirect(route('admin.tamans.index'));

        $taman = Taman::where('nama_taman', 'Taman Koordinat Saja')->first();
        $this->assertNotNull($taman);
        $this->assertSame('Tanjung Uma', $taman->kelurahan->nama);
    }

    public function test_resolver_returns_null_when_geocoder_disabled(): void
    {
        config(['wilayah.geocoder.enabled' => false]);

        $result = KelurahanResolver::findByCoordinates(-1.08286, 104.03050);

        $this->assertNull($result);
    }

    public function test_assigner_keeps_manual_kelurahan_selection(): void
    {
        Http::fake([
            'nominatim.openstreetmap.org/*' => Http::response([
                'address' => [
                    'village' => 'Belian',
                    'city_district' => 'Batam Kota',
                ],
            ]),
        ]);

        $otherKelurahan = Kelurahan::whereHas('kecamatan', fn ($q) => $q->where('nama', 'Sekupang'))
            ->where('nama', 'Tiban Indah')
            ->first();

        $payload = app(TamanWilayahAssigner::class)->apply([
            'latitude' => '-1.08286000',
            'longitude' => '104.03050000',
            'kelurahan_id' => $otherKelurahan->id,
        ]);

        $this->assertSame($otherKelurahan->id, $payload['kelurahan_id']);
    }
}
