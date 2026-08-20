<?php

namespace Tests\Feature;

use App\Models\PemeliharaanTaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PemeliharaanDateRangeFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_index_filters_kinerja_by_date_range(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        PemeliharaanTaman::create($this->kinerjaPayload('2026-07-01', 'Taman Awal'));
        PemeliharaanTaman::create($this->kinerjaPayload('2026-07-10', 'Taman Tengah'));
        PemeliharaanTaman::create($this->kinerjaPayload('2026-07-20', 'Taman Akhir'));

        $this->actingAs($admin)
            ->get(route('admin.pemeliharaan-tamans.index', [
                'tanggal_mulai' => '2026-07-05',
                'tanggal_selesai' => '2026-07-15',
            ]))
            ->assertOk()
            ->assertSee('Taman Tengah')
            ->assertDontSee('Taman Awal')
            ->assertDontSee('Taman Akhir');
    }

    public function test_index_defaults_to_today_when_no_date_filter(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        PemeliharaanTaman::create($this->kinerjaPayload(now()->toDateString(), 'Taman Hari Ini'));
        PemeliharaanTaman::create($this->kinerjaPayload(now()->subDay()->toDateString(), 'Taman Kemarin'));

        $this->actingAs($admin)
            ->get(route('admin.pemeliharaan-tamans.index'))
            ->assertOk()
            ->assertSee('Taman Hari Ini')
            ->assertDontSee('Taman Kemarin');
    }

    /**
     * @return array<string, mixed>
     */
    private function kinerjaPayload(string $tanggal, string $lokasi): array
    {
        return [
            'tanggal' => $tanggal,
            'tim' => 'Tim Wilayah 1',
            'lokasi_pelaksanaan' => $lokasi,
            'uraian_pekerjaan' => 'Test kinerja rentang tanggal.',
        ];
    }
}
