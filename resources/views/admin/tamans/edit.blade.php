@extends('layouts.admin')

@section('title', 'Edit Taman')
@section('header', 'Edit Taman')

@section('content')
    <div class="mx-auto max-w-5xl">
        <x-admin.rth-page-toolbar
            :back-url="route('admin.tamans.show', $taman)"
            back-label="← Kembali ke profil taman" />

        <form action="{{ route('admin.tamans.update', $taman) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('admin.tamans._form', ['taman' => $taman])
        </form>
    </div>

    @foreach ($taman->images as $image)
        <form id="delete-image-{{ $image->id }}"
              action="{{ route('admin.tamans.images.destroy', [$taman, $image]) }}"
              method="POST"
              class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endforeach

    @if ($taman->images->count() > 1)
        <div class="mx-auto mt-8 max-w-5xl">
            <form action="{{ route('admin.tamans.images.reorder', $taman) }}" method="POST"
                  class="rounded-2xl border border-emerald-200 bg-white p-5 shadow-sm">
                @csrf
                <p class="text-sm font-semibold text-gray-900">Urutan tampilan galeri</p>
                <p class="mt-1 text-sm text-gray-600">Seret baris untuk mengubah urutan. Foto di posisi #1 tampil pertama di beranda dan slider publik.</p>
                <ol class="mt-4 space-y-2" id="taman-gallery-order-list">
                    @foreach ($taman->images as $index => $image)
                        <li class="flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50 p-2">
                            <span class="cursor-grab select-none px-1 text-gray-400 active:cursor-grabbing" aria-hidden="true" title="Seret">⋮⋮</span>
                            <input type="hidden" name="order[]" value="{{ $image->id }}">
                            <img src="{{ $image->url }}" alt="" class="h-14 w-24 shrink-0 rounded-lg object-cover">
                            <span class="min-w-0 flex-1 text-sm font-medium text-gray-800">
                                Posisi #{{ $index + 1 }}
                                @if ($index === 0)
                                    <span class="ml-1 text-xs font-semibold text-emerald-700">(sampul)</span>
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ol>
                <button type="submit"
                        class="mt-4 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                    Simpan urutan foto
                </button>
            </form>
        </div>
    @endif

    @include('admin.tamans.partials.location-map-assets')

    @if ($taman->images->count() > 1)
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const list = document.getElementById('taman-gallery-order-list');
                    if (! list) return;

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
                            list.querySelectorAll('li').forEach(function (row, index) {
                                const label = row.querySelector('.flex-1');
                                if (! label) return;
                                const isCover = index === 0;
                                label.innerHTML = 'Posisi #' + (index + 1) + (isCover ? ' <span class="ml-1 text-xs font-semibold text-emerald-700">(sampul)</span>' : '');
                            });
                        });
                        item.addEventListener('dragover', function (event) {
                            event.preventDefault();
                            if (! dragEl || dragEl === item) return;
                            const rect = item.getBoundingClientRect();
                            const after = (event.clientY - rect.top) / rect.height > 0.5;
                            list.insertBefore(dragEl, after ? item.nextSibling : item);
                        });
                    });
                });
            </script>
        @endpush
    @endif
@endsection
