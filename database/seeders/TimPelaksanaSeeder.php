<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Produksi: tim kerja diinput lewat Admin → Sistem → Tim Kerja.
 * PHPUnit: otomatis memuat data contoh via TimPelaksanaSampleSeeder.
 */
class TimPelaksanaSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('testing')) {
            $this->call(TimPelaksanaSampleSeeder::class);
        }
    }
}
