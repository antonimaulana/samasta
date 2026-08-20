<?php

namespace Database\Seeders;

use App\Models\Kelurahan;
use App\Models\TimPelaksana;
use Illuminate\Database\Seeder;

class TimPelaksanaSeeder extends Seeder
{
    public function run(): void
    {
        $teams = [
            ['nama' => 'Tim Wilayah 1', 'nama_pengawas' => 'Maryono', 'memiliki_wilayah_kerja' => true, 'urutan' => 1],
            ['nama' => 'Tim Wilayah 2', 'nama_pengawas' => 'Muhammad Rifan', 'memiliki_wilayah_kerja' => true, 'urutan' => 2],
            ['nama' => 'Tim Wilayah 3', 'nama_pengawas' => 'Syaiful', 'memiliki_wilayah_kerja' => true, 'urutan' => 3],
            ['nama' => 'Tim Wilayah 4', 'nama_pengawas' => 'Abdul Setio', 'memiliki_wilayah_kerja' => true, 'urutan' => 4],
            ['nama' => 'Tim Nursery', 'nama_pengawas' => 'Marsis', 'memiliki_wilayah_kerja' => false, 'urutan' => 5],
            ['nama' => 'Tim Armada', 'nama_pengawas' => 'Munasir', 'memiliki_wilayah_kerja' => false, 'urutan' => 6],
        ];

        foreach ($teams as $team) {
            TimPelaksana::query()->updateOrCreate(
                ['nama' => $team['nama']],
                [
                    'nama_pengawas' => $team['nama_pengawas'],
                    'memiliki_wilayah_kerja' => $team['memiliki_wilayah_kerja'],
                    'aktif' => true,
                    'urutan' => $team['urutan'],
                ],
            );
        }

        $wilayahTeams = TimPelaksana::query()
            ->where('memiliki_wilayah_kerja', true)
            ->orderBy('urutan')
            ->get();

        if ($wilayahTeams->isEmpty()) {
            return;
        }

        $kelurahanIds = Kelurahan::query()
            ->join('kecamatans', 'kecamatans.id', '=', 'kelurahans.kecamatan_id')
            ->orderBy('kecamatans.nama')
            ->orderBy('kelurahans.nama')
            ->pluck('kelurahans.id');

        if ($kelurahanIds->isEmpty()) {
            return;
        }

        $chunks = $kelurahanIds->chunk((int) ceil($kelurahanIds->count() / $wilayahTeams->count()));

        foreach ($wilayahTeams as $index => $team) {
            $assigned = $chunks->get($index, collect());

            if ($assigned->isNotEmpty()) {
                $team->kelurahans()->sync($assigned->values()->all());
            }
        }
    }
}
