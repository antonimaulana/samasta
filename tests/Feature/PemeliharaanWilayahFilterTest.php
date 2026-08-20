<?php

namespace Tests\Feature;

use App\Models\Kelurahan;
use App\Models\PemeliharaanTaman;
use App\Models\Taman;
use App\Models\TimPelaksana;
use App\Models\User;
use App\Support\TimPelaksanaResolver;
use Database\Seeders\TimPelaksanaSeeder;
use Database\Seeders\WilayahBatamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PemeliharaanWilayahFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(WilayahBatamSeeder::class);
        $this->seed(TimPelaksanaSeeder::class);
    }

    public function test_create_form_includes_tim_wilayah_mapping(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Taman::create([
            'nama_taman' => 'Taman Filter Test',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => Kelurahan::query()->value('id'),
            'luasan' => 400,
            'alamat' => 'Alamat filter test',
            'deskripsi' => 'Deskripsi test form pemeliharaan filter wilayah.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.pemeliharaan-tamans.create'))
            ->assertOk()
            ->assertSee('applyWilayahFilter', false)
            ->assertSee('Lokasi di luar wilayah pemeliharaan');
    }

    public function test_store_accepts_manual_lokasi_outside_taman_data(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $payload = [
            'tanggal' => now()->toDateString(),
            'tim' => 'Tim Wilayah 1',
            'lokasi_luar' => '1',
            'lokasi_pelaksanaan' => 'Jl. Manual Lokasi, Batam Center',
            'uraian_pekerjaan' => 'Test lokasi manual',
        ];

        foreach (array_keys(\App\Models\PemeliharaanTaman::FOTO_FIELDS) as $field) {
            $payload[$field] = UploadedFile::fake()->image($field.'.jpg');
        }

        $this->actingAs($admin)
            ->post(route('admin.pemeliharaan-tamans.store'), $payload)
            ->assertRedirect(route('admin.pemeliharaan-tamans.index', [
                'tanggal_mulai' => $payload['tanggal'],
                'tanggal_selesai' => $payload['tanggal'],
            ]));

        $this->assertDatabaseHas('pemeliharaan_tamans', [
            'lokasi_pelaksanaan' => 'Jl. Manual Lokasi, Batam Center',
            'taman_id' => null,
        ]);
    }

    public function test_store_rejects_taman_outside_team_wilayah(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $team1Kelurahan = TimPelaksana::where('nama', 'Tim Wilayah 1')->first()->kelurahans()->first();
        $team2Kelurahan = TimPelaksana::where('nama', 'Tim Wilayah 2')->first()->kelurahans()->first();

        $this->assertNotNull($team1Kelurahan);
        $this->assertNotNull($team2Kelurahan);

        $taman = Taman::create([
            'nama_taman' => 'Taman Wilayah 2',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $team2Kelurahan->id,
            'luasan' => 500,
            'alamat' => 'Alamat test wilayah',
            'deskripsi' => 'Deskripsi test pemeliharaan wilayah filter.',
        ]);

        $payload = [
            'tanggal' => now()->toDateString(),
            'tim' => 'Tim Wilayah 1',
            'taman_id' => $taman->id,
            'uraian_pekerjaan' => 'Test',
        ];

        foreach (array_keys(\App\Models\PemeliharaanTaman::FOTO_FIELDS) as $field) {
            $payload[$field] = UploadedFile::fake()->image($field.'.jpg');
        }

        $this->actingAs($admin)
            ->post(route('admin.pemeliharaan-tamans.store'), $payload)
            ->assertSessionHasErrors('taman_id');
    }

    public function test_resolver_allows_nursery_team_any_taman(): void
    {
        $kelurahan = Kelurahan::query()->first();

        $taman = Taman::create([
            'nama_taman' => 'Taman Nursery Test',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahan->id,
            'luasan' => 300,
            'alamat' => 'Alamat nursery',
            'deskripsi' => 'Deskripsi test tim nursery tanpa wilayah.',
        ]);

        $this->assertTrue(
            app(TimPelaksanaResolver::class)->tamanAllowedForTeam('Tim Nursery', $taman->id)
        );
    }

    public function test_store_tim_armada_requires_armada_rows(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $dumpTruck = \App\Models\AlatSaranaOperasional::create([
            'nama' => 'Dump Truck BP 1234 AB',
            'jenis' => 'Dump Truck',
            'no_plat' => 'BP 1234 AB',
            'sopir' => 'Budi',
            'jumlah' => 1,
            'peruntukan' => 'Tim Armada',
            'kondisi' => 'Baik',
        ]);
        $crane = \App\Models\AlatSaranaOperasional::create([
            'nama' => 'Crane BP 5678 CD',
            'jenis' => 'Crane',
            'no_plat' => 'BP 5678 CD',
            'sopir' => 'Andi',
            'jumlah' => 1,
            'peruntukan' => 'Tim Armada',
            'kondisi' => 'Baik',
        ]);

        $payload = [
            'tanggal' => now()->toDateString(),
            'tim' => 'Tim Armada',
            'lokasi_luar' => '1',
            'lokasi_pelaksanaan' => 'Depo Armada',
            'jumlah_personil' => 4,
            'hari_ke' => 1,
            'total_hari' => 3,
            'persentase_progres' => 25,
            'uraian_pekerjaan' => 'Pengangkutan material',
        ];

        foreach (array_keys(\App\Models\PemeliharaanTaman::FOTO_FIELDS) as $field) {
            $payload[$field] = UploadedFile::fake()->image($field.'.jpg');
        }

        $this->actingAs($admin)
            ->post(route('admin.pemeliharaan-tamans.store'), $payload)
            ->assertSessionHasErrors('armada');

        $payload['armada'] = [
            ['alat_sarana_operasional_id' => $dumpTruck->id, 'sopir' => 'Misriwahyudi'],
            ['alat_sarana_operasional_id' => $crane->id, 'sopir' => 'Darmani'],
        ];

        $this->actingAs($admin)
            ->post(route('admin.pemeliharaan-tamans.store'), $payload)
            ->assertRedirect();

        $record = \App\Models\PemeliharaanTaman::query()->where('lokasi_pelaksanaan', 'Depo Armada')->first();
        $this->assertNotNull($record);
        $this->assertSame(4, $record->jumlah_personil);
        $this->assertSame('Pengangkutan material', $record->uraian_pekerjaan);
        $this->assertCount(2, $record->armadas);
        $this->assertDatabaseHas('pemeliharaan_taman_armadas', [
            'pemeliharaan_taman_id' => $record->id,
            'alat_sarana_operasional_id' => $dumpTruck->id,
            'jenis_armada' => 'Dump Truck',
            'sopir' => 'Misriwahyudi',
        ]);
    }

    public function test_pdf_labels_use_pengawas_kategori_and_combined_progress(): void
    {
        $taman = Taman::create([
            'nama_taman' => 'Taman PDF Label',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => Kelurahan::query()->value('id'),
            'luasan' => 500,
            'alamat' => 'Alamat pdf label',
            'deskripsi' => 'Deskripsi test label pdf pemeliharaan.',
        ]);

        $record = \App\Models\PemeliharaanTaman::create([
            'tanggal' => now()->toDateString(),
            'tim' => 'Tim Wilayah 1',
            'taman_id' => $taman->id,
            'lokasi_pelaksanaan' => PemeliharaanTaman::lokasiLabelFromTaman($taman),
            'hari_ke' => 2,
            'total_hari' => 5,
            'persentase_progres' => 40,
        ]);

        $record->load('taman');

        $this->assertSame('Maryono', $record->namaPengawas());
        $this->assertSame('(Taman Kota) '.$record->lokasi_pelaksanaan, $record->lokasiPelaksanaanPdfLabel());
        $this->assertSame('2 / 5 hari (40% progres)', $record->progressSummaryLabel());

        $manual = \App\Models\PemeliharaanTaman::create([
            'tanggal' => now()->toDateString(),
            'tim' => 'Tim Armada',
            'taman_id' => null,
            'lokasi_pelaksanaan' => 'Jl. Permintaan Warga',
        ]);

        $this->assertSame(
            '(Permintaan Masyarakat) Jl. Permintaan Warga',
            $manual->lokasiPelaksanaanPdfLabel()
        );
    }
}
