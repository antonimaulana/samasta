<?php

namespace Tests\Feature;

use App\Models\Kelurahan;
use App\Models\Taman;
use App\Models\User;
use App\Support\TamanCsvExporter;
use App\Support\TamanCsvImporter;
use Database\Seeders\WilayahBatamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TamanCsvExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(WilayahBatamSeeder::class);
    }

    public function test_admin_can_export_taman_csv_with_id_and_missing_columns_hint(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $kelurahan = Kelurahan::query()->firstOrFail();

        $taman = Taman::query()->create([
            'nama_taman' => 'Taman Export CSV',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahan->id,
            'luasan' => 0,
            'alamat' => '',
            'latitude' => Taman::defaultLatitude(),
            'longitude' => Taman::defaultLongitude(),
            'deskripsi' => '',
            'fasilitas' => [],
        ]);
        $taman->syncStatusData();

        $response = $this->actingAs($admin)
            ->get(route('admin.tamans.export.csv'));

        $response->assertOk();
        $content = $response->streamedContent();

        $this->assertStringContainsString('id;nama_taman;status_data;kolom_belum_lengkap', $content);
        $this->assertStringContainsString('Taman Export CSV', $content);
        $this->assertStringContainsString((string) $taman->id, $content);
        $this->assertStringContainsString('Belum Lengkap', $content);
    }

    public function test_import_updates_taman_by_id_from_exported_row(): void
    {
        $kelurahan = Kelurahan::query()->firstOrFail();

        $taman = Taman::query()->create([
            'nama_taman' => 'Taman Update By Id',
            'kategori' => '',
            'kelurahan_id' => null,
            'luasan' => 0,
            'alamat' => '',
            'latitude' => Taman::defaultLatitude(),
            'longitude' => Taman::defaultLongitude(),
            'deskripsi' => '',
            'fasilitas' => [],
        ]);

        $exporter = app(TamanCsvExporter::class);
        $row = $exporter->rowFor($taman->fresh(['kelurahan.kecamatan', 'images']));
        $headers = app(TamanCsvImporter::class)->exportHeaders();

        $rowByHeader = array_combine($headers, $row);
        $rowByHeader['alamat'] = 'Jl. Diisi Lewat Reimport';
        $rowByHeader['kategori'] = 'Taman Kota';
        $rowByHeader['kecamatan'] = $kelurahan->kecamatan->nama;
        $rowByHeader['kelurahan'] = $kelurahan->nama;

        $lines = [
            'sep=;',
            implode(';', $headers),
            implode(';', array_map(fn (string $header) => (string) ($rowByHeader[$header] ?? ''), $headers)),
        ];

        $path = tempnam(sys_get_temp_dir(), 'taman-export-');
        file_put_contents($path, implode("\n", $lines)."\n");

        $result = app(TamanCsvImporter::class)->import($path);

        $this->assertSame(0, $result['imported']);
        $this->assertSame(1, $result['updated'], implode(' | ', $result['errors']));

        $taman->refresh();
        $this->assertSame('Jl. Diisi Lewat Reimport', $taman->alamat);
        $this->assertSame('Taman Kota', $taman->kategori);
        $this->assertSame($kelurahan->id, $taman->kelurahan_id);
    }
}
