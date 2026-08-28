<?php

namespace Tests\Feature;

use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\Taman;
use App\Models\User;
use Database\Seeders\WilayahBatamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class TamanImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(WilayahBatamSeeder::class);
    }

    public function test_admin_can_download_import_template(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('admin.tamans.import.template'))
            ->assertOk()
            ->assertHeader('content-disposition');
    }

    public function test_admin_can_import_taman_from_csv(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $csv = implode("\n", [
            'nama_taman,kategori,kecamatan,kelurahan,luasan,alamat,latitude,longitude,deskripsi,fasilitas',
            'Taman Import A,Taman Kota,Batam Kota,Belian,1200,Jl. Import A,,,Deskripsi taman import A,Playground',
            'Taman Import B,Taman Lingkungan,Lubuk Baja,Tanjung Uma,800,Jl. Import B,-1.1,104.1,Deskripsi taman import B,',
        ]);

        $file = UploadedFile::fake()->createWithContent('tamans.csv', $csv);

        $this->actingAs($admin)
            ->post(route('admin.tamans.import.store'), ['file' => $file])
            ->assertRedirect(route('admin.tamans.index'))
            ->assertSessionHas('import_result');

        $this->assertDatabaseHas('tamans', ['nama_taman' => 'Taman Import A']);
        $this->assertDatabaseHas('tamans', ['nama_taman' => 'Taman Import B']);

        $taman = Taman::where('nama_taman', 'Taman Import A')->first();
        $this->assertSame('Belian', $taman->kelurahan->nama);
        $this->assertSame('Batam Kota', $taman->kelurahan->kecamatan->nama);
    }

    public function test_operator_cannot_import_taman(): void
    {
        $operator = User::factory()->create(['role' => User::ROLE_OPERATOR]);

        $csv = implode("\n", [
            'nama_taman,kategori,kecamatan,kelurahan,luasan,alamat,latitude,longitude,deskripsi,fasilitas',
            'Taman Import A,Taman Kota,Batam Kota,Belian,1200,Jl. Import A,,,Deskripsi taman import A,',
        ]);

        $file = UploadedFile::fake()->createWithContent('tamans.csv', $csv);

        $this->actingAs($operator)
            ->get(route('admin.tamans.import'))
            ->assertForbidden();

        $this->actingAs($operator)
            ->post(route('admin.tamans.import.store'), ['file' => $file])
            ->assertForbidden();

        $this->assertDatabaseCount('tamans', 0);
    }

    public function test_import_updates_existing_taman_when_nama_matches(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $kelurahan = Kelurahan::whereHas('kecamatan', fn ($q) => $q->where('nama', 'Batam Kota'))->where('nama', 'Belian')->first();

        Taman::create([
            'nama_taman' => 'Taman Existing',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahan->id,
            'luasan' => 1000,
            'alamat' => 'Alamat lama',
            'deskripsi' => 'Deskripsi taman existing.',
        ]);

        $csv = implode("\n", [
            'nama_taman,kategori,kecamatan,kelurahan,luasan,alamat,latitude,longitude,deskripsi,fasilitas,tahun_pembangunan',
            'Taman Existing,Taman Kota,Batam Kota,Belian,2000,Jl. Baru,,,Deskripsi baru,,2018',
            'Taman Baru,Jalur Hijau Jalan,Sekupang,Tiban Indah,500,Jl. Baru 2,,,Deskripsi taman baru,,',
        ]);

        $file = UploadedFile::fake()->createWithContent('tamans.csv', $csv);

        $this->actingAs($admin)
            ->post(route('admin.tamans.import.store'), ['file' => $file])
            ->assertRedirect(route('admin.tamans.index'));

        $result = session('import_result');
        $this->assertSame(1, $result['imported']);
        $this->assertSame(1, $result['updated']);
        $this->assertSame(0, $result['skipped']);
        $this->assertDatabaseCount('tamans', 2);

        $existing = Taman::where('nama_taman', 'Taman Existing')->first();
        $this->assertSame(2000, $existing->luasan);
        $this->assertSame('Jl. Baru', $existing->alamat);
        $this->assertSame(2018, $existing->tahun_pembangunan);
        $this->assertSame(Taman::defaultLatitude(), $existing->latitude);
        $this->assertSame(Taman::defaultLongitude(), $existing->longitude);
    }

    public function test_import_accepts_indonesian_luasan_format(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $csv = implode("\n", [
            'nama_taman,kategori,kecamatan,kelurahan,luasan,alamat,latitude,longitude,deskripsi,fasilitas',
            'Taman Luasan Format,Taman Kota,Batam Kota,Belian,5.000,Jl. Format,,,Deskripsi luasan format,',
        ]);

        $file = UploadedFile::fake()->createWithContent('tamans.csv', $csv);

        $this->actingAs($admin)
            ->post(route('admin.tamans.import.store'), ['file' => $file])
            ->assertRedirect(route('admin.tamans.index'));

        $this->assertDatabaseHas('tamans', [
            'nama_taman' => 'Taman Luasan Format',
            'luasan' => 5000,
        ]);
    }

    public function test_import_repairs_single_column_csv_rows_from_excel(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $csv = implode("\n", [
            'nama_taman;kategori;kecamatan;kelurahan;luasan;alamat;latitude;longitude;deskripsi;fasilitas',
            'Taman Satu Kolom;Taman Kota;Batam Kota;Belian;1200;Jl. Satu Kolom;;;Deskripsi satu kolom;',
        ]);

        $file = UploadedFile::fake()->createWithContent('tamans.csv', $csv);

        $this->actingAs($admin)
            ->post(route('admin.tamans.import.store'), ['file' => $file])
            ->assertRedirect(route('admin.tamans.index'));

        $this->assertDatabaseHas('tamans', ['nama_taman' => 'Taman Satu Kolom']);
    }

    public function test_import_with_all_rows_skipped_stays_on_import_page(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $csv = implode("\n", [
            'nama_taman,kategori,kecamatan,kelurahan,luasan,alamat,latitude,longitude,deskripsi,fasilitas',
            'Taman Invalid,Kategori Salah,Batam Kota,Belian,5000,Jl. Invalid,,,Deskripsi invalid,',
        ]);

        $file = UploadedFile::fake()->createWithContent('tamans.csv', $csv);

        $this->actingAs($admin)
            ->post(route('admin.tamans.import.store'), ['file' => $file])
            ->assertRedirect(route('admin.tamans.import'))
            ->assertSessionHas('warning');

        $this->assertDatabaseMissing('tamans', ['nama_taman' => 'Taman Invalid']);
    }

    public function test_import_accepts_excel_decimal_luasan(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $csv = implode("\n", [
            'nama_taman,kategori,kecamatan,kelurahan,luasan,alamat,latitude,longitude,deskripsi,fasilitas',
            'Taman Excel Decimal,Taman Kota,Batam Kota,Belian,5000.00,Jl. Decimal,,,Deskripsi decimal,',
        ]);

        $file = UploadedFile::fake()->createWithContent('tamans.csv', $csv);

        $this->actingAs($admin)
            ->post(route('admin.tamans.import.store'), ['file' => $file])
            ->assertRedirect(route('admin.tamans.index'));

        $this->assertDatabaseHas('tamans', [
            'nama_taman' => 'Taman Excel Decimal',
            'luasan' => 5000,
        ]);
    }

    public function test_import_accepts_kelurahan_without_kecamatan_prefix(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $csv = implode("\n", [
            'nama_taman,kategori,kecamatan,kelurahan,luasan,alamat,latitude,longitude,deskripsi,fasilitas',
            'Taman Kel Prefix,Taman Kota,,Kelurahan Belian,800,Jl. Prefix,,,Deskripsi kel prefix,',
        ]);

        $file = UploadedFile::fake()->createWithContent('tamans.csv', $csv);

        $this->actingAs($admin)
            ->post(route('admin.tamans.import.store'), ['file' => $file])
            ->assertRedirect(route('admin.tamans.index'));

        $this->assertDatabaseHas('tamans', ['nama_taman' => 'Taman Kel Prefix']);
    }

    public function test_viewer_cannot_access_import(): void
    {
        $viewer = User::factory()->create(['role' => User::ROLE_VIEWER]);

        $this->actingAs($viewer)
            ->get(route('admin.tamans.import'))
            ->assertRedirect(route('admin.dashboard'));
    }
}
