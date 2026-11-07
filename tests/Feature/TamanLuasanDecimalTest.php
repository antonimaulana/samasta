<?php

namespace Tests\Feature;

use App\Models\Kelurahan;
use App\Models\Taman;
use App\Models\User;
use Database\Seeders\WilayahBatamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TamanLuasanDecimalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WilayahBatamSeeder::class);
    }

    public function test_admin_can_store_taman_with_decimal_luasan(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $payload = [
            'nama_taman' => 'RTH Luasan Desimal',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => Kelurahan::query()->value('id'),
            'luasan' => '1234,75',
            'alamat' => 'Jl. Uji',
            'deskripsi' => 'Deskripsi.',
        ];

        $this->actingAs($admin)
            ->post(route('admin.tamans.store'), $payload)
            ->assertRedirect();

        $taman = Taman::query()->where('nama_taman', 'RTH Luasan Desimal')->firstOrFail();

        $this->assertEqualsWithDelta(1234.75, (float) $taman->luasan, 0.001);
    }

    public function test_admin_can_update_taman_with_decimal_luasan(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $taman = Taman::create([
            'nama_taman' => 'RTH Awal',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => Kelurahan::query()->value('id'),
            'luasan' => 1000,
            'alamat' => 'Alamat',
            'deskripsi' => 'Deskripsi.',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.tamans.update', $taman), [
                'nama_taman' => $taman->nama_taman,
                'kategori' => $taman->kategori,
                'kelurahan_id' => $taman->kelurahan_id,
                'luasan' => '99.5',
                'alamat' => $taman->alamat,
                'deskripsi' => $taman->deskripsi,
            ])
            ->assertRedirect();

        $this->assertEqualsWithDelta(99.5, (float) $taman->fresh()->luasan, 0.001);
    }
}
