@extends('layouts.admin')

@section('title', 'Slide Beranda')
@section('header', 'Slide Card Beranda')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-end gap-3">
        <a href="{{ route('home') }}#info-terkini-slider" target="_blank" rel="noopener"
           class="text-sm font-semibold text-green-700 hover:underline">
            Pratinjau beranda ↗
        </a>
        <a href="{{ route('admin.beranda-slides.create') }}"
           class="inline-flex items-center gap-2 rounded-xl bg-green-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-green-700">
            + Tambah slide
        </a>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        @forelse ($slides as $slide)
            <article @class([
                'relative overflow-hidden rounded-2xl border bg-white shadow-sm transition',
                'border-green-200 ring-2 ring-green-100' => $slide->is_active,
                'border-gray-200 opacity-75' => ! $slide->is_active,
            ])>
                <div class="absolute left-0 top-0 h-full w-1.5 bg-gradient-to-b from-lime-400 to-emerald-600"></div>
                <div class="p-5 pl-6">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">
                                #{{ $slide->urutan }}
                            </p>
                            <h2 class="mt-1 text-lg font-bold text-gray-900">{{ $slide->heading }}</h2>
                        </div>
                        <span @class([
                            'rounded-full px-2.5 py-1 text-xs font-bold',
                            'bg-green-100 text-green-800' => $slide->is_active,
                            'bg-gray-100 text-gray-600' => ! $slide->is_active,
                        ])>
                            {{ $slide->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <a href="{{ route('admin.beranda-slides.edit', $slide) }}"
                           class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">
                            Edit
                        </a>
                        <form action="{{ route('admin.beranda-slides.toggle', $slide) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                {{ $slide->is_active ? 'Sembunyikan' : 'Tampilkan' }}
                            </button>
                        </form>
                        <form action="{{ route('admin.beranda-slides.destroy', $slide) }}" method="POST"
                              onsubmit="return confirm('Hapus slide «{{ addslashes($slide->title_attr) }}»?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="rounded-lg border border-red-200 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <p class="lg:col-span-2 rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-8 text-center text-sm text-gray-600">
                Belum ada slide.
            </p>
        @endforelse
    </div>

    @if ($slides->count() > 1)
        <form action="{{ route('admin.beranda-slides.reorder') }}" method="POST" class="mt-8 rounded-2xl border border-gray-200 bg-white p-5">
            @csrf
            <p class="text-sm font-semibold text-gray-800">Urutan slide</p>
            <ol class="mt-3 space-y-2" id="slide-order-list">
                @foreach ($slides as $slide)
                    <li class="flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50 px-4 py-3">
                        <span class="cursor-grab text-gray-400" aria-hidden="true">⋮⋮</span>
                        <input type="hidden" name="order[]" value="{{ $slide->id }}">
                        <span class="flex-1 text-sm font-medium text-gray-800">{{ $slide->heading }}</span>
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
            const list = document.getElementById('slide-order-list');
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
