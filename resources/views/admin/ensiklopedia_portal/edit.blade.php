@extends('layouts.admin')

@section('title', 'Portal Ensiklopedia')
@section('header', 'Portal & Interaktif')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.ensiklopedia.dashboard') }}" class="text-sm font-semibold text-green-700 hover:underline">← Ringkasan ensiklopedia</a>
    </div>

    <div class="mb-6 flex flex-wrap items-center justify-end gap-3">
        <a href="{{ route('ensiklopedia.index') }}" target="_blank" rel="noopener"
           class="text-sm font-semibold text-green-700 hover:underline">
            Pratinjau portal ↗
        </a>
    </div>

    <form action="{{ route('admin.ensiklopedia-portal.update') }}" method="POST" id="ensiklopedia-portal-form">
        @csrf
        @method('PUT')

        <div class="mb-6 flex flex-wrap gap-2 border-b border-gray-200 pb-2" role="tablist">
            @foreach (['hero' => 'Hero', 'musim' => 'Musim', 'tips' => 'Tips', 'fakta' => 'Fakta', 'kuis' => 'Kuis'] as $tab => $label)
                <button type="button" data-tab="{{ $tab }}"
                        class="portal-tab rounded-full px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div data-panel="hero" class="portal-panel space-y-4">
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900">Bagian atas halaman</h2>
                <div class="mt-4 grid gap-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Badge hero</label>
                        <input type="text" name="hero_badge" value="{{ old('hero_badge', $setting->hero_badge) }}" required
                               class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Judul</label>
                        <input type="text" name="hero_title" value="{{ old('hero_title', $setting->hero_title) }}" required
                               class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Deskripsi</label>
                        <textarea name="hero_description" rows="3" required class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm">{{ old('hero_description', $setting->hero_description) }}</textarea>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Placeholder pencarian</label>
                        <input type="text" name="search_placeholder" value="{{ old('search_placeholder', $setting->search_placeholder) }}" required
                               class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm">
                    </div>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Subjudul blok interaktif</label>
                            <input type="text" name="interaktif_subtitle" value="{{ old('interaktif_subtitle', $setting->interaktif_subtitle) }}" required class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-gray-700">Judul blok interaktif</label>
                            <input type="text" name="interaktif_title" value="{{ old('interaktif_title', $setting->interaktif_title) }}" required class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="mb-1 block text-sm font-medium text-gray-700">Label tombol kuis</label>
                            <input type="text" name="quiz_button_label" value="{{ old('quiz_button_label', $setting->quiz_button_label) }}" required class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm">
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div data-panel="musim" class="portal-panel hidden space-y-4">
            <div class="grid gap-4 md:grid-cols-2">
                @foreach (['kemarau' => ['label' => 'Musim kemarau', 'bg' => 'from-amber-50 to-orange-50 border-amber-200'], 'hujan' => ['label' => 'Musim hujan', 'bg' => 'from-sky-50 to-blue-50 border-sky-200']] as $key => $meta)
                    @php $src = $interaktif['musim_'.$key] ?? []; @endphp
                    <section class="rounded-2xl border bg-gradient-to-br p-6 {{ $meta['bg'] }}">
                        <h2 class="text-base font-bold text-gray-900">{{ $meta['label'] }}</h2>
                        <div class="mt-4 space-y-3">
                            <input type="text" name="musim_{{ $key }}_label" value="{{ old('musim_'.$key.'_label', $src['label'] ?? '') }}" required placeholder="Label" class="w-full rounded-xl border border-white/80 px-3 py-2 text-sm">
                            <input type="text" name="musim_{{ $key }}_icon" value="{{ old('musim_'.$key.'_icon', $src['icon'] ?? '') }}" required placeholder="Emoji" class="w-24 rounded-xl border border-white/80 px-3 py-2 text-sm">
                            <textarea name="musim_{{ $key }}_deskripsi" rows="3" required placeholder="Deskripsi" class="w-full rounded-xl border border-white/80 px-3 py-2 text-sm">{{ old('musim_'.$key.'_deskripsi', $src['deskripsi'] ?? '') }}</textarea>
                        </div>
                    </section>
                @endforeach
            </div>
        </div>

        <div data-panel="tips" class="portal-panel hidden space-y-6">
            @foreach ([
                'tips_kemarau' => ['Tips musim kemarau', $interaktif['tips_musim']['kemarau'] ?? []],
                'tips_hujan' => ['Tips musim hujan', $interaktif['tips_musim']['hujan'] ?? []],
                'tips_berkebun' => ['Tips berkebun umum', $interaktif['tips_berkebun'] ?? []],
            ] as $field => [$title, $defaultItems])
                @php $items = old($field, $defaultItems); @endphp
                <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm" data-tip-group="{{ $field }}">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-base font-bold text-gray-900">{{ $title }}</h2>
                        <button type="button" class="add-tip rounded-lg bg-green-600 px-3 py-1.5 text-xs font-bold text-white" data-target="{{ $field }}">+ Tambah tip</button>
                    </div>
                    <div class="tip-rows mt-4 space-y-3" id="{{ $field }}-rows">
                        @foreach ($items as $i => $tip)
                            @include('admin.ensiklopedia_portal._tip-row', ['field' => $field, 'index' => $i, 'tip' => $tip])
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>

        <div data-panel="fakta" class="portal-panel hidden">
            <section class="rounded-2xl border border-violet-200 bg-violet-50/40 p-6">
                <h2 class="text-base font-bold text-gray-900">Fakta edukasi (rotasi di beranda)</h2>
                <p class="mt-1 text-xs text-gray-600">Satu fakta per baris.</p>
                <textarea name="fakta_edukasi_text" rows="14" required class="mt-4 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm font-mono">{{ old('fakta_edukasi_text', implode("\n", $interaktif['fakta_edukasi'] ?? [])) }}</textarea>
            </section>
        </div>

        <div data-panel="kuis" class="portal-panel hidden space-y-4">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-600">Soal pilihan ganda untuk halaman kuis. Indeks jawaban dimulai dari 0 (opsi pertama = 0).</p>
                <button type="button" id="add-kuis" class="rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-bold text-white">+ Tambah soal</button>
            </div>
            <div id="kuis-rows" class="space-y-4">
                @php $kuisItems = old('kuis', $interaktif['kuis'] ?? []); @endphp
                @foreach ($kuisItems as $qi => $soal)
                    @include('admin.ensiklopedia_portal._kuis-row', ['index' => $qi, 'soal' => $soal])
                @endforeach
            </div>
        </div>

        <div class="sticky bottom-0 mt-8 flex justify-end border-t border-gray-200 bg-gray-50/95 py-4 backdrop-blur">
            <button type="submit" class="rounded-xl bg-green-600 px-8 py-3 text-sm font-bold text-white shadow-lg shadow-green-200 hover:bg-green-700">
                Simpan semua pengaturan
            </button>
        </div>
    </form>

    <template id="tip-row-template">
        @include('admin.ensiklopedia_portal._tip-row', ['field' => '__FIELD__', 'index' => '__INDEX__', 'tip' => ['icon' => '🌿', 'judul' => '', 'isi' => '']])
    </template>
    <template id="kuis-row-template">
        @include('admin.ensiklopedia_portal._kuis-row', ['index' => '__INDEX__', 'soal' => ['pertanyaan' => '', 'opsi' => ['', '', '', ''], 'jawaban' => 0, 'penjelasan' => '']])
    </template>
@endsection

@push('scripts')
    <script>
        (function () {
            const tabs = document.querySelectorAll('.portal-tab');
            const panels = document.querySelectorAll('.portal-panel');
            function showTab(name) {
                tabs.forEach(function (t) {
                    const active = t.dataset.tab === name;
                    t.classList.toggle('bg-green-600', active);
                    t.classList.toggle('text-white', active);
                    t.classList.toggle('text-gray-600', !active);
                });
                panels.forEach(function (p) {
                    p.classList.toggle('hidden', p.dataset.panel !== name);
                });
            }
            tabs.forEach(function (t) {
                t.addEventListener('click', function () { showTab(t.dataset.tab); });
            });
            showTab('hero');

            document.querySelectorAll('.add-tip').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const field = btn.dataset.target;
                    const container = document.getElementById(field + '-rows');
                    const index = container.querySelectorAll('.tip-row').length;
                    let html = document.getElementById('tip-row-template').innerHTML;
                    html = html.replaceAll('__FIELD__', field).replaceAll('__INDEX__', String(index));
                    container.insertAdjacentHTML('beforeend', html);
                });
            });

            document.getElementById('add-kuis')?.addEventListener('click', function () {
                const container = document.getElementById('kuis-rows');
                const index = container.querySelectorAll('.kuis-row').length;
                let html = document.getElementById('kuis-row-template').innerHTML;
                html = html.replaceAll('__INDEX__', String(index));
                container.insertAdjacentHTML('beforeend', html);
            });

            document.getElementById('ensiklopedia-portal-form')?.addEventListener('click', function (e) {
                if (e.target.classList.contains('remove-tip-row')) {
                    e.target.closest('.tip-row')?.remove();
                }
                if (e.target.classList.contains('remove-kuis-row')) {
                    e.target.closest('.kuis-row')?.remove();
                }
            });
        })();
    </script>
@endpush
