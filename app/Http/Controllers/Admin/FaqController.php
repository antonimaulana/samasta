<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\FaqSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin.manage');
    }

    public function index(): View
    {
        return view('admin.faq.index', [
            'setting' => FaqSetting::current(),
            'faqs' => Faq::query()->orderBy('urutan')->orderBy('id')->get(),
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'section_badge' => ['nullable', 'string', 'max:80'],
            'section_title' => ['required', 'string', 'max:160'],
        ]);

        FaqSetting::current()->update([
            ...$validated,
            'section_subtitle' => null,
        ]);

        return back()->with('success', 'Judul bagian FAQ beranda disimpan.');
    }

    public function create(): View
    {
        return view('admin.faq.edit', [
            'faq' => new Faq(['is_active' => true]),
            'isNew' => true,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateFaq($request);
        $data['urutan'] = ((int) Faq::query()->max('urutan')) + 10;
        $data['is_active'] = $request->boolean('is_active', true);

        Faq::query()->create($data);

        return redirect()->route('admin.faq.index')->with('success', 'Pertanyaan FAQ ditambahkan.');
    }

    public function edit(Faq $faq): View
    {
        return view('admin.faq.edit', [
            'faq' => $faq,
            'isNew' => false,
        ]);
    }

    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $data = $this->validateFaq($request);
        $data['is_active'] = $request->boolean('is_active');

        $faq->update($data);

        return redirect()->route('admin.faq.index')->with('success', 'FAQ diperbarui.');
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return back()->with('success', 'FAQ dihapus.');
    }

    public function toggle(Faq $faq): RedirectResponse
    {
        $faq->update(['is_active' => ! $faq->is_active]);

        return back()->with('success', 'Status FAQ diperbarui.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order' => ['required', 'array', 'min:1'],
            'order.*' => ['integer', Rule::exists('faqs', 'id')],
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['order'] as $index => $id) {
                Faq::query()->whereKey($id)->update(['urutan' => ($index + 1) * 10]);
            }
        });

        return back()->with('success', 'Urutan FAQ disimpan.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateFaq(Request $request): array
    {
        return $request->validate([
            'question' => ['required', 'string', 'max:300'],
            'answer' => ['required', 'string', 'max:5000'],
        ]);
    }
}
