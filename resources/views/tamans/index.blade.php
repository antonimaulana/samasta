@extends('layouts.public')

@section('title', 'Daftar Taman')

@section('meta_description', 'Daftar taman hijau di Kota Batam — cari berdasarkan nama, kategori, fasilitas, atau lokasi terdekat.')

@section('content')
    <div class="mb-8">
        <a href="{{ route('home') }}" class="mb-4 inline-flex items-center gap-1 text-sm text-emerald-700 hover:underline">
            ← Kembali ke beranda
        </a>
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="text-center sm:text-left">
                <h1 class="text-3xl font-bold text-emerald-950 sm:text-4xl">Jelajahi Taman</h1>
                <p class="mt-2 text-gray-600">Temukan taman hijau di Kota Batam — filter, peta interaktif, dan navigasi</p>
            </div>
            <a href="{{ route('tamans.map') }}"
               class="inline-flex items-center gap-2 rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-800 hover:bg-emerald-100">
                🗺️ Peta Taman
            </a>
        </div>
    </div>

    <div class="mb-8 overflow-hidden rounded-2xl border border-green-100 bg-white shadow-sm">
        <div class="border-b border-green-50 bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-4">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-green-800">Cari Taman</h2>
                    <p class="mt-1 text-sm text-green-700/80">Filter berdasarkan nama, fasilitas, atau lokasi Anda</p>
                </div>
                @unless ($nearbyMode)
                    <button type="button"
                            onclick="findNearbyTamans('{{ route('tamans.index') }}')"
                            class="inline-flex items-center gap-2 rounded-xl border border-emerald-300 bg-white px-4 py-2 text-sm font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-50">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Taman Terdekat
                    </button>
                @endunless
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

            <div>
                <p class="mb-3 text-sm font-medium text-gray-700">Filter Fasilitas</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ($popularFasilitas as $fasilitas)
                        @php $isChecked = in_array($fasilitas, $selectedFasilitas, true); @endphp
                        <label class="cursor-pointer">
                            <input type="checkbox"
                                   name="fasilitas[]"
                                   value="{{ $fasilitas }}"
                                   class="peer sr-only"
                                   @checked($isChecked)>
                            <span class="inline-flex items-center rounded-full border px-3.5 py-1.5 text-sm font-medium transition
                                         {{ $isChecked
                                             ? 'border-green-600 bg-green-600 text-white shadow-sm'
                                             : 'border-green-200 bg-green-50 text-green-800 hover:border-green-400 hover:bg-green-100' }}
                                         peer-focus-visible:ring-2 peer-focus-visible:ring-green-500 peer-focus-visible:ring-offset-2">
                                {{ $fasilitas }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3 pt-1">
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-green-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 100-15 7.5 7.5 0 000 15z"/>
                    </svg>
                    Cari Taman
                </button>

                @if ($search !== '' || count($selectedFasilitas) > 0 || $nearbyMode)
                    <a href="{{ route('tamans.index') }}"
                       class="rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50">
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
                <article class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition hover:border-green-200 hover:shadow-md">
                    @if ($taman->foto_url)
                        <img src="{{ $taman->foto_url }}" alt="{{ $taman->nama_taman }}"
                             class="h-48 w-full object-cover">
                    @else
                        <div class="flex h-48 items-center justify-center bg-green-100 text-green-700">
                            <span class="text-4xl">🌳</span>
                        </div>
                    @endif

                    <div class="p-5">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-teal-100 px-2.5 py-0.5 text-xs font-medium text-teal-800">{{ $taman->kategori }}</span>
                            @if ($nearbyMode && isset($taman->distance_km))
                                <span class="rounded-full bg-emerald-600 px-2.5 py-0.5 text-xs font-bold text-white">
                                    {{ \App\Support\GeoDistance::format($taman->distance_km) }}
                                </span>
                            @endif
                            @if ($taman->luasan > 0)
                                <span class="text-xs text-gray-500">{{ number_format($taman->luasan, 0, ',', '.') }} M²</span>
                            @endif
                        </div>
                        <h2 class="mt-2 text-lg font-semibold text-gray-900">{{ $taman->nama_taman }}</h2>
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

                        <a href="{{ route('tamans.show', $taman) }}"
                           class="mt-4 inline-block text-sm font-medium text-green-700 hover:underline">
                            Lihat detail →
                        </a>
                    </div>
                </article>
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

@push('scripts')
    <script>
        document.querySelectorAll('input[name="fasilitas[]"]').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                const badge = this.nextElementSibling;
                if (this.checked) {
                    badge.classList.remove('border-green-200', 'bg-green-50', 'text-green-800', 'hover:border-green-400', 'hover:bg-green-100');
                    badge.classList.add('border-green-600', 'bg-green-600', 'text-white', 'shadow-sm');
                } else {
                    badge.classList.add('border-green-200', 'bg-green-50', 'text-green-800', 'hover:border-green-400', 'hover:bg-green-100');
                    badge.classList.remove('border-green-600', 'bg-green-600', 'text-white', 'shadow-sm');
                }
            });
        });
    </script>
@endpush
