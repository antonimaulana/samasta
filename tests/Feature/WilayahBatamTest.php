<?php

namespace Tests\Feature;

use App\Models\Kecamatan;
use App\Models\Kelurahan;
use Database\Seeders\WilayahBatamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WilayahBatamTest extends TestCase
{
    use RefreshDatabase;

    public function test_wilayah_seeder_creates_12_kecamatan_and_64_kelurahan(): void
    {
        $this->seed(WilayahBatamSeeder::class);

        $this->assertSame(12, Kecamatan::count());
        $this->assertSame(64, Kelurahan::count());
        $this->assertDatabaseHas('kecamatans', ['nama' => 'Batam Kota']);
        $this->assertDatabaseHas('kelurahans', ['nama' => 'Belian']);
    }
}
