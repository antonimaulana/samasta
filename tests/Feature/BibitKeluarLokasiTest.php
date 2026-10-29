<?php

namespace Tests\Feature;

use App\Models\Bibit;
use App\Models\BibitKeluar;
use App\Models\BibitKeluarBatch;
use App\Models\BibitMasuk;
use App\Models\Taman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BibitKeluarLokasiTest extends TestCase
{
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
        BibitMasuk::create([
            'bibit_id' => $bibit->id,
            'jumlah' => 10,
            'sisa_stok' => 10,
            'tanggal_masuk' => now()->toDateString(),
            'sumber' => 'Produksi',
            'status_siap_tanam' => true,
            'foto' => 'bibit-masuk/test.jpg',
        ]);

        $response = $this->actingAs($operator)->post(route('admin.bibit-keluars.store'), [
            'tanggal_keluar' => now()->toDateString(),
            'peruntukan' => 'Permintaan Masyarakat/Instansi',
            'lokasi_luar' => '1',
            'lokasi' => 'Kantor Kelurahan Sei Jodoh',
            'items' => [
                ['bibit_id' => $bibit->id, 'jumlah' => 3],
            ],
            'foto' => UploadedFile::fake()->image('bukti.jpg'),
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
        BibitMasuk::create([
            'bibit_id' => $bibit->id,
            'jumlah' => 5,
            'sisa_stok' => 5,
            'tanggal_masuk' => now()->toDateString(),
            'sumber' => 'Produksi',
            'status_siap_tanam' => true,
            'foto' => 'bibit-masuk/test.jpg',
        ]);
        $taman = Taman::create([
            'nama_taman' => 'Taman Keluar Bibit',
            'kategori' => 'Taman Kota',
            'luasan' => 500,
            'alamat' => 'Jl. Test No. 1',
            'deskripsi' => 'Taman uji stok keluar.',
        ]);

        $this->actingAs($operator)->post(route('admin.bibit-keluars.store'), [
            'tanggal_keluar' => now()->toDateString(),
            'peruntukan' => 'Penanaman Awal',
            'taman_id' => $taman->id,
            'items' => [
                ['bibit_id' => $bibit->id, 'jumlah' => 2],
            ],
            'foto' => UploadedFile::fake()->image('bukti.jpg'),
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
        foreach ([$bibitA, $bibitB] as $bibit) {
            BibitMasuk::create([
                'bibit_id' => $bibit->id,
                'jumlah' => $bibit->stok_tersedia,
                'sisa_stok' => $bibit->stok_tersedia,
                'tanggal_masuk' => now()->toDateString(),
                'sumber' => 'Produksi',
                'status_siap_tanam' => true,
                'foto' => 'bibit-masuk/test.jpg',
            ]);
        }
        $taman = Taman::create([
            'nama_taman' => 'Taman Multi Bibit',
            'kategori' => 'Taman Kota',
            'luasan' => 300,
            'alamat' => 'Jl. Multi',
            'deskripsi' => 'Uji multi bibit.',
        ]);

        $this->actingAs($operator)->post(route('admin.bibit-keluars.store'), [
            'tanggal_keluar' => now()->toDateString(),
            'peruntukan' => 'Penanaman Awal',
            'taman_id' => $taman->id,
            'items' => [
                ['bibit_id' => $bibitA->id, 'jumlah' => 2],
                ['bibit_id' => $bibitB->id, 'jumlah' => 3],
            ],
            'foto' => UploadedFile::fake()->image('bukti.jpg'),
        ])->assertRedirect(route('admin.bibit-keluars.index'));

        $this->assertSame(1, BibitKeluarBatch::count());
        $this->assertSame(2, BibitKeluar::count());
        $this->assertSame(2, $bibitA->fresh()->stok_tersedia);
        $this->assertSame(3, $bibitB->fresh()->stok_tersedia);
    }
}
