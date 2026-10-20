@extends('layouts.admin')

@section('title', 'Edit Slide Beranda')
@section('header', 'Edit Slide Card')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.beranda-slides.index') }}" class="text-sm font-semibold text-green-700 hover:underline">← Kembali ke daftar slide</a>
    </div>

    <form action="{{ route('admin.beranda-slides.update', $slide) }}" method="POST" class="grid gap-6 lg:grid-cols-5">
        @csrf
        @method('PUT')

        <div class="lg:col-span-3 space-y-6">
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-base font-bold text-gray-900">Identitas & tautan</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Judul aksesibilitas</label>
                        <input type="text" name="title_attr" value="{{ old('title_attr', $slide->title_attr) }}" required
                               class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-green-500 focus:ring-1 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Jenis tampilan</label>
                        <select name="jenis" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-green-500 focus:ring-1 focus:ring-green-500">
                            @foreach ($jenisOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('jenis', $slide->jenis) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Route internal</label>
                        <select name="route_name" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-green-500 focus:ring-1 focus:ring-green-500">
                            @foreach ($routeOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('route_name', $slide->route_name ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-gray-700">URL manual (jika route kosong)</label>
                        <input type="url" name="href" value="{{ old('href', $slide->href) }}"
                               class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-green-500 focus:ring-1 focus:ring-green-500"
                               placeholder="https://...">
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-base font-bold text-gray-900">Teks utama</h2>
                <div class="mt-4 space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Judul slide</label>
                        <input type="text" name="heading" value="{{ old('heading', $slide->heading) }}" required
                               class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-green-500 focus:ring-1 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Deskripsi</label>
                        <textarea name="description" rows="4" required
                                  class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-green-500 focus:ring-1 focus:ring-green-500">{{ old('description', $slide->description) }}</textarea>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Tombol CTA</label>
                        <input type="text" name="cta_text" value="{{ old('cta_text', $slide->cta_text) }}" required
                               class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-green-500 focus:ring-1 focus:ring-green-500">
                    </div>
                </div>
            </section>

            @if ($slide->jenis === \App\Models\BerandaSlide::JENIS_SIMPONI || old('jenis') === \App\Models\BerandaSlide::JENIS_SIMPONI)
                <section class="rounded-2xl border border-teal-200 bg-teal-50/50 p-6">
                    <h2 class="text-base font-bold text-teal-900">Simponi</h2>
                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">URL portal Simponi</label>
                            <input type="url" name="external_url"
                                   value="{{ old('external_url', $slide->config['external_url'] ?? $slide->href) }}" required
                                   class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Badge</label>
                            <input type="text" name="badge" value="{{ old('badge', $slide->badge) }}"
                                   class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Tag (pisah koma atau baris baru)</label>
                            <textarea name="tags_text" rows="3" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm">{{ old('tags_text', implode("\n", $slide->tags ?? [])) }}</textarea>
                        </div>
                    </div>
                </section>
            @endif

            @if ($slide->jenis === \App\Models\BerandaSlide::JENIS_STANDARD || old('jenis') === \App\Models\BerandaSlide::JENIS_STANDARD)
                <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-base font-bold text-gray-900">Gaya & statistik</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Badge</label>
                            <input type="text" name="badge" value="{{ old('badge', $slide->badge) }}"
                                   class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Preset data live</label>
                            <select name="hero_live_preset" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm">
                                @foreach ($livePresets as $value => $label)
                                    <option value="{{ $value }}" @selected(old('hero_live_preset', $slide->hero_live_preset ?? '') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-gray-700">Gradien panel kiri (Tailwind)</label>
                            <input type="text" name="left_gradient" value="{{ old('left_gradient', $slide->left_gradient) }}"
                                   placeholder="from-lime-400 via-green-400 to-emerald-500"
                                   class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-mono text-xs">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Mode hero</label>
                            <select name="hero_mode" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm">
                                <option value="single" @selected(old('hero_mode', $slide->hero_mode) === 'single')>Satu angka</option>
                                <option value="dual" @selected(old('hero_mode', $slide->hero_mode) === 'dual')>Dua angka</option>
                            </select>
                        </div>
                        <div class="flex items-center gap-2 pt-6">
                            <input type="checkbox" name="show_stats" value="1" id="show_stats" @checked(old('show_stats', $slide->show_stats))
                                   class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                            <label for="show_stats" class="text-sm text-gray-700">Tampilkan statistik bawah (jika preset live)</label>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-gray-700">Tag (pisah koma atau baris baru)</label>
                            <textarea name="tags_text" rows="3" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm">{{ old('tags_text', implode("\n", $slide->tags ?? [])) }}</textarea>
                        </div>
                    </div>
                </section>
            @endif
        </div>

        <aside class="lg:col-span-2">
            <div class="sticky top-6 space-y-4 rounded-2xl border border-green-100 bg-gradient-to-br from-lime-50 to-emerald-50 p-6 shadow-sm">
                <h2 class="text-sm font-bold uppercase tracking-wider text-green-800">Pratinjau ringkas</h2>
                <p class="text-xs text-gray-600">Perubahan tampil di beranda setelah disimpan. Statistik live diambil dari database operasional.</p>
                <dl class="mt-4 space-y-2 text-sm">
                    <div><dt class="text-gray-500">Slug</dt><dd class="font-mono text-gray-800">{{ $slide->slug }}</dd></div>
                    <div><dt class="text-gray-500">Status</dt><dd class="font-semibold">{{ $slide->is_active ? 'Aktif' : 'Nonaktif' }}</dd></div>
                </dl>
                <button type="submit" class="mt-4 w-full rounded-xl bg-green-600 py-3 text-sm font-bold text-white hover:bg-green-700">
                    Simpan perubahan
                </button>
            </div>
        </aside>
    </form>
@endsection
