<?php

namespace Tests\Feature;

use App\Models\Taman;
use App\Models\User;
use App\Support\TamanCsvImporter;
use Database\Seeders\WilayahBatamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
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
        $this->assertStringContainsString('nama_taman;kategori;kecamatan;kelurahan;luasan;alamat;latitude;longitude;deskripsi;fasilitas;tahun_pembangunan;nilai_pembangunan;kontraktor;konsultan_perencana;data_verified_at', $content);
    }

    public function test_import_accepts_semicolon_delimited_csv(): void
    {
        $path = $this->writeTempCsv(
            "sep=;\n".
            "nama_taman;kategori;kecamatan;kelurahan;luasan;alamat;latitude;longitude;deskripsi;fasilitas\n".
            "Taman Semicolon;Taman Kota;Batam Kota;Belian;5000;Jl. Test;1.045600;104.030500;Deskripsi test;Area bermain\n"
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
            "Taman Comma,Taman Kota,Batam Kota,Belian,5000,Jl. Test,1.045600,104.030500,Deskripsi test,Area bermain\n"
        );

        $result = app(TamanCsvImporter::class)->import($path);

        $this->assertSame(1, $result['imported']);
        $this->assertDatabaseHas('tamans', ['nama_taman' => 'Taman Comma']);
    }

    public function test_import_accepts_new_pembangunan_and_verification_columns(): void
    {
        $path = $this->writeTempCsv(
            'nama_taman,kategori,kecamatan,kelurahan,luasan,alamat,latitude,longitude,deskripsi,fasilitas,tahun_pembangunan,nilai_pembangunan,kontraktor,konsultan_perencana,data_verified_at'.PHP_EOL.
            'Taman Lengkap,Taman Kota,Batam Kota,Belian,5000,Jl. Lengkap,,,Deskripsi lengkap,Playground:Baik; Area Parkir:Rusak Ringan,2019,1.500.000.000,PT Kontraktor A,PT Konsultan B,23/08/2026 09:30'.PHP_EOL
        );

        $result = app(TamanCsvImporter::class)->import($path);

        $this->assertSame(1, $result['imported']);
        $this->assertDatabaseHas('tamans', [
            'nama_taman' => 'Taman Lengkap',
            'tahun_pembangunan' => 2019,
            'nilai_pembangunan' => 1500000000,
            'kontraktor' => 'PT Kontraktor A',
            'konsultan_perencana' => 'PT Konsultan B',
        ]);

        $taman = \App\Models\Taman::where('nama_taman', 'Taman Lengkap')->first();
        $this->assertNotNull($taman->data_verified_at);
        $this->assertSame([
            ['nama' => 'Playground', 'kondisi' => 'Baik'],
            ['nama' => 'Area Parkir', 'kondisi' => 'Rusak Ringan'],
        ], $taman->fasilitas_items);
    }

    public function test_import_accepts_row_with_mostly_empty_columns_and_marks_belum_lengkap(): void
    {
        $path = $this->writeTempCsv(
            'nama_taman,kategori,kecamatan,kelurahan,luasan,alamat,latitude,longitude,deskripsi,fasilitas'.PHP_EOL.
            'Taman Minimal,,,,,,,,,'.PHP_EOL
        );

        $result = app(TamanCsvImporter::class)->import($path);

        $this->assertSame(1, $result['imported']);
        $this->assertSame(0, $result['skipped']);

        $taman = Taman::where('nama_taman', 'Taman Minimal')->first();
        $this->assertNotNull($taman);
        $this->assertSame(Taman::STATUS_DATA_BELUM_LENGKAP, $taman->status_data);
        $this->assertNull($taman->kelurahan_id);
        $this->assertSame(0, $taman->luasan);
        $this->assertSame('', $taman->alamat);
        $this->assertSame('', $taman->deskripsi);
        $this->assertSame(Taman::defaultLatitude(), $taman->latitude);
        $this->assertSame(Taman::defaultLongitude(), $taman->longitude);
    }

    public function test_import_accepts_comma_csv_even_when_sep_hint_uses_semicolon(): void
    {
        $path = $this->writeTempCsv(
            "sep=;\n".
            'nama_taman,kategori,kecamatan,kelurahan,luasan,alamat,latitude,longitude,deskripsi,fasilitas,tahun_pembangunan,nilai_pembangunan,kontraktor,konsultan_perencana,data_verified_at'.PHP_EOL.
            'Taman Excel Comma,Taman Kota,,,5000,Jl. Comma,,,Deskripsi,,2019,,,,'.PHP_EOL
        );

        $result = app(TamanCsvImporter::class)->import($path);

        $this->assertSame(1, $result['imported'], implode(' | ', $result['errors']));
        $this->assertDatabaseHas('tamans', ['nama_taman' => 'Taman Excel Comma', 'tahun_pembangunan' => 2019]);
    }

    public function test_import_accepts_excel_sep_row_with_comma_header_on_same_line(): void
    {
        $path = $this->writeTempCsv(
            'sep=,nama_tam,kategori,kecamatan,kelurahan,luasan,alamat,latitude,longitude,deskripsi,fasilitas,tahun_pen,nilai_pemb,konsultan,data_verified_at'.PHP_EOL.
            'Taman Satu Baris,Taman Kota,,,5000,Jl. Satu,,,Deskripsi,,2015,,,'.PHP_EOL
        );

        $result = app(TamanCsvImporter::class)->import($path);

        $this->assertSame(1, $result['imported'], implode(' | ', $result['errors']));
        $this->assertDatabaseHas('tamans', ['nama_taman' => 'Taman Satu Baris', 'luasan' => 5000]);
    }

    public function test_import_accepts_truncated_excel_headers_and_indonesian_decimal_luasan(): void
    {
        $path = $this->writeTempCsv(
            "sep=;\n".
            'nama_tam;kategori;kecamatan;kelurahan;luasan;alamat;latitude;longitude;deskripsi;fasilitas;tahun_pen;nilai_pemb;konsultan_;data_verified_at'.PHP_EOL.
            'Taman Luasan ID;Taman Kota;;;3.358,3;Jl. Luasan;;;;2015;;;;'.PHP_EOL
        );

        $result = app(TamanCsvImporter::class)->import($path);

        $this->assertSame(1, $result['imported'], implode(' | ', $result['errors']));

        $taman = Taman::where('nama_taman', 'Taman Luasan ID')->first();
        $this->assertSame(3358, $taman->luasan);
        $this->assertSame(Taman::STATUS_DATA_BELUM_LENGKAP, $taman->status_data);
    }

    public function test_import_treats_dash_and_formatted_numbers_as_empty_or_valid(): void
    {
        $path = $this->writeTempCsv(
            "sep=;\n".
            'nama_taman;kategori;kecamatan;kelurahan;luasan;alamat;latitude;longitude;deskripsi;fasilitas;tahun_pembangunan;nilai_pembangunan;kontraktor;konsultan_perencana;data_verified_at'.PHP_EOL.
            'Taman Dash;-;-;-;-;-;Jl. Dash;;;Deskripsi;-;-;Rp 1.500.000;-;-'.PHP_EOL.
            'Taman Format;Taman Kota;Batam Kota;Belian;5.000 m²;Jl. Format;;;Deskripsi;;-;5000,00;;;'.PHP_EOL
        );

        $result = app(TamanCsvImporter::class)->import($path);

        $this->assertSame(2, $result['imported'], implode(' | ', $result['errors']));
        $this->assertSame(0, $result['skipped']);

        $dash = Taman::where('nama_taman', 'Taman Dash')->first();
        $this->assertSame(Taman::STATUS_DATA_BELUM_LENGKAP, $dash->status_data);
        $this->assertNull($dash->nilai_pembangunan);

        $format = Taman::where('nama_taman', 'Taman Format')->first();
        $this->assertSame(5000, $format->luasan);
        $this->assertSame(5000, $format->nilai_pembangunan);
    }

    public function test_import_update_preserves_existing_fields_when_csv_cell_empty(): void
    {
        $kelurahan = \App\Models\Kelurahan::whereHas('kecamatan', fn ($q) => $q->where('nama', 'Batam Kota'))
            ->where('nama', 'Belian')
            ->first();

        Taman::create([
            'nama_taman' => 'Taman Keep Old',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahan->id,
            'luasan' => 900,
            'alamat' => 'Alamat tetap',
            'deskripsi' => 'Deskripsi tetap',
        ]);

        $path = $this->writeTempCsv(
            'nama_taman,kategori,kecamatan,kelurahan,luasan,alamat,latitude,longitude,deskripsi,fasilitas'.PHP_EOL.
            'Taman Keep Old,,,,5000,,,,,'.PHP_EOL
        );

        $result = app(TamanCsvImporter::class)->import($path);

        $this->assertSame(0, $result['imported']);
        $this->assertSame(1, $result['updated']);

        $taman = Taman::where('nama_taman', 'Taman Keep Old')->first();
        $this->assertSame(5000, $taman->luasan);
        $this->assertSame('Alamat tetap', $taman->alamat);
        $this->assertSame('Deskripsi tetap', $taman->deskripsi);
        $this->assertSame('Taman Kota', $taman->kategori);
    }

    public function test_import_without_coordinates_does_not_call_external_geocoder(): void
    {
        Http::fake();

        $lines = [
            'nama_taman,kategori,kecamatan,kelurahan,luasan,alamat,latitude,longitude,deskripsi,fasilitas',
        ];

        for ($i = 1; $i <= 10; $i++) {
            $lines[] = "Taman Bulk {$i},Taman Kota,,,,,,,,";
        }

        $path = $this->writeTempCsv(implode(PHP_EOL, $lines).PHP_EOL);
        $result = app(TamanCsvImporter::class)->import($path);

        $this->assertSame(10, $result['imported'], implode(' | ', $result['errors']));
        $this->assertSame(0, $result['skipped']);
        Http::assertNothingSent();
        $this->assertSame(10, Taman::query()->count());
    }

    private function writeTempCsv(string $content): string
    {
        $path = tempnam(sys_get_temp_dir(), 'taman-import-');
        $this->assertNotFalse($path);
        file_put_contents($path, $content);

        return $path;
    }
}
