<?php

namespace Tests\Feature;

use App\Models\PemeliharaanTaman;
use App\Models\User;
use App\Support\TimPelaksanaPdfOrder;
use Database\Seeders\TimPelaksanaSeeder;
use Database\Seeders\WilayahBatamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\PetugasTestHelpers;
use Tests\TestCase;

class PemeliharaanTamanPdfTest extends TestCase
{
    use PetugasTestHelpers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(WilayahBatamSeeder::class);
        $this->seed(TimPelaksanaSeeder::class);
    }

    public function test_admin_can_export_pemeliharaan_rutin_bulk_pdf(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('PHP GD extension is required for PDF export.');
        }

        Storage::fake('public');

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $payload = $this->withPetugasForTeam([
            'tanggal' => now()->format('Y-m-d\TH:i'),
            'tim' => 'Tim Wilayah 1',
            'lokasi_luar' => '1',
            'lokasi_pelaksanaan' => 'Lokasi uji PDF',
            'uraian_pekerjaan' => 'Uji export PDF',
        ], 'Tim Wilayah 1');

        foreach (array_keys(PemeliharaanTaman::FOTO_FIELDS) as $field) {
            $payload[$field] = UploadedFile::fake()->image($field.'.jpg');
        }

        $this->actingAs($admin)
            ->post(route('admin.pemeliharaan-tamans.store'), $payload)
            ->assertRedirect();

        $this->actingAs($admin)
            ->get(route('admin.pemeliharaan-tamans.export-pdf', [
                'tanggal_mulai' => now()->toDateString(),
                'tanggal_selesai' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_bulk_pdf_lists_tim_sections_in_standard_order(): void
    {
        $tanggal = now()->toDateString();

        PemeliharaanTaman::create([
            'tanggal' => $tanggal,
            'tim' => 'Tim Wilayah 2',
            'lokasi_pelaksanaan' => 'Lokasi W2',
            'jumlah_personil' => 2,
            'uraian_pekerjaan' => 'W2',
        ]);

        PemeliharaanTaman::create([
            'tanggal' => $tanggal,
            'tim' => 'Tim Wilayah 1',
            'lokasi_pelaksanaan' => 'Lokasi W1',
            'jumlah_personil' => 2,
            'uraian_pekerjaan' => 'W1',
        ]);

        $grouped = PemeliharaanTaman::query()
            ->whereDate('tanggal', $tanggal)
            ->get()
            ->groupBy('tim');

        $sorted = TimPelaksanaPdfOrder::sortGrouped($grouped);

        $html = view('admin.pemeliharaan_tamans.pdf', [
            'tanggalMulai' => now()->startOfDay(),
            'tanggalSelesai' => now()->startOfDay(),
            'kinerjasPerTim' => $sorted,
            'timFilter' => null,
            'totalOperasional' => 2,
        ])->render();

        $posW1 = strpos($html, 'Tim Wilayah 1 (1 operasional)');
        $posW2 = strpos($html, 'Tim Wilayah 2 (1 operasional)');

        $this->assertNotFalse($posW1);
        $this->assertNotFalse($posW2);
        $this->assertLessThan($posW2, $posW1);
    }

    public function test_admin_can_export_single_pemeliharaan_rutin_pdf(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('PHP GD extension is required for PDF export.');
        }

        Storage::fake('public');

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $record = PemeliharaanTaman::create([
            'tanggal' => now()->toDateString(),
            'tim' => 'Tim Wilayah 1',
            'lokasi_pelaksanaan' => 'Lokasi PDF per kegiatan',
            'jumlah_personil' => 2,
            'uraian_pekerjaan' => 'Detail PDF',
        ]);

        foreach (array_keys(PemeliharaanTaman::FOTO_FIELDS) as $field) {
            $path = 'pemeliharaan-taman/'.$field.'.jpg';
            Storage::disk('public')->put($path, UploadedFile::fake()->image($field.'.jpg')->getContent());
            $record->{$field} = $path;
        }
        $record->save();

        $this->actingAs($admin)
            ->get(route('admin.pemeliharaan-tamans.export-pdf-operasional', $record))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
