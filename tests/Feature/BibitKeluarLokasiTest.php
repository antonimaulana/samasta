<?php

namespace Tests\Feature;

use App\Models\Bibit;
use App\Models\BibitKeluar;
use App\Models\BibitKeluarBatch;
use App\Models\Taman;
use App\Models\User;
use App\Support\OperasionalPelaksanaanTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CreatesBibitStokFixtures;
use Tests\TestCase;

class BibitKeluarLokasiTest extends TestCase
{
    use CreatesBibitStokFixtures;
    use RefreshDatabase;

    public function test_store_stok_keluar_with_manual_lokasi_and_permintaan_masyarakat(): void
    {
        Storage::fake('public');

        $operator = User::factory()->create(['role' => User::ROLE_OPERATOR]);
        $bibit = Bibit::create([
            'nama_tanaman' => 'Mangga',
            'jenis' => 'Pohon Buah',
            'stok_tersedia' => 10,
            'sumber_bibit' => 'Produksi',
            'status_siap_tanam' => true,
        ]);
        $this->createBibitMasukLine($bibit, 10);

        $response = $this->actingAs($operator)->post(route('admin.bibit-keluars.store'), [
            'tanggal_keluar' => OperasionalPelaksanaanTime::inputValue(now()),
            'peruntukan' => 'Permintaan Masyarakat/Instansi',
            'lokasi_luar' => '1',
            'lokasi' => 'Kantor Kelurahan Sei Jodoh',
            'items' => [
                ['bibit_id' => $bibit->id, 'jumlah' => 3],
            ],
            'fotos' => [UploadedFile::fake()->image('bukti.jpg')],
            'dokumen_pendukung' => UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'),
        ]);

        $response->assertRedirect(route('admin.bibit-keluars.index'));
        $response->assertSessionHas('success');

        $batch = BibitKeluarBatch::query()->first();
        $this->assertNotNull($batch);
        $this->assertSame('Permintaan Masyarakat/Instansi', $batch->peruntukan);
        $this->assertNull($batch->taman_id);
        $this->assertSame('Kantor Kelurahan Sei Jodoh', $batch->lokasi);
        $this->assertSame(1, $batch->items()->count());
        $this->assertSame(7, $bibit->fresh()->stok_tersedia);
        $this->assertTrue($batch->hasFotoBukti());
        $this->assertNotNull($batch->dokumen_pendukung);
    }

    public function test_store_stok_keluar_with_taman_sets_lokasi_label(): void
    {
        Storage::fake('public');

        $operator = User::factory()->create(['role' => User::ROLE_OPERATOR]);
        $bibit = Bibit::create([
            'nama_tanaman' => 'Flamboyan',
            'jenis' => 'Pohon Pelindung / Peneduh',
            'stok_tersedia' => 5,
            'sumber_bibit' => 'Produksi',
            'status_siap_tanam' => true,
        ]);
        $this->createBibitMasukLine($bibit, 5);
        $taman = Taman::create([
            'nama_taman' => 'Taman Keluar Bibit',
            'kategori' => 'Taman Kota',
            'luasan' => 500,
            'alamat' => 'Jl. Test No. 1',
            'deskripsi' => 'Taman uji stok keluar.',
        ]);

        $this->actingAs($operator)->post(route('admin.bibit-keluars.store'), [
            'tanggal_keluar' => OperasionalPelaksanaanTime::inputValue(now()),
            'peruntukan' => 'Penanaman Awal',
            'taman_id' => $taman->id,
            'items' => [
                ['bibit_id' => $bibit->id, 'jumlah' => 2],
            ],
            'fotos' => [UploadedFile::fake()->image('bukti.jpg')],
        ])->assertRedirect(route('admin.bibit-keluars.index'));

        $batch = BibitKeluarBatch::query()->first();
        $this->assertSame($taman->id, $batch->taman_id);
        $this->assertSame('Taman Keluar Bibit — Jl. Test No. 1', $batch->lokasi);
    }

    public function test_store_stok_keluar_with_multiple_bibit_in_one_transaction(): void
    {
        Storage::fake('public');

        $operator = User::factory()->create(['role' => User::ROLE_OPERATOR]);
        $bibitA = Bibit::create([
            'nama_tanaman' => 'Anggrek',
            'jenis' => 'Tanaman Hias',
            'stok_tersedia' => 4,
            'sumber_bibit' => 'Produksi',
            'status_siap_tanam' => true,
        ]);
        $bibitB = Bibit::create([
            'nama_tanaman' => 'Melati',
            'jenis' => 'Tanaman Hias',
            'stok_tersedia' => 6,
            'sumber_bibit' => 'Produksi',
            'status_siap_tanam' => true,
        ]);
        $this->createBibitMasukLine($bibitA, 4);
        $this->createBibitMasukLine($bibitB, 6);
        $taman = Taman::create([
            'nama_taman' => 'Taman Multi Bibit',
            'kategori' => 'Taman Kota',
            'luasan' => 300,
            'alamat' => 'Jl. Multi',
            'deskripsi' => 'Uji multi bibit.',
        ]);

        $this->actingAs($operator)->post(route('admin.bibit-keluars.store'), [
            'tanggal_keluar' => OperasionalPelaksanaanTime::inputValue(now()),
            'peruntukan' => 'Penanaman Awal',
            'taman_id' => $taman->id,
            'items' => [
                ['bibit_id' => $bibitA->id, 'jumlah' => 2],
                ['bibit_id' => $bibitB->id, 'jumlah' => 3],
            ],
            'fotos' => [UploadedFile::fake()->image('bukti.jpg')],
        ])->assertRedirect(route('admin.bibit-keluars.index'));

        $this->assertSame(1, BibitKeluarBatch::count());
        $this->assertSame(2, BibitKeluar::count());
        $this->assertSame(2, $bibitA->fresh()->stok_tersedia);
        $this->assertSame(3, $bibitB->fresh()->stok_tersedia);
    }
}
