<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelurahan;
use App\Models\TimPelaksana;
use App\Support\PetugasRosterBuilder;
use App\Support\TimPelaksanaResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TimPelaksanaController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', TimPelaksana::class);

        $teams = TimPelaksana::query()
            ->withCount('kelurahans')
            ->orderBy('urutan')
            ->orderBy('nama')
            ->get();

        return view('admin.tim_pelaksanas.index', compact('teams'));
    }

    public function editWilayah(TimPelaksana $timPelaksana): View
    {
        $this->authorize('manageWilayah', $timPelaksana);

        $timPelaksana->load('kelurahans');

        $kecamatans = Kelurahan::query()
            ->with('kecamatan')
            ->join('kecamatans', 'kecamatans.id', '=', 'kelurahans.kecamatan_id')
            ->orderBy('kecamatans.nama')
            ->orderBy('kelurahans.nama')
            ->select('kelurahans.*')
            ->get()
            ->groupBy(fn (Kelurahan $kelurahan) => $kelurahan->kecamatan->nama);

        $assignedIds = $timPelaksana->kelurahans->pluck('id')->all();

        return view('admin.tim_pelaksanas.wilayah', compact('timPelaksana', 'kecamatans', 'assignedIds'));
    }

    public function updateWilayah(Request $request, TimPelaksana $timPelaksana): RedirectResponse
    {
        $this->authorize('manageWilayah', $timPelaksana);

        $validated = $request->validate([
            'kelurahan_ids' => ['nullable', 'array'],
            'kelurahan_ids.*' => ['integer', 'exists:kelurahans,id'],
        ]);

        $timPelaksana->assignWilayahKerja($validated['kelurahan_ids'] ?? []);

        return redirect()
            ->route('admin.tim-pelaksanas.index')
            ->with('success', 'Wilayah kerja '.$timPelaksana->nama.' berhasil diperbarui.');
    }

    public function roster(Request $request, PetugasRosterBuilder $rosterBuilder): JsonResponse
    {
        $this->authorize('viewAny', TimPelaksana::class);

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
}
