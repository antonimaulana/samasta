@extends('layouts.admin')

@section('title', 'FAQ Beranda')
@section('header', 'FAQ Beranda')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-end gap-3">
        <a href="{{ route('home') }}#faq-beranda" target="_blank" rel="noopener"
           class="text-sm font-semibold text-green-700 hover:underline">
            Pratinjau beranda ↗
        </a>
        <a href="{{ route('admin.faq.create') }}"
           class="inline-flex items-center gap-2 rounded-xl bg-green-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-green-700">
            + Tambah pertanyaan
        </a>
    </div>

    <form action="{{ route('admin.faq.settings.update') }}" method="POST" class="mb-8 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        @csrf
        @method('PUT')
        <h2 class="text-sm font-bold text-gray-900">Judul bagian di beranda</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Badge</label>
                <input type="text" name="section_badge" value="{{ old('section_badge', $setting->section_badge) }}"
                       class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm">
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-medium text-gray-700">Judul</label>
                <input type="text" name="section_title" value="{{ old('section_title', $setting->section_title) }}" required
                       class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm">
            </div>
        </div>
        <button type="submit" class="mt-4 rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
            Simpan judul bagian
        </button>
    </form>

    <div class="space-y-3">
        @forelse ($faqs as $faq)
            <article @class([
                'rounded-2xl border bg-white p-4 shadow-sm',
                'border-green-200' => $faq->is_active,
                'border-gray-200 opacity-75' => ! $faq->is_active,
            ])>
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-gray-900">{{ $faq->question }}</p>
                        <p class="mt-1 line-clamp-2 text-sm text-gray-600">{{ $faq->answer }}</p>
                    </div>
                    <span @class([
                        'shrink-0 rounded-full px-2.5 py-1 text-xs font-bold',
                        'bg-green-100 text-green-800' => $faq->is_active,
                        'bg-gray-100 text-gray-600' => ! $faq->is_active,
                    ])>{{ $faq->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                </div>
                <div class="mt-3 flex flex-wrap gap-2">
                    <a href="{{ route('admin.faq.edit', $faq) }}"
                       class="rounded-lg bg-green-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-green-700">Edit</a>
                    <form action="{{ route('admin.faq.toggle', $faq) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50">
                            {{ $faq->is_active ? 'Sembunyikan' : 'Tampilkan' }}
                        </button>
                    </form>
                    <form action="{{ route('admin.faq.destroy', $faq) }}" method="POST"
                          onsubmit="return confirm('Hapus pertanyaan ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-lg border border-red-200 px-3 py-1.5 text-sm text-red-700 hover:bg-red-50">Hapus</button>
                    </form>
                </div>
            </article>
        @empty
            <p class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-8 text-center text-sm text-gray-600">
                Belum ada pertanyaan FAQ.
            </p>
        @endforelse
    </div>

    @if ($faqs->count() > 1)
        <form action="{{ route('admin.faq.reorder') }}" method="POST" class="mt-8 rounded-2xl border border-gray-200 bg-white p-5">
            @csrf
            <p class="text-sm font-semibold text-gray-800">Urutan tampil</p>
            <ol class="mt-3 space-y-2" id="faq-order-list">
                @foreach ($faqs as $faq)
                    <li class="flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50 px-4 py-3">
                        <span class="cursor-grab text-gray-400" aria-hidden="true">⋮⋮</span>
                        <input type="hidden" name="order[]" value="{{ $faq->id }}">
                        <span class="flex-1 text-sm font-medium text-gray-800">{{ $faq->question }}</span>
                    </li>
                @endforeach
            </ol>
            <button type="submit" class="mt-4 rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                Simpan urutan
            </button>
        </form>
    @endif
@endsection

@push('scripts')
    <script>
        (function () {
            const list = document.getElementById('faq-order-list');
            if (!list) return;
            let dragEl = null;
            list.querySelectorAll('li').forEach(function (item) {
                item.draggable = true;
                item.addEventListener('dragstart', function () {
                    dragEl = item;
                    item.classList.add('opacity-50');
                });
                item.addEventListener('dragend', function () {
                    item.classList.remove('opacity-50');
                    dragEl = null;
                });
                item.addEventListener('dragover', function (e) {
                    e.preventDefault();
                    if (!dragEl || dragEl === item) return;
                    const rect = item.getBoundingClientRect();
                    const next = (e.clientY - rect.top) / rect.height > 0.5;
                    list.insertBefore(dragEl, next ? item.nextSibling : item);
                });
            });
        })();
    </script>
@endpush
