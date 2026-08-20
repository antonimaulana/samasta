<?php

namespace Database\Seeders;

use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Support\WilayahKotaBatam;
use Illuminate\Database\Seeder;

class WilayahBatamSeeder extends Seeder
{
    public function run(): void
    {
        foreach (WilayahKotaBatam::kecamatan() as $data) {
            $kecamatan = Kecamatan::query()->updateOrCreate(
                ['nama' => $data['nama']],
                ['kode_kemendagri' => $data['kode']],
            );

            foreach ($data['kelurahan'] as $namaKelurahan) {
                Kelurahan::query()->updateOrCreate(
                    [
                        'kecamatan_id' => $kecamatan->id,
                        'nama' => $namaKelurahan,
                    ],
                );
            }
        }
    }
}
