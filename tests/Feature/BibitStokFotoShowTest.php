<?php

namespace Tests\Feature;

use App\Models\Bibit;
use App\Models\BibitKeluarBatch;
use App\Models\BibitMasukBatch;
use App\Models\Kelurahan;
use App\Models\Taman;
use App\Models\User;
use Database\Seeders\WilayahBatamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CreatesBibitStokFixtures;
use Tests\TestCase;

class BibitStokFotoShowTest extends TestCase
{
    use CreatesBibitStokFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(WilayahBatamSeeder::class);
    }

    public function test_bibit_show_page_lists_masuk_and_keluar_without_error(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $bibit = Bibit::create([
            'nama_tanaman' => 'Flamboyan',
            'jenis' => 'Pohon Rindang',
            'stok_tersedia' => 5,
            'sumber_bibit' => 'Produksi',
            'status_siap_tanam' => true,
        ]);

        $this->createBibitMasukLine($bibit, 5);

        $this->actingAs($admin)
            ->get(route('admin.bibits.show', $bibit))
            ->assertOk()
            ->assertSee('Flamboyan')
            ->assertSee('Riwayat Stok Masuk');
    }

    public function test_stok_masuk_show_displays_foto_bukti_when_file_exists(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('bibit-masuk/bukti.jpg', 'fake-image');

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $bibit = Bibit::create([
            'nama_tanaman' => 'Mangga',
            'jenis' => 'Pohon Buah',
            'stok_tersedia' => 10,
            'sumber_bibit' => 'Produksi',
            'status_siap_tanam' => true,
        ]);

        $masuk = $this->createBibitMasukLine($bibit, 10);
        $batch = $masuk->batch;
        $batch->buktiFotos()->create([
            'path' => 'bibit-masuk/bukti.jpg',
            'urutan' => 1,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.bibit-masuks.show', $batch))
            ->assertOk()
            ->assertSee('Foto bukti', false)
            ->assertSee('bibit-masuk/bukti.jpg', false);
    }

    public function test_stok_keluar_show_displays_foto_bukti_when_file_exists(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('bibit-keluar/bukti.jpg', 'fake-image');

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $kelurahanId = Kelurahan::query()->value('id');
        $taman = Taman::create([
            'nama_taman' => 'Taman Foto Keluar',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 1000,
            'alamat' => 'Alamat',
            'deskripsi' => 'Deskripsi.',
        ]);
        $bibit = Bibit::create([
            'nama_tanaman' => 'Flamboyan',
            'jenis' => 'Pohon Pelindung / Peneduh',
            'stok_tersedia' => 5,
            'sumber_bibit' => 'Produksi',
            'status_siap_tanam' => true,
        ]);

        $batch = BibitKeluarBatch::create([
            'tanggal_keluar' => now()->format('Y-m-d H:i:s'),
            'peruntukan' => 'Penanaman Awal',
            'taman_id' => $taman->id,
            'lokasi' => $taman->nama_taman,
        ]);
        $batch->items()->create([
            'bibit_id' => $bibit->id,
            'jumlah' => 5,
        ]);
        $batch->buktiFotos()->create([
            'path' => 'bibit-keluar/bukti.jpg',
            'urutan' => 1,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.bibit-keluars.show', $batch))
            ->assertOk()
            ->assertSee('Foto bukti', false)
            ->assertSee('bibit-keluar/bukti.jpg', false);
    }
}
