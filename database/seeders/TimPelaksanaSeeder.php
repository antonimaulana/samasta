<?php

namespace Database\Seeders;

use App\Models\Kelurahan;
use App\Models\Petugas;
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

        $this->seedPetugasContoh();
    }

    private function seedPetugasContoh(): void
    {
        $samples = [
            'Tim Wilayah 1' => [
                ['nama' => 'Maryono', 'is_pengawas' => true, 'is_inti' => true],
                ['nama' => 'Ahmad Hidayat', 'is_inti' => true],
                ['nama' => 'Budi Santoso', 'is_inti' => true],
                ['nama' => 'Candra Wijaya'],
                ['nama' => 'Dedi Kurniawan'],
            ],
            'Tim Wilayah 2' => [
                ['nama' => 'Muhammad Rifan', 'is_pengawas' => true, 'is_inti' => true],
                ['nama' => 'Eko Prasetyo', 'is_inti' => true],
                ['nama' => 'Fitri Rahmawati', 'is_inti' => true],
                ['nama' => 'Gunawan'],
            ],
            'Tim Armada' => [
                ['nama' => 'Munasir', 'is_pengawas' => true, 'is_inti' => true],
                ['nama' => 'Hendra Sopir', 'jabatan' => 'Sopir', 'is_inti' => true],
                ['nama' => 'Iwan Sopir', 'jabatan' => 'Sopir'],
            ],
        ];

        foreach ($samples as $teamName => $members) {
            $team = TimPelaksana::query()->where('nama', $teamName)->first();

            if (! $team) {
                continue;
            }

            foreach ($members as $index => $member) {
                Petugas::query()->updateOrCreate(
                    [
                        'tim_pelaksana_id' => $team->id,
                        'nama' => $member['nama'],
                    ],
                    [
                        'jabatan' => $member['jabatan'] ?? null,
                        'is_inti' => (bool) ($member['is_inti'] ?? false),
                        'is_pengawas' => (bool) ($member['is_pengawas'] ?? false),
                        'aktif' => true,
                        'urutan' => $index + 1,
                    ],
                );
            }
        }
    }
}
