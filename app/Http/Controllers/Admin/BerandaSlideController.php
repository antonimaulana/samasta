<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BerandaSlide;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BerandaSlideController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin.manage');
    }

    public function index(): View
    {
        $slides = BerandaSlide::query()->orderBy('urutan')->orderBy('id')->get();

        return view('admin.beranda_slides.index', compact('slides'));
    }

    public function edit(BerandaSlide $berandaSlide): View
    {
        return view('admin.beranda_slides.edit', [
            'slide' => $berandaSlide,
            'jenisOptions' => $this->jenisOptions(),
            'livePresets' => $this->livePresets(),
            'routeOptions' => $this->routeOptions(),
        ]);
    }

    public function update(Request $request, BerandaSlide $berandaSlide): RedirectResponse
    {
        $data = $this->validateSlide($request, $berandaSlide);
        $berandaSlide->update($data);

        return redirect()
            ->route('admin.beranda-slides.index')
            ->with('success', 'Slide beranda «'.$berandaSlide->title_attr.'» berhasil diperbarui.');
    }

    public function toggle(BerandaSlide $berandaSlide): RedirectResponse
    {
        $berandaSlide->update(['is_active' => ! $berandaSlide->is_active]);

        return back()->with('success', 'Status slide diperbarui.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order' => ['required', 'array', 'min:1'],
            'order.*' => ['integer', Rule::exists('beranda_slides', 'id')],
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['order'] as $index => $id) {
                BerandaSlide::query()->whereKey($id)->update(['urutan' => ($index + 1) * 10]);
            }
        });

        return back()->with('success', 'Urutan slide disimpan.');
    }

    /**
     * @return array<string, string>
     */
    private function jenisOptions(): array
    {
        return [
            BerandaSlide::JENIS_STANDARD => 'Standar (dua kolom + statistik)',
            BerandaSlide::JENIS_SIMPONI => 'Simponi (logo + teks)',
            BerandaSlide::JENIS_PERDA => 'Perda ketertiban (konten otomatis)',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function livePresets(): array
    {
        return [
            '' => '— Statis / isi manual —',
            'rth' => 'Data RTH live (luas, lokasi, kategori)',
            'operasional_dual' => 'Kegiatan operasional (pemangkasan & pohon tumbang)',
            'ensiklopedia' => 'Ensiklopedia (jumlah artikel & topik)',
            'aduan' => 'Aduan masyarakat (total, selesai, bulan ini)',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function routeOptions(): array
    {
        return [
            '' => '— URL manual —',
            'rth.index' => 'RTH Kota Batam',
            'ensiklopedia.index' => 'Ensiklopedia',
            'masukan.index' => 'Masukan / layanan',
            'peraturan.index' => 'Peraturan / Perda',
            'aduan.create' => 'Form aduan',
            'home' => 'Beranda',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validateSlide(Request $request, BerandaSlide $slide): array
    {
        $jenis = $request->input('jenis', $slide->jenis);

        $rules = [
            'jenis' => ['required', Rule::in([
                BerandaSlide::JENIS_STANDARD,
                BerandaSlide::JENIS_SIMPONI,
                BerandaSlide::JENIS_PERDA,
            ])],
            'is_active' => ['sometimes', 'boolean'],
            'title_attr' => ['required', 'string', 'max:120'],
            'route_name' => ['nullable', 'string', 'max:80'],
            'href' => ['nullable', 'string', 'max:500'],
            'heading' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string', 'max:2000'],
            'cta_text' => ['required', 'string', 'max:120'],
        ];

        if ($jenis === BerandaSlide::JENIS_STANDARD) {
            $rules += [
                'badge' => ['nullable', 'string', 'max:80'],
                'left_gradient' => ['nullable', 'string', 'max:120'],
                'hero_mode' => ['nullable', Rule::in(['single', 'dual'])],
                'hero_live_preset' => ['nullable', 'string', 'max:40'],
                'show_stats' => ['sometimes', 'boolean'],
                'tags_text' => ['nullable', 'string', 'max:500'],
                'tag_classes' => ['nullable', 'string', 'max:120'],
                'card_ring' => ['nullable', 'string', 'max:80'],
                'card_shadow' => ['nullable', 'string', 'max:120'],
                'cta_color' => ['nullable', 'string', 'max:80'],
                'heading_hover' => ['nullable', 'string', 'max:80'],
            ];
        }

        if ($jenis === BerandaSlide::JENIS_SIMPONI) {
            $rules += [
                'badge' => ['nullable', 'string', 'max:80'],
                'external_url' => ['required', 'url', 'max:500'],
                'tags_text' => ['nullable', 'string', 'max:500'],
            ];
        }

        $validated = $request->validate($rules);

        $payload = [
            'jenis' => $validated['jenis'],
            'title_attr' => $validated['title_attr'],
            'route_name' => filled($validated['route_name'] ?? null) ? $validated['route_name'] : null,
            'href' => filled($validated['href'] ?? null) ? $validated['href'] : null,
            'heading' => $validated['heading'],
            'description' => $validated['description'],
            'cta_text' => $validated['cta_text'],
        ];

        if ($jenis === BerandaSlide::JENIS_STANDARD) {
            $payload += [
                'badge' => $validated['badge'] ?? null,
                'left_gradient' => $validated['left_gradient'] ?? null,
                'hero_mode' => $validated['hero_mode'] ?? 'single',
                'hero_live_preset' => filled($validated['hero_live_preset'] ?? null) ? $validated['hero_live_preset'] : null,
                'show_stats' => $request->boolean('show_stats'),
                'tags' => $this->tagsFromText($validated['tags_text'] ?? ''),
                'tag_classes' => $validated['tag_classes'] ?? null,
                'card_ring' => $validated['card_ring'] ?? null,
                'card_shadow' => $validated['card_shadow'] ?? null,
                'cta_color' => $validated['cta_color'] ?? null,
                'heading_hover' => $validated['heading_hover'] ?? null,
            ];
        }

        if ($jenis === BerandaSlide::JENIS_SIMPONI) {
            $url = $validated['external_url'];
            $payload['href'] = $url;
            $payload['badge'] = $validated['badge'] ?? 'Layanan Pemakaman';
            $payload['tags'] = $this->tagsFromText($validated['tags_text'] ?? '');
            $payload['config'] = ['external_url' => $url];
        }

        return $payload;
    }

    /**
     * @return list<string>
     */
    private function tagsFromText(string $text): array
    {
        if (trim($text) === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', preg_split('/[\n,]+/', $text) ?: [])));
    }
}
