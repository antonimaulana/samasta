<?php

namespace Tests\Feature;

use App\Models\Bibit;
use App\Models\BibitKeluar;
use App\Models\BibitMasuk;
use App\Models\Kelurahan;
use App\Models\Taman;
use App\Models\User;
use Database\Seeders\WilayahBatamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BibitStokFotoShowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(WilayahBatamSeeder::class);
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

        $masuk = BibitMasuk::create([
            'bibit_id' => $bibit->id,
            'jumlah' => 10,
            'sisa_stok' => 10,
            'tanggal_masuk' => now()->toDateString(),
            'sumber' => 'Produksi',
            'status_siap_tanam' => true,
            'foto' => 'bibit-masuk/bukti.jpg',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.bibit-masuks.show', $masuk))
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

        $keluar = BibitKeluar::create([
            'bibit_id' => $bibit->id,
            'jumlah' => 5,
            'tanggal_keluar' => now()->toDateString(),
            'peruntukan' => 'Penanaman Awal',
            'taman_id' => $taman->id,
            'foto' => 'bibit-keluar/bukti.jpg',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.bibit-keluars.show', $keluar))
            ->assertOk()
            ->assertSee('Foto bukti', false)
            ->assertSee('bibit-keluar/bukti.jpg', false);
    }
}
