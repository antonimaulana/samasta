<?php

namespace App\Http\Controllers\Admin\Dpa;

use App\Http\Controllers\Controller;
use App\Models\DpaPenyedia;
use App\Support\TableSearch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PenyediaController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin.manage');
    }

    public function index(Request $request): View
    {
        $penyedias = TableSearch::apply(
            DpaPenyedia::query()->withCount('paketPekerjaans')->orderBy('nama'),
            $request,
            ['nama', 'pic', 'jabatan', 'npwp', 'no_rekening']
        )->paginate(15)->withQueryString();

        return view('admin.dpa.penyedias.index', compact('penyedias'));
    }

    public function create(): View
    {
        return view('admin.dpa.penyedias.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePenyedia($request);

        if ($request->hasFile('company_profile_file')) {
            $validated['company_profile_file'] = $this->storeCompanyProfileFile($request->file('company_profile_file'));
        }

        DpaPenyedia::create($validated);

        return redirect()
            ->route('admin.dpa.penyedias.index')
            ->with('success', 'Penyedia berhasil ditambahkan.');
    }

    public function edit(DpaPenyedia $penyedia): View
    {
        return view('admin.dpa.penyedias.edit', compact('penyedia'));
    }

    public function update(Request $request, DpaPenyedia $penyedia): RedirectResponse
    {
        $validated = $this->validatePenyedia($request);

        if ($request->hasFile('company_profile_file')) {
            $this->deletePublicFile($penyedia->company_profile_file);
            $validated['company_profile_file'] = $this->storeCompanyProfileFile($request->file('company_profile_file'));
        }

        $penyedia->update($validated);

        return redirect()
            ->route('admin.dpa.penyedias.index')
            ->with('success', 'Penyedia berhasil diperbarui.');
    }

    public function destroy(DpaPenyedia $penyedia): RedirectResponse
    {
        if ($penyedia->paketPekerjaans()->exists()) {
            return redirect()
                ->route('admin.dpa.penyedias.index')
                ->with('error', 'Penyedia tidak dapat dihapus karena masih digunakan pada paket pekerjaan.');
        }

        $this->deletePublicFile($penyedia->company_profile_file);
        $penyedia->delete();

        return redirect()
            ->route('admin.dpa.penyedias.index')
            ->with('success', 'Penyedia berhasil dihapus.');
    }

    private function validatePenyedia(Request $request): array
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'pic' => ['nullable', 'string', 'max:255'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'npwp' => ['nullable', 'string', 'max:50'],
            'no_rekening' => ['nullable', 'string', 'max:100'],
            'company_profile' => ['nullable', 'string'],
            'company_profile_file' => ['nullable', 'file', 'max:10240'],
        ]);

        unset($validated['company_profile_file']);

        return $validated;
    }

    private function storeCompanyProfileFile(UploadedFile $file): string
    {
        $path = $file->store('dpa-penyedia', 'public');

        return 'storage/'.$path;
    }

    private function deletePublicFile(?string $filePath): void
    {
        if (blank($filePath) || ! str_starts_with($filePath, 'storage/')) {
            return;
        }

        Storage::disk('public')->delete(substr($filePath, strlen('storage/')));
    }
}
