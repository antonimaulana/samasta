<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\TamanCsvImporter;
use Database\Seeders\WilayahBatamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TamanCsvImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(WilayahBatamSeeder::class);
    }

    public function test_template_download_uses_semicolon_delimiter_for_excel(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($admin)
            ->get(route('admin.tamans.import.template'));

        $response->assertOk();

        $content = $response->streamedContent();

        $this->assertStringContainsString("sep=;\r\n", $content);
        $this->assertStringContainsString('nama_taman;kategori;kecamatan;kelurahan;luasan;alamat;latitude;longitude;deskripsi;fasilitas', $content);
    }

    public function test_import_accepts_semicolon_delimited_csv(): void
    {
        $path = $this->writeTempCsv(
            "sep=;\n".
            "nama_taman;kategori;kecamatan;kelurahan;luasan;alamat;latitude;longitude;deskripsi;fasilitas\n".
            "Taman Semicolon;Taman Kota;Batam Kota;Belian;5000;Jl. Test; -1.082860;104.030500;Deskripsi test;Area bermain\n"
        );

        $result = app(TamanCsvImporter::class)->import($path);

        $this->assertSame(1, $result['imported']);
        $this->assertSame(0, $result['skipped']);
        $this->assertDatabaseHas('tamans', ['nama_taman' => 'Taman Semicolon']);
    }

    public function test_import_still_accepts_comma_delimited_csv(): void
    {
        $path = $this->writeTempCsv(
            "nama_taman,kategori,kecamatan,kelurahan,luasan,alamat,latitude,longitude,deskripsi,fasilitas\n".
            "Taman Comma,Taman Kota,Batam Kota,Belian,5000,Jl. Test,-1.082860,104.030500,Deskripsi test,Area bermain\n"
        );

        $result = app(TamanCsvImporter::class)->import($path);

        $this->assertSame(1, $result['imported']);
        $this->assertDatabaseHas('tamans', ['nama_taman' => 'Taman Comma']);
    }

    private function writeTempCsv(string $content): string
    {
        $path = tempnam(sys_get_temp_dir(), 'taman-import-');
        $this->assertNotFalse($path);
        file_put_contents($path, $content);

        return $path;
    }
}
