<?php

namespace Tests\Feature;

use App\Models\Kelurahan;
use App\Models\Taman;
use App\Models\User;
use Database\Seeders\WilayahBatamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TamanIndexSortTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(WilayahBatamSeeder::class);
    }

    public function test_index_can_sort_by_nama_ascending(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $kelurahanId = Kelurahan::query()->value('id');

        Taman::create([
            'nama_taman' => 'Zebra Park',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 1000,
            'alamat' => 'Alamat Z',
            'deskripsi' => 'Deskripsi Z',
        ]);
        Taman::create([
            'nama_taman' => 'Alpha Park',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 1000,
            'alamat' => 'Alamat A',
            'deskripsi' => 'Deskripsi A',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.tamans.index', ['sort' => 'nama', 'direction' => 'asc']))
            ->assertOk()
            ->assertSeeInOrder(['Alpha Park', 'Zebra Park']);
    }

    public function test_index_can_sort_by_luasan_descending(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $kelurahanId = Kelurahan::query()->value('id');

        Taman::create([
            'nama_taman' => 'Taman Kecil',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 500,
            'alamat' => 'Alamat kecil',
            'deskripsi' => 'Deskripsi kecil',
        ]);
        Taman::create([
            'nama_taman' => 'Taman Besar',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 9000,
            'alamat' => 'Alamat besar',
            'deskripsi' => 'Deskripsi besar',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.tamans.index', ['sort' => 'luasan', 'direction' => 'desc']))
            ->assertOk()
            ->assertSeeInOrder(['Taman Besar', 'Taman Kecil']);
    }

    public function test_index_defaults_to_kategori_then_nama(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $kelurahanId = Kelurahan::query()->value('id');

        Taman::create([
            'nama_taman' => 'Zebra Lingkungan',
            'kategori' => 'Taman Lingkungan',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 1000,
            'alamat' => 'Alamat lingkungan',
            'deskripsi' => 'Deskripsi lingkungan',
        ]);
        Taman::create([
            'nama_taman' => 'Beta Kota',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 1000,
            'alamat' => 'Alamat kota',
            'deskripsi' => 'Deskripsi kota',
        ]);
        Taman::create([
            'nama_taman' => 'Alpha Kota',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 1000,
            'alamat' => 'Alamat kota A',
            'deskripsi' => 'Deskripsi kota A',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.tamans.index'))
            ->assertOk()
            ->assertSeeInOrder(['Alpha Kota', 'Beta Kota', 'Zebra Lingkungan']);
    }
}
