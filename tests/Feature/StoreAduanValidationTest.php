<?php

namespace Tests\Feature;

use App\Models\Taman;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class StoreAduanValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_aduan_requires_taman_foto_and_gps(): void
    {
        $response = $this->post(route('aduan.store'), [
            'jenis_aduan' => 'Kondisi Taman Rusak',
            'deskripsi' => 'Deskripsi aduan yang cukup panjang untuk validasi.',
            'nama_pelapor' => 'Pelapor Test',
            'kontak_pelapor' => '081234567890',
        ]);

        $response->assertSessionHasErrors(['taman_id', 'foto', 'latitude', 'longitude']);
    }

    public function test_aduan_can_be_submitted_with_required_fields(): void
    {
        $taman = Taman::create([
            'nama_taman' => 'Taman Aduan Test',
            'kategori' => 'Taman Kota',
            'luasan' => 1000,
            'alamat' => 'Alamat test aduan',
            'deskripsi' => 'Deskripsi taman untuk test aduan wajib.',
        ]);

        $response = $this->post(route('aduan.store'), [
            'taman_id' => $taman->id,
            'jenis_aduan' => 'Kondisi Taman Rusak',
            'deskripsi' => 'Deskripsi aduan yang cukup panjang untuk validasi.',
            'foto' => UploadedFile::fake()->image('bukti.jpg'),
            'latitude' => -1.08286000,
            'longitude' => 104.03050000,
            'nama_pelapor' => 'Pelapor Test',
            'kontak_pelapor' => '081234567890',
        ]);

        $response->assertRedirect(route('aduan.success'));

        $this->assertDatabaseHas('aduan_masyarakats', [
            'taman_id' => $taman->id,
            'lokasi' => 'Taman Aduan Test — Alamat test aduan',
        ]);
    }
}
