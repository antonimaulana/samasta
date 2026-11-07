@extends('layouts.public')

@section('title', 'Daftar Taman')

@section('meta_description', 'Daftar taman hijau di Kota Batam — cari berdasarkan nama, kategori, fasilitas, atau lokasi terdekat.')

@section('content')
    @php
        $navActionClass = 'inline-flex flex-1 items-center justify-center gap-2 rounded-xl border border-emerald-300 bg-white px-3 py-2 text-sm font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-50 sm:flex-none sm:px-4';
    @endphp
    <div class="mb-6">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-1 text-sm text-emerald-700 hover:underline">
            ← Kembali ke beranda
        </a>
    </div>

    <div class="mb-8 overflow-hidden rounded-2xl border border-green-100 bg-white shadow-sm">
        <div class="border-b border-green-50 bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <h2 class="text-lg font-semibold text-green-800">Cari Taman</h2>
                    <p class="mt-1 text-sm text-green-700/80">Filter berdasarkan nama, fasilitas, atau lokasi Anda</p>
                </div>
                <div class="flex w-full items-center justify-between gap-2 sm:w-auto sm:justify-end">
                    @unless ($nearbyMode)
                        <button type="button"
                                onclick="findNearbyTamans('{{ route('tamans.index') }}')"
                                class="{{ $navActionClass }}">
                            @include('tamans.partials.icon-location-outline')
                            Taman Terdekat
                        </button>
                    @endunless
                    <a href="{{ route('tamans.map') }}"
                       class="{{ $navActionClass }} {{ $nearbyMode ? 'w-full sm:w-auto' : '' }}">
                        @include('tamans.partials.icon-map-outline')
                        Peta Taman
                    </a>
                </div>
            </div>
        </div>

        @if ($nearbyMode)
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-emerald-100 bg-emerald-50 px-6 py-3">
                <p class="text-sm font-medium text-emerald-800">
                    📍 Menampilkan taman terdekat dari lokasi Anda
                </p>
                <a href="{{ route('tamans.index', array_filter(['search' => $search ?: null, 'fasilitas' => $selectedFasilitas ?: null])) }}"
                   class="text-sm font-semibold text-emerald-700 hover:underline">
                    Matikan mode terdekat
                </a>
            </div>
        @endif

        <form action="{{ route('tamans.index') }}" method="GET" class="space-y-5 p-6">
            @if ($nearbyMode)
                <input type="hidden" name="nearby" value="1">
                <input type="hidden" name="lat" value="{{ $userLat }}">
                <input type="hidden" name="lng" value="{{ $userLng }}">
            @endif
            <div>
                <label for="search" class="mb-2 block text-sm font-medium text-gray-700">Nama Taman</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-green-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 100-15 7.5 7.5 0 000 15z"/>
                        </svg>
                    </span>
                    <input type="text"
                           name="search"
                           id="search"
                           value="{{ $search }}"
                           placeholder="Contoh: Taman Majapahit, Taman KDA..."
                           class="w-full rounded-xl border border-gray-200 py-3 pl-10 pr-4 text-sm focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-500/20">
                </div>
            </div>

            @include('tamans.partials.fasilitas-filter-chips', [
                'popularFasilitas' => $popularFasilitas,
                'selectedFasilitas' => $selectedFasilitas,
            ])

            <div class="flex flex-wrap items-center justify-between gap-3 pt-1 sm:justify-start">
                <button type="submit"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-green-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 sm:flex-none">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 100-15 7.5 7.5 0 000 15z"/>
                    </svg>
                    Cari Taman
                </button>

                @if ($search !== '' || count($selectedFasilitas) > 0 || $nearbyMode)
                    <a href="{{ route('tamans.index') }}"
                       class="inline-flex flex-1 items-center justify-center rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50 sm:flex-none">
                        Reset Filter
                    </a>
                @endif
            </div>
        </form>
    </div>

    @if ($search !== '' || count($selectedFasilitas) > 0 || $nearbyMode)
        <div class="mb-6 flex flex-wrap items-center gap-2 text-sm text-gray-600">
            <span>Menampilkan <strong class="text-green-800">{{ $tamans->total() }}</strong> hasil</span>
            @if ($nearbyMode)
                <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-emerald-800">Terdekat dari lokasi Anda</span>
            @endif
            @if ($search !== '')
                <span class="rounded-full bg-green-100 px-2.5 py-0.5 text-green-800">"{{ $search }}"</span>
            @endif
            @foreach ($selectedFasilitas as $fasilitas)
                <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-emerald-800">{{ $fasilitas }}</span>
            @endforeach
        </div>
    @endif

    @if ($tamans->isEmpty())
        <div class="rounded-xl border border-gray-200 bg-white p-12 text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-green-100 text-3xl">🌿</div>
            @if ($search !== '' || count($selectedFasilitas) > 0 || $nearbyMode)
                <p class="font-medium text-gray-800">
                    @if ($nearbyMode)
                        Tidak ada taman dengan koordinat lokasi yang cocok dengan filter Anda.
                    @else
                        Tidak ada taman yang cocok dengan pencarian Anda.
                    @endif
                </p>
                <p class="mt-2 text-sm text-gray-500">Coba ubah kata kunci atau kurangi filter fasilitas.</p>
                <a href="{{ route('tamans.index') }}"
                   class="mt-4 inline-block text-sm font-medium text-green-700 hover:underline">
                    Lihat semua taman
                </a>
            @else
                <p class="text-gray-500">Belum ada data taman yang tersedia.</p>
            @endif
        </div>
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($tamans as $taman)
                <a href="{{ route('tamans.show', $taman) }}"
                   class="group flex flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition hover:border-green-300 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500 focus-visible:ring-offset-2">
                    @if ($taman->foto_url)
                        <img src="{{ $taman->foto_url }}" alt="{{ $taman->nama_taman }}"
                             class="h-48 w-full object-cover transition group-hover:brightness-[0.98]">
                    @else
                        <div class="flex h-48 items-center justify-center bg-green-100 text-green-700">
                            <span class="text-4xl" aria-hidden="true">🌳</span>
                        </div>
                    @endif

                    <div class="flex flex-1 flex-col p-5">
                        <div class="flex flex-wrap items-center gap-2">
                            <x-taman-kategori-badge :kategori="$taman->kategori" />
                            @if ($nearbyMode && isset($taman->distance_km))
                                <span class="rounded-full bg-emerald-600 px-2.5 py-0.5 text-xs font-bold text-white">
                                    {{ \App\Support\GeoDistance::format($taman->distance_km) }}
                                </span>
                            @endif
                            @if ($taman->luasan > 0)
                                <span class="text-xs text-gray-500">{{ $taman->luasanLabel() }} M²</span>
                            @endif
                        </div>
                        <h2 class="mt-2 text-lg font-semibold text-gray-900 group-hover:text-green-800">{{ $taman->nama_taman }}</h2>
                        <p class="mt-2 line-clamp-2 text-sm text-gray-600">{{ $taman->alamat }}</p>

                        @if ($taman->fasilitas_nama_list !== [])
                            <div class="mt-3 flex flex-wrap gap-1">
                                @foreach (array_slice($taman->fasilitas_nama_list, 0, 3) as $fasilitas)
                                    <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs text-green-800">{{ $fasilitas }}</span>
                                @endforeach
                                @if (count($taman->fasilitas_nama_list) > 3)
                                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600">+{{ count($taman->fasilitas_nama_list) - 3 }}</span>
                                @endif
                            </div>
                        @endif

                        <span class="mt-4 text-sm font-medium text-green-700 group-hover:underline">
                            Lihat detail →
                        </span>
                    </div>
                </a>
            @endforeach
        </div>

        @if ($tamans->hasPages())
            <div class="mt-8">
                {{ $tamans->links() }}
            </div>
        @endif
    @endif
@endsection

@include('tamans.partials.nearby-script')
@include('tamans.partials.fasilitas-filter-script')
