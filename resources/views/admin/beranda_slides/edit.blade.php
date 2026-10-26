@extends('layouts.admin')

@section('title', ($isNew ?? false) ? 'Tambah Slide Beranda' : 'Edit Slide Beranda')
@section('header', ($isNew ?? false) ? 'Tambah Slide' : 'Edit Slide')

@section('content')
    @php $isNew = $isNew ?? ! $slide->exists; @endphp

    <div class="mb-4">
        <a href="{{ route('admin.beranda-slides.index') }}" class="text-sm font-semibold text-green-700 hover:underline">← Daftar slide</a>
    </div>

    <form action="{{ $isNew ? route('admin.beranda-slides.store') : route('admin.beranda-slides.update', $slide) }}"
          method="POST" enctype="multipart/form-data" class="grid gap-6 lg:grid-cols-5">
        @csrf
        @unless($isNew)
            @method('PUT')
        @endunless

        <div class="lg:col-span-3 space-y-5">
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-sm font-bold text-gray-900">Visual</h2>
                <div class="mt-3 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Jenis kartu</label>
                        <select name="jenis" id="slide-jenis" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-green-500 focus:ring-1 focus:ring-green-500">
                            @foreach ($jenisOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('jenis', $slide->jenis) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    @unless($slide->jenis === \App\Models\BerandaSlide::JENIS_PERDA)
                        <div id="visual-mode-wrap">
                            <label class="mb-1 block text-sm font-medium text-gray-700">Mode visual</label>
                            <select name="visual_mode" id="visual_mode" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-green-500 focus:ring-1 focus:ring-green-500">
                                @foreach ($visualModeOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('visual_mode', $slide->visual_mode ?? \App\Models\BerandaSlide::VISUAL_LIVE) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endunless

                    <div id="panel-live" class="sm:col-span-2 grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-gray-700">Preset statistik live</label>
                            <select name="hero_live_preset" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm">
                                @foreach ($livePresets as $value => $label)
                                    <option value="{{ $value }}" @selected(old('hero_live_preset', $slide->hero_live_preset ?? '') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-center gap-2 sm:col-span-2">
                            <input type="checkbox" name="show_stats" value="1" id="show_stats" @checked(old('show_stats', $slide->show_stats))
                                   class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                            <label for="show_stats" class="text-sm text-gray-700">Statistik tambahan</label>
                        </div>
                    </div>

                    <div id="panel-gradient" class="sm:col-span-2 grid gap-4 sm:grid-cols-2 hidden">
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-gray-700">Gradien (Tailwind)</label>
                            <input type="text" name="left_gradient" value="{{ old('left_gradient', $slide->left_gradient) }}"
                                   placeholder="from-lime-400 via-green-400 to-emerald-500"
                                   class="w-full rounded-xl border border-gray-200 px-4 py-2.5 font-mono text-xs focus:border-green-500 focus:ring-1 focus:ring-green-500">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Tampilan angka</label>
                            <select name="hero_mode" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm">
                                <option value="single" @selected(old('hero_mode', $slide->hero_mode) === 'single')>Satu angka</option>
                                <option value="dual" @selected(old('hero_mode', $slide->hero_mode) === 'dual')>Dua angka</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Angka / label</label>
                            <input type="text" name="hero_value" value="{{ old('hero_value', $slide->hero_value) }}"
                                   class="mb-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm">
                            <input type="text" name="hero_label" value="{{ old('hero_label', $slide->hero_label) }}"
                                   class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm">
                        </div>
                    </div>

                    <div id="panel-image" class="sm:col-span-2 space-y-3 hidden">
                        @if ($slide->image_path)
                            <div class="flex items-start gap-4 rounded-xl border border-gray-100 bg-gray-50 p-4">
                                <img src="{{ asset('storage/'.$slide->image_path) }}" alt="" class="h-24 w-auto max-w-[40%] rounded-lg object-contain">
                                <label class="flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="remove_image" value="1" class="rounded border-gray-300 text-red-600">
                                    Hapus gambar
                                </label>
                            </div>
                        @endif
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Gambar</label>
                            <input type="file" name="image" accept="image/*"
                                   class="w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-green-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Alt teks</label>
                            <input type="text" name="image_alt" value="{{ old('image_alt', $slide->image_alt) }}"
                                   class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm">
                        </div>
                    </div>

                    <div id="panel-simponi-url" class="sm:col-span-2 hidden">
                        <label class="mb-1 block text-sm font-medium text-gray-700">URL layanan</label>
                        <input type="url" name="external_url"
                               value="{{ old('external_url', $slide->config['external_url'] ?? $slide->href) }}"
                               class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm">
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-sm font-bold text-gray-900">Kategori &amp; tag</h2>
                <div class="mt-3 grid gap-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Kategori</label>
                        <input type="text" name="badge" value="{{ old('badge', $slide->badge) }}"
                               class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-green-500 focus:ring-1 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Tag</label>
                        <textarea name="tags_text" rows="2"
                                  class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-green-500 focus:ring-1 focus:ring-green-500">{{ old('tags_text', implode("\n", $slide->tags ?? [])) }}</textarea>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <label class="text-sm font-bold text-gray-900">Judul</label>
                <input type="text" name="heading" value="{{ old('heading', $slide->heading) }}" required
                       class="mt-3 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-green-500 focus:ring-1 focus:ring-green-500">
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <label class="text-sm font-bold text-gray-900">Deskripsi singkat</label>
                <textarea name="description" rows="4" required maxlength="600"
                          class="mt-3 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-green-500 focus:ring-1 focus:ring-green-500">{{ old('description', $slide->description) }}</textarea>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-sm font-bold text-gray-900">Tombol aksi</h2>
                <div class="mt-3 grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Teks tombol</label>
                        <input type="text" name="cta_text" value="{{ old('cta_text', $slide->cta_text) }}" required
                               class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-green-500 focus:ring-1 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Route internal</label>
                        <select name="route_name" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm">
                            @foreach ($routeOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('route_name', $slide->route_name ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">URL manual</label>
                        <input type="url" name="href" value="{{ old('href', $slide->href) }}"
                               class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm">
                    </div>
                </div>
            </section>

            <details class="rounded-2xl border border-gray-200 bg-gray-50 p-5">
                <summary class="cursor-pointer text-sm font-bold text-gray-800">Lanjutan</summary>
                <div class="mt-3 space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Judul aksesibilitas</label>
                        <input type="text" name="title_attr" value="{{ old('title_attr', $slide->title_attr) }}"
                               class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm">
                    </div>
                    <div id="panel-style" class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Kelas tag</label>
                            <input type="text" name="tag_classes" value="{{ old('tag_classes', $slide->tag_classes) }}"
                                   class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-xs font-mono">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Warna CTA</label>
                            <input type="text" name="cta_color" value="{{ old('cta_color', $slide->cta_color) }}"
                                   class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-xs font-mono">
                        </div>
                    </div>
                </div>
            </details>
        </div>

        <aside class="lg:col-span-2">
            <div class="sticky top-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $slide->is_active ?? true))
                           class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                    <span class="text-sm font-semibold text-gray-800">Tampilkan di beranda</span>
                </label>
                @unless($isNew)
                    <p class="mt-3 text-xs text-gray-500">Slug: <span class="font-mono text-gray-700">{{ $slide->slug }}</span></p>
                @endunless
                <button type="submit" class="mt-4 w-full rounded-xl bg-green-600 py-3 text-sm font-bold text-white hover:bg-green-700">
                    Simpan
                </button>
            </div>
        </aside>
    </form>
@endsection

@push('scripts')
    <script>
        (function () {
            const jenis = document.getElementById('slide-jenis');
            const visualMode = document.getElementById('visual_mode');
            const panelLive = document.getElementById('panel-live');
            const panelGradient = document.getElementById('panel-gradient');
            const panelImage = document.getElementById('panel-image');
            const panelSimponi = document.getElementById('panel-simponi-url');
            const panelStyle = document.getElementById('panel-style');

            function syncPanels() {
                const j = jenis ? jenis.value : '{{ $slide->jenis }}';
                const mode = visualMode ? visualMode.value : '';

                if (panelLive) panelLive.classList.add('hidden');
                if (panelGradient) panelGradient.classList.add('hidden');
                if (panelImage) panelImage.classList.add('hidden');
                if (panelSimponi) panelSimponi.classList.add('hidden');

                if (j === '{{ \App\Models\BerandaSlide::JENIS_PERDA }}') {
                    return;
                }

                if (j === '{{ \App\Models\BerandaSlide::JENIS_SIMPONI }}') {
                    if (panelSimponi) panelSimponi.classList.remove('hidden');
                    if (mode === '{{ \App\Models\BerandaSlide::VISUAL_IMAGE }}' && panelImage) {
                        panelImage.classList.remove('hidden');
                    }
                    return;
                }

                if (mode === '{{ \App\Models\BerandaSlide::VISUAL_LIVE }}' && panelLive) {
                    panelLive.classList.remove('hidden');
                } else if (mode === '{{ \App\Models\BerandaSlide::VISUAL_GRADIENT }}' && panelGradient) {
                    panelGradient.classList.remove('hidden');
                } else if (mode === '{{ \App\Models\BerandaSlide::VISUAL_IMAGE }}' && panelImage) {
                    panelImage.classList.remove('hidden');
                }

                if (panelStyle) {
                    panelStyle.classList.toggle('hidden', j !== '{{ \App\Models\BerandaSlide::JENIS_STANDARD }}');
                }
            }

            jenis?.addEventListener('change', syncPanels);
            visualMode?.addEventListener('change', syncPanels);
            syncPanels();
        })();
    </script>
@endpush
