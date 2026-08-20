<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pejabat;
use App\Support\KontenBerandaCache;
use App\Support\TableSearch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PejabatController extends Controller
{
    public function index(Request $request): View
    {
        $pejabats = TableSearch::apply(
            Pejabat::query()->orderBy('urutan')->orderBy('nama'),
            $request,
            ['nama', 'jabatan']
        )->paginate(15)->withQueryString();

        return view('admin.pejabats.index', compact('pejabats'));
    }

    public function create(): View
    {
        return view('admin.pejabats.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePejabat($request);

        if ($request->hasFile('photo')) {
            $validated['image_path'] = $this->storePhoto($request);
        }

        Pejabat::create($validated);
        KontenBerandaCache::forgetAll();

        return redirect()
            ->route('admin.pejabats.index')
            ->with('success', 'Data pejabat berhasil ditambahkan.');
    }

    public function edit(Pejabat $pejabat): View
    {
        return view('admin.pejabats.edit', compact('pejabat'));
    }

    public function update(Request $request, Pejabat $pejabat): RedirectResponse
    {
        $validated = $this->validatePejabat($request, $pejabat);

        if ($request->hasFile('photo')) {
            $this->deletePhoto($pejabat->image_path);
            $validated['image_path'] = $this->storePhoto($request);
        }

        $pejabat->update($validated);
        KontenBerandaCache::forgetAll();

        return redirect()
            ->route('admin.pejabats.index')
            ->with('success', 'Data pejabat berhasil diperbarui.');
    }

    public function destroy(Pejabat $pejabat): RedirectResponse
    {
        $this->deletePhoto($pejabat->image_path);
        $pejabat->delete();
        KontenBerandaCache::forgetAll();

        return redirect()
            ->route('admin.pejabats.index')
            ->with('success', 'Data pejabat berhasil dihapus.');
    }

    private function validatePejabat(Request $request, ?Pejabat $pejabat = null): array
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'jabatan' => ['required', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'accent_bg' => ['required', 'string', 'max:255'],
            'accent_ring' => ['required', 'string', 'max:255'],
            'photo_class' => ['nullable', 'string', 'max:255'],
            'photo_frame_class' => ['nullable', 'string', 'max:255'],
            'urutan' => ['nullable', 'integer', 'min:0'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $validated['urutan'] = $validated['urutan'] ?? 0;
        $validated['is_published'] = $request->boolean('is_published');

        unset($validated['photo']);

        return $validated;
    }

    private function storePhoto(Request $request): string
    {
        $path = $request->file('photo')->store('pimpinan', 'public');

        return 'storage/'.$path;
    }

    private function deletePhoto(?string $imagePath): void
    {
        if (blank($imagePath) || ! str_starts_with($imagePath, 'storage/')) {
            return;
        }

        Storage::disk('public')->delete(substr($imagePath, strlen('storage/')));
    }
}
