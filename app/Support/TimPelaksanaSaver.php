<?php

namespace App\Support;

use App\Models\Petugas;
use App\Models\TimPelaksana;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TimPelaksanaSaver
{
    /**
     * @param  array{
     *     nama: string,
     *     nama_pengawas: ?string,
     *     memiliki_wilayah_kerja: bool,
     *     kelurahan_ids?: list<int>,
     *     members?: list<array{id?: int, nama: string, nip?: ?string, jabatan?: ?string}>
     * }  $data
     */
    public function create(array $data): TimPelaksana
    {
        return DB::transaction(function () use ($data) {
            $maxUrutan = (int) TimPelaksana::query()->max('urutan');

            $team = TimPelaksana::query()->create([
                'nama' => $data['nama'],
                'nama_pengawas' => $data['nama_pengawas'],
                'memiliki_wilayah_kerja' => $data['memiliki_wilayah_kerja'],
                'aktif' => true,
                'urutan' => $maxUrutan + 1,
            ]);

            $this->syncWilayah($team, $data);
            $this->syncMembers($team, $data['members'] ?? [], $data['nama_pengawas']);

            return $team;
        });
    }

    /**
     * @param  array{
     *     nama: string,
     *     nama_pengawas: ?string,
     *     memiliki_wilayah_kerja: bool,
     *     kelurahan_ids?: list<int>,
     *     members?: list<array{id?: int, nama: string, nip?: ?string, jabatan?: ?string}>
     * }  $data
     */
    public function update(TimPelaksana $team, array $data): TimPelaksana
    {
        return DB::transaction(function () use ($team, $data) {
            $team->update([
                'nama' => $data['nama'],
                'nama_pengawas' => $data['nama_pengawas'],
                'memiliki_wilayah_kerja' => $data['memiliki_wilayah_kerja'],
            ]);

            $this->syncWilayah($team, $data);
            $this->syncMembers($team, $data['members'] ?? [], $data['nama_pengawas']);

            return $team->fresh();
        });
    }

    /**
     * @param  array{memiliki_wilayah_kerja: bool, kelurahan_ids?: list<int>}  $data
     */
    private function syncWilayah(TimPelaksana $team, array $data): void
    {
        if (! $data['memiliki_wilayah_kerja']) {
            $team->kelurahans()->sync([]);

            return;
        }

        $team->assignWilayahKerja($data['kelurahan_ids'] ?? []);
    }

    /**
     * @param  list<array{id?: int, nama: string, nip?: ?string, jabatan?: ?string}>  $members
     */
    private function syncMembers(TimPelaksana $team, array $members, ?string $namaPengawas): void
    {
        $members = collect($members)
            ->map(function (array $row) {
                $nip = isset($row['nip']) ? preg_replace('/\D+/', '', (string) $row['nip']) : '';

                return [
                    'id' => isset($row['id']) ? (int) $row['id'] : null,
                    'nama' => trim((string) ($row['nama'] ?? '')),
                    'nip' => $nip !== '' ? $nip : null,
                    'jabatan' => filled($row['jabatan'] ?? null) ? trim((string) $row['jabatan']) : null,
                ];
            })
            ->filter(fn (array $row) => $row['nama'] !== '')
            ->values();

        $pengawasKey = mb_strtolower(trim((string) $namaPengawas));

        $keepIds = $members->pluck('id')->filter()->all();

        $team->petugas()
            ->when($keepIds !== [], fn ($q) => $q->whereNotIn('id', $keepIds))
            ->when($keepIds === [], fn ($q) => $q)
            ->get()
            ->each(function (Petugas $petugas) {
                if ($petugas->pemeliharaanTamans()->exists() || $petugas->pemangkasanProgres()->exists()) {
                    $petugas->update(['aktif' => false]);
                } else {
                    $petugas->delete();
                }
            });

        foreach ($members as $index => $member) {
            $isPengawas = $pengawasKey !== '' && mb_strtolower($member['nama']) === $pengawasKey;

            $attributes = [
                'nama' => $member['nama'],
                'nip' => $member['nip'],
                'jabatan' => $member['jabatan'],
                'is_pengawas' => $isPengawas,
                'aktif' => true,
                'urutan' => $index + 1,
            ];

            if ($member['id']) {
                $petugas = $team->petugas()->whereKey($member['id'])->first();

                if (! $petugas) {
                    continue;
                }

                $petugas->update($attributes);

                continue;
            }

            $team->petugas()->create($attributes);
        }
    }

    public function delete(TimPelaksana $team): void
    {
        if ($team->users()->exists()) {
            throw ValidationException::withMessages([
                'nama' => 'Tim masih terhubung ke akun pengguna. Lepaskan tim dari Kelola Pengguna terlebih dahulu.',
            ]);
        }

        DB::transaction(function () use ($team) {
            $team->delete();
        });
    }
}
