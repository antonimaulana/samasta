<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelurahan;
use App\Models\Petugas;
use App\Models\TimPelaksana;
use App\Support\PetugasRosterBuilder;
use App\Support\TimPelaksanaResolver;
use App\Support\TimPelaksanaSaver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TimPelaksanaController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', TimPelaksana::class);

        $query = TimPelaksana::query()->withCount('kelurahans');

        if (Schema::hasTable('petugas')) {
            $query->withCount('petugas');
        }

        $teams = $query->orderBy('urutan')->orderBy('nama')->get();

        return view('admin.tim_pelaksanas.index', compact('teams'));
    }

    public function create(): View
    {
        $this->authorize('create', TimPelaksana::class);

        return view('admin.tim_pelaksanas.create', $this->formViewData());
    }

    public function store(Request $request, TimPelaksanaSaver $saver): RedirectResponse
    {
        $this->authorize('create', TimPelaksana::class);

        $data = $this->validated($request);

        $team = $saver->create($data);

        return redirect()
            ->route('admin.tim-pelaksanas.index')
            ->with('success', 'Tim kerja '.$team->nama.' berhasil ditambahkan.');
    }

    public function edit(TimPelaksana $timPelaksana): View
    {
        $this->authorize('update', $timPelaksana);

        $timPelaksana->load(['kelurahans', 'petugas' => fn ($q) => $q->orderBy('urutan')->orderBy('nama')]);

        return view('admin.tim_pelaksanas.edit', array_merge(
            $this->formViewData($timPelaksana),
            ['timPelaksana' => $timPelaksana],
        ));
    }

    public function update(Request $request, TimPelaksana $timPelaksana, TimPelaksanaSaver $saver): RedirectResponse
    {
        $this->authorize('update', $timPelaksana);

        $data = $this->validated($request, $timPelaksana);

        $team = $saver->update($timPelaksana, $data);

        return redirect()
            ->route('admin.tim-pelaksanas.index')
            ->with('success', 'Tim kerja '.$team->nama.' berhasil diperbarui.');
    }

    public function destroy(TimPelaksana $timPelaksana, TimPelaksanaSaver $saver): RedirectResponse
    {
        $this->authorize('delete', $timPelaksana);

        $nama = $timPelaksana->nama;

        try {
            $saver->delete($timPelaksana);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()
            ->route('admin.tim-pelaksanas.index')
            ->with('success', 'Tim kerja '.$nama.' dihapus.');
    }

    public function roster(Request $request, PetugasRosterBuilder $rosterBuilder): JsonResponse
    {
        $this->authorize('viewAny', TimPelaksana::class);

        if ($request->query('scope') === 'operasional-wilayah') {
            return response()->json([
                'petugas' => $rosterBuilder->forOperasionalWilayah(),
            ]);
        }

        if ($request->filled('teams')) {
            $teams = collect($request->input('teams'))
                ->filter(fn ($team) => is_string($team) && $team !== '')
                ->values()
                ->all();

            return response()->json([
                'petugas' => $rosterBuilder->forTeamNames($teams),
            ]);
        }

        $validated = $request->validate([
            'tim' => ['required', 'string'],
        ]);

        return response()->json([
            'petugas' => $rosterBuilder->forTeamName($validated['tim']),
        ]);
    }

    public function suggest(Request $request, TimPelaksanaResolver $resolver): JsonResponse
    {
        $this->authorize('viewAny', TimPelaksana::class);

        $validated = $request->validate([
            'taman_id' => ['required', 'integer', 'exists:tamans,id'],
        ]);

        $teams = $resolver->forTamanId((int) $validated['taman_id']);

        return response()->json([
            'teams' => $teams,
            'has_mapping' => $teams !== [],
        ]);
    }

    /**
     * @return array{kecamatans: \Illuminate\Support\Collection, assignedIds: list<int>, defaultMembers: list<array<string, mixed>>}
     */
    private function formViewData(?TimPelaksana $timPelaksana = null): array
    {
        $kecamatans = Kelurahan::query()
            ->with('kecamatan')
            ->join('kecamatans', 'kecamatans.id', '=', 'kelurahans.kecamatan_id')
            ->orderBy('kecamatans.nama')
            ->orderBy('kelurahans.nama')
            ->select('kelurahans.*')
            ->get()
            ->groupBy(fn (Kelurahan $kelurahan) => $kelurahan->kecamatan->nama);

        $assignedIds = $timPelaksana
            ? $timPelaksana->kelurahans->pluck('id')->all()
            : [];

        $defaultMembers = old('members');

        if ($defaultMembers === null && $timPelaksana) {
            $defaultMembers = $timPelaksana->petugas
                ->map(fn (Petugas $p) => [
                    'id' => $p->id,
                    'nama' => $p->nama,
                    'nip' => $p->nip,
                    'jabatan' => $p->jabatan,
                ])
                ->all();
        }

        if ($defaultMembers === null) {
            $defaultMembers = [['nama' => '', 'nip' => '', 'jabatan' => '']];
        }

        return compact('kecamatans', 'assignedIds', 'defaultMembers');
    }

    /**
     * @return array{
     *     nama: string,
     *     nama_pengawas: ?string,
     *     memiliki_wilayah_kerja: bool,
     *     kelurahan_ids: list<int>,
     *     members: list<array{id?: int, nama: string, nip?: ?string, jabatan?: ?string}>
     * }
     */
    private function validated(Request $request, ?TimPelaksana $timPelaksana = null): array
    {
        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:120',
                Rule::unique('tim_pelaksanas', 'nama')->ignore($timPelaksana?->id),
            ],
            'nama_pengawas' => ['nullable', 'string', 'max:120'],
            'memiliki_wilayah_kerja' => ['nullable', 'boolean'],
            'kelurahan_ids' => ['nullable', 'array'],
            'kelurahan_ids.*' => ['integer', 'exists:kelurahans,id'],
            'members' => ['nullable', 'array'],
            'members.*.id' => ['nullable', 'integer'],
            'members.*.nama' => ['nullable', 'string', 'max:120'],
            'members.*.nip' => ['nullable', 'string', 'max:20'],
            'members.*.jabatan' => ['nullable', 'string', 'max:80'],
        ]);

        $memilikiWilayah = $request->boolean('memiliki_wilayah_kerja');

        $members = collect($validated['members'] ?? [])
            ->filter(fn (array $row) => filled(trim((string) ($row['nama'] ?? ''))))
            ->values()
            ->all();

        foreach ($members as $index => $member) {
            $nip = isset($member['nip']) ? preg_replace('/\D+/', '', (string) $member['nip']) : '';

            if ($nip !== '' && strlen($nip) !== 18) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "members.{$index}.nip" => 'NIP harus 18 digit angka.',
                ]);
            }

            $petugasId = isset($member['id']) ? (int) $member['id'] : null;

            if ($nip !== '') {
                $exists = Petugas::query()
                    ->where('nip', $nip)
                    ->when($petugasId, fn ($q) => $q->where('id', '!=', $petugasId))
                    ->exists();

                if ($exists) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        "members.{$index}.nip" => 'NIP sudah digunakan petugas lain.',
                    ]);
                }
            }
        }

        return [
            'nama' => trim($validated['nama']),
            'nama_pengawas' => filled($validated['nama_pengawas'] ?? null)
                ? trim((string) $validated['nama_pengawas'])
                : null,
            'memiliki_wilayah_kerja' => $memilikiWilayah,
            'kelurahan_ids' => $memilikiWilayah ? array_values($validated['kelurahan_ids'] ?? []) : [],
            'members' => $members,
        ];
    }
}
