<?php

namespace Tests\Feature;

use App\Models\Bibit;
use App\Models\BibitMasuk;
use App\Models\User;
use App\Support\BibitCsvImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BibitImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_template_download_uses_semicolon_delimiter_for_excel(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($admin)
            ->get(route('admin.bibits.import.template'));

        $response->assertOk();

        $content = $response->streamedContent();

        $this->assertStringContainsString("sep=;\r\n", $content);
        $this->assertStringContainsString('nama_tanaman;nama_ilmiah;jenis;stok_awal;sumber;tanggal_masuk;status_siap_tanam', $content);
    }

    public function test_import_creates_bibit_and_stok_masuk(): void
    {
        $path = $this->writeTempCsv(
            "sep=;\n".
            "nama_tanaman;nama_ilmiah;jenis;stok_awal;sumber;tanggal_masuk;status_siap_tanam\n".
            "Mahoni;Swietenia macrophylla;Pohon Pelindung / Peneduh;100;Produksi;2026-08-11;1\n"
        );

        $result = app(BibitCsvImporter::class)->import($path);

        $this->assertSame(1, $result['imported']);
        $this->assertSame(0, $result['skipped']);

        $bibit = Bibit::where('nama_tanaman', 'Mahoni')->first();
        $this->assertNotNull($bibit);
        $this->assertSame('Swietenia macrophylla', $bibit->nama_ilmiah);
        $this->assertSame('Pohon Pelindung / Peneduh', $bibit->jenis);
        $this->assertSame(100, $bibit->stok_tersedia);

        $this->assertDatabaseHas('bibit_masuks', [
            'bibit_id' => $bibit->id,
            'jumlah' => 100,
            'sisa_stok' => 100,
            'sumber' => 'Produksi',
        ]);
    }

    public function test_import_adds_stok_masuk_to_existing_bibit(): void
    {
        $bibit = Bibit::create([
            'nama_tanaman' => 'Mahoni',
            'nama_ilmiah' => 'Swietenia macrophylla',
            'jenis' => 'Pohon Pelindung / Peneduh',
            'stok_tersedia' => 20,
            'sumber_bibit' => 'Produksi',
            'status_siap_tanam' => false,
        ]);

        BibitMasuk::create([
            'bibit_id' => $bibit->id,
            'jumlah' => 20,
            'sisa_stok' => 20,
            'tanggal_masuk' => now()->toDateString(),
            'sumber' => 'Produksi',
            'status_siap_tanam' => false,
        ]);

        $path = $this->writeTempCsv(
            "nama_tanaman,nama_ilmiah,jenis,stok_awal\n".
            "Mahoni,Swietenia macrophylla,Pohon Pelindung / Peneduh,30\n"
        );

        $result = app(BibitCsvImporter::class)->import($path);

        $this->assertSame(1, $result['imported']);
        $this->assertSame(50, $bibit->fresh()->stok_tersedia);
        $this->assertSame(2, BibitMasuk::where('bibit_id', $bibit->id)->count());
    }

    public function test_operator_can_import_bibit_from_csv(): void
    {
        $operator = User::factory()->create(['role' => User::ROLE_OPERATOR]);

        $csv = implode("\n", [
            'nama_tanaman,nama_ilmiah,jenis,stok_awal',
            'Flamboyan,Delonix regia,Pohon Pelindung / Hias,25',
        ]);

        $file = UploadedFile::fake()->createWithContent('bibits.csv', $csv);

        $this->actingAs($operator)
            ->post(route('admin.bibits.import.store'), ['file' => $file])
            ->assertRedirect(route('admin.bibits.index'))
            ->assertSessionHas('import_result');

        $this->assertDatabaseHas('bibits', ['nama_tanaman' => 'Flamboyan', 'stok_tersedia' => 25]);
    }

    public function test_viewer_cannot_import_bibit(): void
    {
        $viewer = User::factory()->create(['role' => User::ROLE_VIEWER]);

        $csv = implode("\n", [
            'nama_tanaman,nama_ilmiah,jenis,stok_awal',
            'Flamboyan,Delonix regia,Pohon Pelindung / Hias,25',
        ]);

        $file = UploadedFile::fake()->createWithContent('bibits.csv', $csv);

        $this->actingAs($viewer)
            ->get(route('admin.bibits.import'))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($viewer)
            ->post(route('admin.bibits.import.store'), ['file' => $file])
            ->assertForbidden();

        $this->assertDatabaseCount('bibits', 0);
    }

    public function test_import_skips_row_with_invalid_jenis(): void
    {
        $path = $this->writeTempCsv(
            "nama_tanaman,nama_ilmiah,jenis,stok_awal\n".
            "Tanaman Invalid,,Jenis Salah,10\n"
        );

        $result = app(BibitCsvImporter::class)->import($path);

        $this->assertSame(0, $result['imported']);
        $this->assertSame(1, $result['skipped']);
        $this->assertDatabaseCount('bibits', 0);
    }

    public function test_import_skips_row_when_jenis_conflicts_with_existing_bibit(): void
    {
        Bibit::create([
            'nama_tanaman' => 'Mahoni',
            'jenis' => 'Pohon Pelindung / Peneduh',
            'stok_tersedia' => 0,
            'sumber_bibit' => 'Produksi',
        ]);

        $path = $this->writeTempCsv(
            "nama_tanaman,nama_ilmiah,jenis,stok_awal\n".
            "Mahoni,,Semak / Tanaman Hias Daun,10\n"
        );

        $result = app(BibitCsvImporter::class)->import($path);

        $this->assertSame(0, $result['imported']);
        $this->assertSame(1, $result['skipped']);
        $this->assertSame(0, BibitMasuk::count());
    }

    public function test_import_accepts_indonesian_header_aliases(): void
    {
        $path = $this->writeTempCsv(
            "sep=;\n".
            "nama tanaman;nama ilmiah;jenis tanaman;stok awal\n".
            "Angsana;Pterocarpus indicus;Pohon Peneduh / Pelindung;75\n"
        );

        $result = app(BibitCsvImporter::class)->import($path);

        $this->assertSame(1, $result['imported']);
        $this->assertDatabaseHas('bibits', [
            'nama_tanaman' => 'Angsana',
            'stok_tersedia' => 75,
        ]);
    }

    private function writeTempCsv(string $content): string
    {
        $path = tempnam(sys_get_temp_dir(), 'bibit-import-');
        $this->assertNotFalse($path);
        file_put_contents($path, $content);

        return $path;
    }
}
