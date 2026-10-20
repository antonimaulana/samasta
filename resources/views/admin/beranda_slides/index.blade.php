@extends('layouts.admin')

@section('title', 'Slide Beranda')
@section('header', 'Slide Card Beranda')

@section('content')
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div class="max-w-2xl">
            <p class="text-sm text-gray-600">
                Atur konten slider <strong>Informasi Terkini</strong> di halaman publik. Statistik (RTH, aduan, dll.) tetap dihitung otomatis jika preset live dipilih.
            </p>
            <a href="{{ route('home') }}#info-terkini-slider" target="_blank" rel="noopener"
               class="mt-2 inline-flex items-center gap-1 text-sm font-semibold text-green-700 hover:underline">
                Lihat di beranda ↗
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid gap-4 lg:grid-cols-2">
        @foreach ($slides as $slide)
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
                                Urutan {{ $slide->urutan }} · {{ strtoupper($slide->jenis) }}
                            </p>
                            <h2 class="mt-1 text-lg font-bold text-gray-900">{{ $slide->heading }}</h2>
                            <p class="mt-1 line-clamp-2 text-sm text-gray-600">{{ $slide->description }}</p>
                        </div>
                        <span @class([
                            'rounded-full px-2.5 py-1 text-xs font-bold',
                            'bg-green-100 text-green-800' => $slide->is_active,
                            'bg-gray-100 text-gray-600' => ! $slide->is_active,
                        ])>
                            {{ $slide->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>

                    @if ($slide->hero_live_preset)
                        <p class="mt-3 inline-flex items-center gap-1 rounded-lg bg-violet-50 px-2.5 py-1 text-xs font-semibold text-violet-700">
                            ⚡ Data live: {{ $slide->hero_live_preset }}
                        </p>
                    @endif

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <a href="{{ route('admin.beranda-slides.edit', $slide) }}"
                           class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">
                            Edit konten
                        </a>
                        <form action="{{ route('admin.beranda-slides.toggle', $slide) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                {{ $slide->is_active ? 'Sembunyikan' : 'Tampilkan' }}
                            </button>
                        </form>
                    </div>
                </div>
            </article>
        @endforeach
    </div>

    @if ($slides->count() > 1)
        <form action="{{ route('admin.beranda-slides.reorder') }}" method="POST" class="mt-8 rounded-2xl border border-gray-200 bg-gray-50 p-5">
            @csrf
            <p class="text-sm font-semibold text-gray-800">Urutan tampil (atas = pertama)</p>
            <p class="mt-1 text-xs text-gray-500">Seret ID sesuai urutan di bawah, lalu simpan.</p>
            <ol class="mt-4 space-y-2" id="slide-order-list">
                @foreach ($slides as $slide)
                    <li class="flex items-center gap-3 rounded-xl border border-white bg-white px-4 py-3 shadow-sm">
                        <span class="cursor-grab text-gray-400" aria-hidden="true">⋮⋮</span>
                        <input type="hidden" name="order[]" value="{{ $slide->id }}">
                        <span class="flex-1 text-sm font-medium text-gray-800">{{ $slide->title_attr }}</span>
                        <span class="text-xs text-gray-400">#{{ $slide->id }}</span>
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
