@php
    $heroFeaturedTamans = $heroFeaturedTamans ?? collect();
@endphp

<section class="home-hero relative overflow-hidden text-white">
    <img src="{{ asset('images/hero-welcome-batam.jpg') }}"
         alt="Panorama Kota Batam — taman, masjid, dan teluk Batam"
         aria-hidden="true"
         fetchpriority="high"
         decoding="async"
         class="absolute inset-0 h-full w-full object-cover object-[42%_38%] sm:object-[45%_40%]">
    <div class="home-hero__overlay pointer-events-none absolute inset-0"></div>
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_20%_30%,rgba(255,255,255,0.08)_0%,transparent_45%)]"></div>

    <div class="relative mx-auto flex min-h-[inherit] max-w-7xl flex-col justify-center px-4 py-14 sm:px-6 lg:py-16 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-[minmax(0,1.05fr)_minmax(0,0.95fr)] lg:items-center lg:gap-8 xl:gap-12">
            <div class="max-w-xl">
                <div class="mb-5 inline-flex max-w-full items-center gap-2 rounded-full border border-white/25 bg-white/15 px-4 py-2 text-[10px] font-bold leading-snug tracking-wide text-white shadow-lg shadow-black/10 backdrop-blur-md sm:text-xs">
                    <span class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-white/25 text-xs">🌱</span>
                    <span class="hidden sm:inline">Dinas Perumahan, Kawasan Permukiman dan Pertamanan Kota Batam</span>
                    <span class="sm:hidden">Disperkimtan Kota Batam</span>
                </div>

                <h1 class="text-4xl font-black leading-[1.08] tracking-tight drop-shadow-lg sm:text-5xl lg:text-[3.25rem] lg:leading-[1.05]">
                    Temukan Taman
                    <span class="mt-1 block bg-gradient-to-r from-lime-200 via-emerald-100 to-white bg-clip-text text-transparent">
                        Favorit di Batam
                    </span>
                </h1>

                <p class="mt-4 max-w-lg text-base font-medium leading-relaxed text-white/90 drop-shadow sm:text-lg">
                    Portal profil pertamanan — jelajahi taman, fasilitas, dan peta interaktif taman di Kota Batam.
                </p>

                <div class="mt-7 w-full max-w-xl space-y-3">
                    <form action="{{ route('tamans.index') }}" method="GET"
                          class="overflow-hidden rounded-2xl bg-white p-2 shadow-2xl shadow-black/25 ring-1 ring-white/70">
                        <div class="flex flex-col gap-2 sm:flex-row">
                            <div class="relative flex-1">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-green-600">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                              d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 100-15 7.5 7.5 0 000 15z"/>
                                    </svg>
                                </span>
                                <input type="text" name="search" placeholder="Cari nama taman..."
                                       class="w-full rounded-xl border-0 bg-gray-50 py-3.5 pl-12 pr-4 text-gray-900 placeholder:text-gray-400 focus:bg-white focus:ring-2 focus:ring-lime-400">
                            </div>
                            <button type="submit"
                                    class="rounded-xl bg-gradient-to-r from-lime-500 to-green-600 px-8 py-3.5 text-sm font-bold uppercase tracking-wide text-white shadow-lg shadow-green-900/30 transition hover:from-lime-400 hover:to-green-500 active:scale-[0.98]">
                                Cari
                            </button>
                        </div>
                    </form>

                    <button type="button"
                            onclick="findNearbyTamans('{{ route('tamans.index') }}')"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-white/40 bg-white/10 px-5 py-3 text-sm font-bold text-white backdrop-blur-md transition hover:bg-white/20 sm:w-auto sm:justify-start">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Cari Taman Terdekat
                    </button>
                </div>
            </div>

            @if ($heroFeaturedTamans->isNotEmpty())
                <div class="w-full lg:max-w-md lg:justify-self-end xl:max-w-lg">
                    <div class="rounded-3xl border border-white/20 bg-black/20 p-3 shadow-2xl shadow-black/20 backdrop-blur-md sm:p-4">
                        <div class="mb-3 flex items-center justify-between gap-2 px-1">
                            <p class="text-xs font-bold uppercase tracking-wider text-white/90">Taman Unggulan</p>
                            <a href="{{ route('tamans.index') }}" class="text-xs font-semibold text-lime-200 hover:text-white">Lihat semua →</a>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            @foreach ($heroFeaturedTamans as $item)
                                @include('home.partials.hero-taman-card', [
                                    'taman' => $item['taman'],
                                    'label' => $item['label'],
                                ])
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
