<?php

namespace Tests\Feature;

use App\Models\Bibit;
use App\Models\BibitKeluar;
use App\Models\BibitMasuk;
use App\Models\Taman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BibitTransaksiDeleteTest extends TestCase
{
    use RefreshDatabase;

    private function createBibit(int $stok = 100): Bibit
    {
        return Bibit::create([
            'nama_tanaman' => 'Pohon Test',
            'jenis' => 'Pohon Pelindung / Peneduh',
            'stok_tersedia' => $stok,
            'sumber_bibit' => 'Produksi',
            'status_siap_tanam' => true,
        ]);
    }

    private function createMasuk(Bibit $bibit, int $jumlah, ?int $sisaStok = null): BibitMasuk
    {
        return BibitMasuk::create([
            'bibit_id' => $bibit->id,
            'jumlah' => $jumlah,
            'sisa_stok' => $sisaStok ?? $jumlah,
            'tanggal_masuk' => now()->toDateString(),
            'sumber' => 'Produksi',
            'status_siap_tanam' => true,
            'foto' => 'bibit-masuk/test.jpg',
        ]);
    }

    private function createKeluar(Bibit $bibit, Taman $taman, int $jumlah): BibitKeluar
    {
        return BibitKeluar::create([
            'bibit_id' => $bibit->id,
            'jumlah' => $jumlah,
            'tanggal_keluar' => now()->toDateString(),
            'peruntukan' => 'Penanaman Awal',
            'taman_id' => $taman->id,
            'foto' => 'bibit-keluar/test.jpg',
        ]);
    }

    public function test_operator_can_delete_unused_bibit_masuk_and_restore_stock(): void
    {
        $operator = User::factory()->create(['role' => User::ROLE_OPERATOR]);
        $bibit = $this->createBibit(50);
        $masuk = $this->createMasuk($bibit, 50);

        $this->actingAs($operator)
            ->from(route('admin.bibit-masuks.index'))
            ->delete(route('admin.bibit-masuks.destroy', $masuk))
            ->assertRedirect(route('admin.bibit-masuks.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('bibit_masuks', ['id' => $masuk->id]);
        $this->assertSame(0, $bibit->fresh()->stok_tersedia);
    }

    public function test_operator_cannot_delete_bibit_masuk_when_batch_partially_used(): void
    {
        $operator = User::factory()->create(['role' => User::ROLE_OPERATOR]);
        $bibit = $this->createBibit(70);
        $masuk = $this->createMasuk($bibit, 100, 70);

        $this->actingAs($operator)
            ->from(route('admin.bibit-masuks.index'))
            ->delete(route('admin.bibit-masuks.destroy', $masuk))
            ->assertRedirect(route('admin.bibit-masuks.index'))
            ->assertSessionHasErrors('delete');

        $this->assertDatabaseHas('bibit_masuks', ['id' => $masuk->id]);
        $this->assertSame(70, $bibit->fresh()->stok_tersedia);
    }

    public function test_operator_can_delete_bibit_keluar_and_restore_stock(): void
    {
        $operator = User::factory()->create(['role' => User::ROLE_OPERATOR]);
        $bibit = $this->createBibit(30);
        $masuk = $this->createMasuk($bibit, 100, 70);
        $taman = Taman::create([
            'nama_taman' => 'Taman Bibit Test',
            'kategori' => 'Taman Kota',
            'luasan' => 1000,
            'alamat' => 'Alamat test bibit keluar',
            'deskripsi' => 'Deskripsi taman untuk test hapus stok keluar.',
        ]);
        $keluar = $this->createKeluar($bibit, $taman, 30);

        $this->actingAs($operator)
            ->from(route('admin.bibit-keluars.index'))
            ->delete(route('admin.bibit-keluars.destroy', $keluar))
            ->assertRedirect(route('admin.bibit-keluars.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('bibit_keluars', ['id' => $keluar->id]);
        $this->assertSame(60, $bibit->fresh()->stok_tersedia);
        $this->assertSame(100, $masuk->fresh()->sisa_stok);
    }

    public function test_viewer_cannot_delete_bibit_transaksi(): void
    {
        $viewer = User::factory()->create(['role' => User::ROLE_VIEWER]);
        $bibit = $this->createBibit(20);
        $masuk = $this->createMasuk($bibit, 20);

        $this->actingAs($viewer)
            ->delete(route('admin.bibit-masuks.destroy', $masuk))
            ->assertForbidden();

        $this->assertDatabaseHas('bibit_masuks', ['id' => $masuk->id]);
    }
}
