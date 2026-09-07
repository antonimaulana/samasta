<?php

namespace Tests\Feature;

use App\Models\Kelurahan;
use App\Models\Pemangkasan;
use App\Models\PemangkasanProgres;
use App\Models\Taman;
use App\Models\User;
use App\Support\PemangkasanProgresPdf;
use Database\Seeders\WilayahBatamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PemangkasanProgresPdfTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WilayahBatamSeeder::class);
    }

    public function test_admin_can_update_progress_entry(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        [$permohonan, $progres] = $this->createSampleProgress('Catatan awal.');

        $this->actingAs($admin)
            ->get(route('admin.pemangkasans.progres.edit', [
                'pemangkasan' => $permohonan,
                'pemangkasanProgres' => $progres,
            ]))
            ->assertOk()
            ->assertSee('Edit Progres Permohonan', false)
            ->assertSee('Catatan awal.', false);

        $payload = [
            'tanggal' => now()->format('Y-m-d\TH:i'),
            'jumlah_personil' => 8,
            'catatan' => 'Catatan diperbarui admin.',
        ];

        $this->actingAs($admin)
            ->put(route('admin.pemangkasans.progres.update', [
                'pemangkasan' => $permohonan,
                'pemangkasanProgres' => $progres,
            ]), $payload)
            ->assertRedirect(route('admin.pemangkasans.show', $permohonan))
            ->assertSessionHas('success');

        $progres->refresh();

        $this->assertSame(8, $progres->jumlah_personil);
        $this->assertSame('Catatan diperbarui admin.', $progres->catatan);
    }

    public function test_admin_can_export_daily_progress_pdf(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('PHP GD extension is required for PDF export.');
        }

        Storage::fake('public');

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        [$permohonan, $progres] = $this->createSampleProgress('Pemangkasan selesai hari ini.');

        $this->actingAs($admin)
            ->get(route('admin.pemangkasans.export-pdf-progres', [
                'pemangkasan' => $permohonan,
                'pemangkasanProgres' => $progres,
            ]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_pdf_for_day_one_uses_day_one_entry_not_day_two(): void
    {
        Storage::fake('public');

        $taman = Taman::create([
            'nama_taman' => 'Taman PDF Multi Hari',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => Kelurahan::query()->value('id'),
            'luasan' => 500,
            'alamat' => 'Alamat pdf multi hari',
            'deskripsi' => 'Deskripsi pdf multi hari.',
        ]);

        $permohonan = Pemangkasan::create([
            'jenis_layanan' => 'Pemangkasan Pohon',
            'taman_id' => $taman->id,
            'lokasi_pohon' => $taman->nama_taman,
            'asal' => 'Warga',
            'penanggungjawab' => 'Budi',
            'kontak_permohonan' => '081234567890',
            'tanggal_permohonan' => '2026-08-20',
            'kategori' => 'Laporan Masyarakat',
            'kondisi_sebelum' => '',
            'tanggal_eksekusi' => '2026-08-28',
            'tanggal_akhir_jadwal' => '2026-08-29',
            'total_hari' => 2,
            'pelaksana' => ['Tim Wilayah 1'],
            'status' => 'Diproses',
        ]);

        $dayTwo = PemangkasanProgres::create([
            'pemangkasan_id' => $permohonan->id,
            'tanggal' => '2026-08-29',
            'hari_ke' => 2,
            'jumlah_personil' => 9,
            'catatan' => 'Catatan khusus hari kedua.',
        ]);

        $dayOne = PemangkasanProgres::create([
            'pemangkasan_id' => $permohonan->id,
            'tanggal' => '2026-08-28',
            'hari_ke' => 1,
            'jumlah_personil' => 4,
            'catatan' => 'Catatan khusus hari pertama.',
        ]);

        $html = PemangkasanProgresPdf::render(
            PemangkasanProgresPdf::resolveEntry($permohonan, $dayOne),
            $permohonan->fresh(['taman']),
        );

        $this->assertStringContainsString('Catatan khusus hari pertama.', $html);
        $this->assertStringContainsString('4 orang', $html);
        $this->assertStringContainsString('1 / 2 hari (50% progres)', $html);
        $this->assertStringContainsString('Warga — Budi (081234567890)', $html);
        $this->assertStringContainsString('(Taman Kota)', $html);
        $this->assertStringNotContainsString('Jenis Layanan', $html);
        $this->assertStringNotContainsString('Penanggung Jawab</th>', $html);
        $this->assertStringNotContainsString('Catatan khusus hari kedua.', $html);
        $this->assertStringNotContainsString('9 orang', $html);

        $this->assertSame($dayOne->id, PemangkasanProgresPdf::resolveEntry($permohonan, $dayOne)->id);
        $this->assertSame($dayTwo->id, PemangkasanProgresPdf::resolveEntry($permohonan, $dayTwo)->id);
    }

    /**
     * @return array{0: Pemangkasan, 1: PemangkasanProgres}
     */
    private function createSampleProgress(string $catatan): array
    {
        $taman = Taman::create([
            'nama_taman' => 'Taman PDF Test',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => Kelurahan::query()->value('id'),
            'luasan' => 500,
            'alamat' => 'Alamat pdf test',
            'deskripsi' => 'Deskripsi pdf test.',
        ]);

        $permohonan = Pemangkasan::create([
            'jenis_layanan' => 'Pemangkasan Pohon',
            'taman_id' => $taman->id,
            'lokasi_pohon' => $taman->nama_taman,
            'asal' => 'Warga',
            'penanggungjawab' => 'Budi',
            'kontak_permohonan' => '081234567890',
            'tanggal_permohonan' => '2026-08-20',
            'kategori' => 'Laporan Masyarakat',
            'kondisi_sebelum' => '',
            'tanggal_eksekusi' => '2026-08-28',
            'tanggal_akhir_jadwal' => '2026-08-28',
            'total_hari' => 1,
            'pelaksana' => ['Tim Wilayah 1'],
            'status' => 'Diproses',
        ]);

        $fotoPaths = [];
        foreach (array_keys(\App\Models\PemeliharaanTaman::FOTO_FIELDS) as $field) {
            $fotoPaths[$field] = UploadedFile::fake()->image($field.'.jpg')->store('pemangkasan/progres', 'public');
        }

        $progres = PemangkasanProgres::create(array_merge([
            'pemangkasan_id' => $permohonan->id,
            'tanggal' => '2026-08-28',
            'hari_ke' => 1,
            'jumlah_personil' => 4,
            'catatan' => $catatan,
        ], $fotoPaths));

        return [$permohonan, $progres];
    }
}
