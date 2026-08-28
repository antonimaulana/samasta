<?php

namespace Tests\Feature;

use App\Models\Kelurahan;
use App\Models\Taman;
use App\Models\User;
use App\Support\TamanCompleteness;
use Database\Seeders\WilayahBatamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TamanCompletenessTest extends TestCase
{
    use RefreshDatabase;

    public function test_score_counts_thirteen_profile_fields(): void
    {
        $taman = Taman::create([
            'nama_taman' => 'Taman Hampir Lengkap',
            'kategori' => 'Taman Kota',
            'luasan' => 5000,
            'alamat' => 'Jl. Contoh No. 1',
            'deskripsi' => 'Deskripsi lengkap taman.',
            'latitude' => '1.04560000',
            'longitude' => '104.03050000',
            'fasilitas' => [
                ['nama' => 'Jogging track', 'kondisi' => 'Baik'],
            ],
            'tahun_pembangunan' => 2018,
            'nilai_pembangunan' => 1500000000,
            'kontraktor' => 'PT Contoh',
            'konsultan_perencana' => 'Konsultan ABC',
            'foto' => 'tamans/sample.jpg',
        ]);

        $completeness = app(TamanCompleteness::class);

        $this->assertSame(92, $completeness->score($taman));
        $this->assertSame(Taman::STATUS_DATA_BELUM_LENGKAP, $completeness->statusFor($taman));
    }

    public function test_status_from_score_is_binary(): void
    {
        $completeness = app(TamanCompleteness::class);

        $this->assertSame(Taman::STATUS_DATA_BELUM_LENGKAP, $completeness->labelFromScore(0));
        $this->assertSame(Taman::STATUS_DATA_BELUM_LENGKAP, $completeness->labelFromScore(99));
        $this->assertSame(Taman::STATUS_DATA_LENGKAP, $completeness->labelFromScore(100));
    }

    public function test_status_data_text(): void
    {
        $taman = Taman::make(['status_data' => Taman::STATUS_DATA_BELUM_LENGKAP]);
        $this->assertSame('Belum Lengkap', $taman->status_data_text);

        $taman->status_data = Taman::STATUS_DATA_LENGKAP;
        $this->assertSame('Lengkap', $taman->status_data_text);
    }

    public function test_sync_status_data_updates_model(): void
    {
        $taman = Taman::create([
            'nama_taman' => 'Taman Sync',
            'kategori' => 'Taman Kota',
            'luasan' => 1000,
            'alamat' => 'Alamat sync',
            'deskripsi' => 'Deskripsi sync.',
        ]);

        $taman->syncStatusData();
        $taman->refresh();

        $this->assertSame(Taman::STATUS_DATA_BELUM_LENGKAP, $taman->status_data);
    }

    public function test_admin_can_create_taman_with_only_nama_and_kategori(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->post(route('admin.tamans.store'), [
                'nama_taman' => 'Taman Minimal Form',
                'kategori' => 'Taman Kota',
            ])
            ->assertRedirect(route('admin.tamans.index'));

        $taman = Taman::query()->where('nama_taman', 'Taman Minimal Form')->first();

        $this->assertNotNull($taman);
        $this->assertSame('Taman Kota', $taman->kategori);
        $this->assertSame(0, $taman->luasan);
        $this->assertSame('', $taman->alamat);
        $this->assertSame('', $taman->deskripsi);
        $this->assertSame(Taman::STATUS_DATA_BELUM_LENGKAP, $taman->status_data);
        $this->assertNotNull($taman->data_verified_at);
    }

    public function test_fully_filled_taman_has_lengkap_status(): void
    {
        $this->seed(WilayahBatamSeeder::class);
        $kelurahanId = Kelurahan::query()->value('id');
        $this->assertNotNull($kelurahanId);

        $taman = Taman::create([
            'nama_taman' => 'Taman Lengkap',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 5000,
            'alamat' => 'Jl. Lengkap',
            'deskripsi' => 'Deskripsi lengkap.',
            'latitude' => '1.04560000',
            'longitude' => '104.03050000',
            'fasilitas' => [['nama' => 'Toilet', 'kondisi' => 'Baik']],
            'tahun_pembangunan' => 2020,
            'nilai_pembangunan' => 1000000000,
            'kontraktor' => 'PT Jaya',
            'konsultan_perencana' => 'Konsultan X',
            'foto' => 'tamans/full.jpg',
        ]);

        $taman->syncStatusData();
        $taman->refresh();

        $this->assertSame(Taman::STATUS_DATA_LENGKAP, $taman->status_data);
        $this->assertSame(100, app(TamanCompleteness::class)->score($taman));
    }

    public function test_fasilitas_normalizes_legacy_string_array(): void
    {
        $normalized = Taman::normalizeFasilitasArray(['Toilet', 'Area bermain']);

        $this->assertSame([
            ['nama' => 'Toilet', 'kondisi' => 'Baik'],
            ['nama' => 'Playground', 'kondisi' => 'Baik'],
        ], $normalized);
    }

    public function test_resolve_fasilitas_nama_maps_aliases(): void
    {
        $this->assertSame('Playground', Taman::resolveFasilitasNama('Area bermain'));
        $this->assertSame('Area Parkir', Taman::resolveFasilitasNama('Tempat Parkir'));
        $this->assertNull(Taman::resolveFasilitasNama('Wifi'));
    }

    public function test_normalize_batam_coordinates_fixes_negative_latitude(): void
    {
        $normalized = Taman::normalizeBatamCoordinates('-1.08286000', '104.03050000');

        $this->assertSame('1.08286000', $normalized['latitude']);
        $this->assertSame('104.03050000', $normalized['longitude']);
    }

    public function test_normalize_batam_coordinates_fixes_swapped_values(): void
    {
        $normalized = Taman::normalizeBatamCoordinates('104.09619800', '1.13214200');

        $this->assertSame('1.13214200', $normalized['latitude']);
        $this->assertSame('104.09619800', $normalized['longitude']);
    }

    public function test_normalized_map_coordinates_returns_null_for_placeholder(): void
    {
        $taman = Taman::create([
            'nama_taman' => 'Taman Placeholder',
            'kategori' => 'Taman Kota',
            'luasan' => 1000,
            'alamat' => 'Alamat',
            'deskripsi' => 'Deskripsi.',
            'latitude' => Taman::DEFAULT_LATITUDE,
            'longitude' => Taman::DEFAULT_LONGITUDE,
        ]);

        $this->assertNull($taman->normalizedMapCoordinates());
    }
}
