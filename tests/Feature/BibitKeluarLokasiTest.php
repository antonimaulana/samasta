<?php

namespace Tests\Feature;

use App\Models\Bibit;
use App\Models\BibitKeluar;
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
            'bibit_id' => $bibit->id,
            'jumlah' => 3,
            'tanggal_keluar' => now()->toDateString(),
            'peruntukan' => 'Permintaan Masyarakat/Instansi',
            'lokasi_luar' => '1',
            'lokasi' => 'Kantor Kelurahan Sei Jodoh',
            'foto' => UploadedFile::fake()->image('bukti.jpg'),
        ]);

        $response->assertRedirect(route('admin.bibit-keluars.index'));
        $response->assertSessionHas('success');

        $keluar = BibitKeluar::query()->first();
        $this->assertNotNull($keluar);
        $this->assertSame('Permintaan Masyarakat/Instansi', $keluar->peruntukan);
        $this->assertNull($keluar->taman_id);
        $this->assertSame('Kantor Kelurahan Sei Jodoh', $keluar->lokasi);
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
            'bibit_id' => $bibit->id,
            'jumlah' => 2,
            'tanggal_keluar' => now()->toDateString(),
            'peruntukan' => 'Penanaman Awal',
            'taman_id' => $taman->id,
            'foto' => UploadedFile::fake()->image('bukti.jpg'),
        ])->assertRedirect(route('admin.bibit-keluars.index'));

        $keluar = BibitKeluar::query()->first();
        $this->assertSame($taman->id, $keluar->taman_id);
        $this->assertSame('Taman Keluar Bibit — Jl. Test No. 1', $keluar->lokasi);
    }
}
