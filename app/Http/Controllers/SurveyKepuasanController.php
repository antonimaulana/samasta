<?php

namespace App\Http\Controllers;

use App\Models\SurveyKepuasan;
use App\Models\Taman;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SurveyKepuasanController extends Controller
{
    public function create(Request $request): View
    {
        $tamans = Taman::query()->orderBy('nama_taman')->get(['id', 'nama_taman']);

        return view('survey.create', [
            'tamans' => $tamans,
            'selectedTamanId' => $request->integer('taman') ?: null,
            'selectedKategori' => $request->input('kategori'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kategori' => ['required', Rule::in(SurveyKepuasan::KATEGORI)],
            'rating' => ['required', 'integer', 'between:1,5'],
            'taman_id' => ['nullable', 'exists:tamans,id'],
            'saran' => ['nullable', 'string', 'max:2000'],
            'nama' => ['nullable', 'string', 'max:100'],
        ], [], [
            'kategori' => 'kategori layanan',
            'rating' => 'penilaian',
            'taman_id' => 'taman',
            'saran' => 'saran',
            'nama' => 'nama',
        ]);

        if (blank($validated['nama'] ?? null)) {
            $validated['nama'] = null;
        }

        SurveyKepuasan::create($validated);

        return redirect()
            ->route('survey.success')
            ->with('success', 'Terima kasih atas partisipasi Anda.');
    }

    public function success(): View
    {
        return view('survey.success');
    }
}
