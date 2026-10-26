<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BerandaSlide;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

    public function show(BerandaSlide $berandaSlide): RedirectResponse
    {
        return redirect()->route('admin.beranda-slides.edit', $berandaSlide);
    }

    public function create(): View
    {
        return view('admin.beranda_slides.edit', $this->formViewData(new BerandaSlide([
            'jenis' => BerandaSlide::JENIS_STANDARD,
            'visual_mode' => BerandaSlide::VISUAL_GRADIENT,
            'is_active' => true,
            'hero_mode' => 'single',
            'show_stats' => false,
            'cta_text' => 'Lihat selengkapnya',
            'left_gradient' => 'from-lime-400 via-green-400 to-emerald-500',
        ])));
    }

    public function store(Request $request): RedirectResponse
    {
        $slide = new BerandaSlide;
        $data = $this->validateSlide($request, $slide);
        $data['slug'] = $this->uniqueSlug((string) $data['heading']);
        $data['urutan'] = ((int) BerandaSlide::query()->max('urutan')) + 10;
        $data['is_active'] = $request->boolean('is_active', true);
        $data = $this->mergeUploadedImage($request, $slide, $data);

        $slide = BerandaSlide::query()->create($data);

        return redirect()
            ->route('admin.beranda-slides.index')
            ->with('success', 'Slide «'.$slide->title_attr.'» berhasil ditambahkan.');
    }

    public function edit(BerandaSlide $berandaSlide): View
    {
        return view('admin.beranda_slides.edit', $this->formViewData($berandaSlide));
    }

    public function update(Request $request, BerandaSlide $berandaSlide): RedirectResponse
    {
        $data = $this->validateSlide($request, $berandaSlide);
        $data['is_active'] = $request->boolean('is_active');
        $data = $this->mergeUploadedImage($request, $berandaSlide, $data);

        $berandaSlide->update($data);

        return redirect()
            ->route('admin.beranda-slides.index')
            ->with('success', 'Slide beranda «'.$berandaSlide->title_attr.'» berhasil diperbarui.');
    }

    public function destroy(BerandaSlide $berandaSlide): RedirectResponse
    {
        $label = $berandaSlide->title_attr;

        if ($berandaSlide->image_path) {
            Storage::disk('public')->delete($berandaSlide->image_path);
        }

        $berandaSlide->delete();

        return redirect()
            ->route('admin.beranda-slides.index')
            ->with('success', 'Slide «'.$label.'» dihapus.');
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
     * @return array<string, mixed>
     */
    private function formViewData(BerandaSlide $slide): array
    {
        return [
            'slide' => $slide,
            'isNew' => ! $slide->exists,
            'jenisOptions' => $this->jenisOptions(),
            'visualModeOptions' => $this->visualModeOptions($slide),
            'livePresets' => $this->livePresets(),
            'routeOptions' => $this->routeOptions(),
        ];
    }

    private function uniqueSlug(string $heading): string
    {
        $base = Str::slug($heading);
        if ($base === '') {
            $base = 'slide';
        }

        $slug = $base;
        $suffix = 2;

        while (BerandaSlide::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function mergeUploadedImage(Request $request, BerandaSlide $slide, array $data): array
    {
        if ($request->boolean('remove_image') && $slide->image_path) {
            Storage::disk('public')->delete($slide->image_path);
            $data['image_path'] = null;
        }

        if ($request->hasFile('image')) {
            if ($slide->image_path) {
                Storage::disk('public')->delete($slide->image_path);
            }
            $data['image_path'] = $request->file('image')->store('beranda-slides', 'public');
        }

        return $data;
    }

    /**
     * @return array<string, string>
     */
    private function jenisOptions(): array
    {
        return [
            BerandaSlide::JENIS_STANDARD => 'Informasi',
            BerandaSlide::JENIS_SIMPONI => 'Layanan eksternal',
            BerandaSlide::JENIS_PERDA => 'Perda ketertiban',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function visualModeOptions(BerandaSlide $slide): array
    {
        $jenis = $slide->jenis ?? BerandaSlide::JENIS_STANDARD;

        if ($jenis === BerandaSlide::JENIS_PERDA) {
            return [BerandaSlide::VISUAL_TEMPLATE => 'Layout Perda'];
        }

        if ($jenis === BerandaSlide::JENIS_SIMPONI) {
            return [
                BerandaSlide::VISUAL_ASSET => 'Aset bawaan',
                BerandaSlide::VISUAL_IMAGE => 'Gambar',
            ];
        }

        return [
            BerandaSlide::VISUAL_LIVE => 'Statistik live',
            BerandaSlide::VISUAL_GRADIENT => 'Gradien + angka',
            BerandaSlide::VISUAL_IMAGE => 'Gambar',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function livePresets(): array
    {
        return [
            '' => '— Pilih —',
            'rth' => 'RTH',
            'operasional_dual' => 'Operasional pertamanan',
            'ensiklopedia' => 'Ensiklopedia',
            'aduan' => 'Aduan masyarakat',
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
        $jenis = $request->input('jenis', $slide->jenis ?? BerandaSlide::JENIS_STANDARD);
        $existingImagePath = $slide->exists ? $slide->image_path : null;

        $rules = [
            'jenis' => ['required', Rule::in([
                BerandaSlide::JENIS_STANDARD,
                BerandaSlide::JENIS_SIMPONI,
                BerandaSlide::JENIS_PERDA,
            ])],
            'title_attr' => ['nullable', 'string', 'max:120'],
            'route_name' => ['nullable', 'string', 'max:80'],
            'href' => ['nullable', 'string', 'max:500'],
            'badge' => ['nullable', 'string', 'max:80'],
            'tags_text' => ['nullable', 'string', 'max:500'],
            'heading' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string', 'max:600'],
            'cta_text' => ['required', 'string', 'max:120'],
            'image' => ['nullable', 'image', 'max:4096'],
            'image_alt' => ['nullable', 'string', 'max:200'],
            'remove_image' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];

        if ($jenis === BerandaSlide::JENIS_STANDARD) {
            $rules['visual_mode'] = ['required', Rule::in([
                BerandaSlide::VISUAL_LIVE,
                BerandaSlide::VISUAL_GRADIENT,
                BerandaSlide::VISUAL_IMAGE,
            ])];
            $rules += [
                'left_gradient' => ['nullable', 'string', 'max:120'],
                'hero_mode' => ['nullable', Rule::in(['single', 'dual'])],
                'hero_live_preset' => ['nullable', 'string', 'max:40'],
                'show_stats' => ['sometimes', 'boolean'],
                'hero_value' => ['nullable', 'string', 'max:80'],
                'hero_label' => ['nullable', 'string', 'max:120'],
                'hero_dual_left_value' => ['nullable', 'string', 'max:80'],
                'hero_dual_left_label' => ['nullable', 'string', 'max:120'],
                'hero_dual_right_value' => ['nullable', 'string', 'max:80'],
                'hero_dual_right_label' => ['nullable', 'string', 'max:120'],
                'stat_left_value' => ['nullable', 'string', 'max:80'],
                'stat_left_label' => ['nullable', 'string', 'max:120'],
                'stat_right_value' => ['nullable', 'string', 'max:80'],
                'stat_right_label' => ['nullable', 'string', 'max:120'],
                'tag_classes' => ['nullable', 'string', 'max:120'],
                'card_ring' => ['nullable', 'string', 'max:80'],
                'card_shadow' => ['nullable', 'string', 'max:120'],
                'cta_color' => ['nullable', 'string', 'max:80'],
                'heading_hover' => ['nullable', 'string', 'max:80'],
            ];
        }

        if ($jenis === BerandaSlide::JENIS_SIMPONI) {
            $rules['visual_mode'] = ['required', Rule::in([
                BerandaSlide::VISUAL_ASSET,
                BerandaSlide::VISUAL_IMAGE,
            ])];
            $rules['external_url'] = ['required', 'url', 'max:500'];
        }

        $validated = $request->validate($rules);

        $titleAttr = filled($validated['title_attr'] ?? null)
            ? $validated['title_attr']
            : Str::limit($validated['heading'], 120, '');

        $payload = [
            'jenis' => $validated['jenis'],
            'title_attr' => $titleAttr,
            'route_name' => filled($validated['route_name'] ?? null) ? $validated['route_name'] : null,
            'href' => filled($validated['href'] ?? null) ? $validated['href'] : null,
            'badge' => $validated['badge'] ?? null,
            'tags' => $this->tagsFromText($validated['tags_text'] ?? ''),
            'heading' => $validated['heading'],
            'description' => $validated['description'],
            'cta_text' => $validated['cta_text'],
            'image_alt' => $validated['image_alt'] ?? null,
        ];

        if ($jenis === BerandaSlide::JENIS_PERDA) {
            $payload['visual_mode'] = BerandaSlide::VISUAL_TEMPLATE;
            $payload['tags'] = $this->tagsFromText($validated['tags_text'] ?? '');

            return $payload;
        }

        if ($jenis === BerandaSlide::JENIS_SIMPONI) {
            $url = $validated['external_url'];
            $payload['visual_mode'] = $validated['visual_mode'];
            $payload['href'] = $url;
            $payload['config'] = array_merge($slide->exists ? ($slide->config ?? []) : [], [
                'external_url' => $url,
                'visual_asset' => ($slide->config ?? [])['visual_asset'] ?? 'images/logo-simponi.png',
            ]);

            if ($validated['visual_mode'] === BerandaSlide::VISUAL_IMAGE && ! $request->hasFile('image') && ! $existingImagePath) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'image' => 'Unggah gambar visual untuk mode gambar.',
                ]);
            }

            return $payload;
        }

        $payload['visual_mode'] = $validated['visual_mode'];
        $payload += [
            'left_gradient' => $validated['left_gradient'] ?? null,
            'hero_mode' => $validated['hero_mode'] ?? 'single',
            'hero_live_preset' => filled($validated['hero_live_preset'] ?? null) ? $validated['hero_live_preset'] : null,
            'show_stats' => $request->boolean('show_stats'),
            'hero_value' => $validated['hero_value'] ?? null,
            'hero_label' => $validated['hero_label'] ?? null,
            'hero_dual_left_value' => $validated['hero_dual_left_value'] ?? null,
            'hero_dual_left_label' => $validated['hero_dual_left_label'] ?? null,
            'hero_dual_right_value' => $validated['hero_dual_right_value'] ?? null,
            'hero_dual_right_label' => $validated['hero_dual_right_label'] ?? null,
            'stat_left_value' => $validated['stat_left_value'] ?? null,
            'stat_left_label' => $validated['stat_left_label'] ?? null,
            'stat_right_value' => $validated['stat_right_value'] ?? null,
            'stat_right_label' => $validated['stat_right_label'] ?? null,
            'tag_classes' => $validated['tag_classes'] ?? null,
            'card_ring' => $validated['card_ring'] ?? null,
            'card_shadow' => $validated['card_shadow'] ?? null,
            'cta_color' => $validated['cta_color'] ?? null,
            'heading_hover' => $validated['heading_hover'] ?? null,
        ];

        if ($validated['visual_mode'] === BerandaSlide::VISUAL_IMAGE && ! $request->hasFile('image') && ! $existingImagePath && ! $request->boolean('remove_image')) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'image' => 'Unggah gambar untuk panel visual.',
            ]);
        }

        if ($validated['visual_mode'] === BerandaSlide::VISUAL_LIVE && blank($payload['hero_live_preset'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'hero_live_preset' => 'Pilih preset statistik live untuk mode visual ini.',
            ]);
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
