@extends('layouts.public')

@section('title', 'Ensiklopedia')

@section('meta_description', 'Ensiklopedia '.config('app.name').' — artikel edukasi seputar pertamanan, tanaman, dan penghijauan Kota Batam.')

@push('styles')
    <style>
        @keyframes float-y {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }
        .animate-float { animation: float-y 4s ease-in-out infinite; }
        .article-card.hidden-by-search { display: none !important; }
        .category-section.hidden-by-search { display: none !important; }
    </style>
@endpush

@section('hero')
    <section class="relative overflow-hidden bg-gradient-to-br from-lime-400 via-green-400 to-emerald-500 text-white">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_20%_30%,rgba(255,255,255,0.22)_0%,transparent_50%)]"></div>
        <div class="pointer-events-none absolute -right-16 top-8 h-72 w-72 rounded-full bg-emerald-300/40 blur-3xl"></div>
        <div class="pointer-events-none absolute -left-10 bottom-0 h-64 w-64 rounded-full bg-lime-300/40 blur-3xl"></div>
        <div class="pointer-events-none absolute inset-0 opacity-20"
             style="background-image: radial-gradient(circle, white 1.5px, transparent 1.5px); background-size: 28px 28px;"></div>

        <div class="relative mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:py-16 lg:px-8">
            <div class="grid gap-10 lg:grid-cols-5 lg:items-center">
                <div class="lg:col-span-3">
                    <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-white/40 bg-white/20 px-4 py-2 text-xs font-bold uppercase tracking-widest backdrop-blur-sm">
                        <span class="text-lg">📚</span>
                        Pusat Pengetahuan Hijau
                    </div>
                    <h1 class="text-4xl font-black leading-tight drop-shadow-sm sm:text-5xl">
                        Ensiklopedia
                        <span class="mt-1 block bg-gradient-to-r from-yellow-200 via-lime-100 to-white bg-clip-text text-transparent">
                            {{ config('app.name') }}
                        </span>
                    </h1>
                    <p class="mt-4 max-w-2xl text-base leading-relaxed text-white/90 sm:text-lg">
                        Pelajari program pemerintah, jenis tanaman, dan tips penghijauan —
                        sumber edukasi resmi seputar pertamanan &amp; RTH Kota Batam.
                    </p>

                    {{-- Pencarian --}}
                    <div class="mt-8 overflow-hidden rounded-2xl bg-white p-2 shadow-2xl shadow-green-700/25 ring-2 ring-white/50">
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-green-500">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                          d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 100-15 7.5 7.5 0 000 15z"/>
                                </svg>
                            </span>
                            <input type="search" id="ensiklopedia-search" placeholder="Cari artikel, topik, atau kata kunci..."
                                   class="w-full rounded-xl border-0 bg-gray-50 py-3.5 pl-12 pr-4 text-gray-900 placeholder:text-gray-400 focus:bg-white focus:ring-2 focus:ring-lime-400">
                        </div>
                    </div>
                    <p id="search-empty" class="mt-3 hidden text-sm font-semibold text-yellow-100">
                        Tidak ada artikel yang cocok. Coba kata kunci lain.
                    </p>
                </div>

                {{-- Highlight artikel --}}
                <div class="lg:col-span-2">
                    @if ($highlightArtikel)
                        <a href="{{ route('ensiklopedia.show', $highlightArtikel->slug) }}"
                           class="animate-float group block overflow-hidden rounded-3xl border-2 border-white/40 bg-white/15 p-6 shadow-2xl backdrop-blur-md transition hover:bg-white/25 hover:shadow-green-600/30">
                            <span class="inline-flex rounded-full bg-yellow-300/90 px-3 py-1 text-[10px] font-black uppercase tracking-wider text-green-900">
                                ⭐ Rekomendasi Baca
                            </span>
                            <div class="mt-4 flex items-start gap-3">
                                <span class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-2xl bg-white/30 text-3xl">{{ $highlightArtikel->icon }}</span>
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wide text-white/70">{{ $highlightArtikel->kategori->nama }}</p>
                                    <h2 class="mt-1 text-lg font-black leading-snug group-hover:underline">{{ $highlightArtikel->judul }}</h2>
                                </div>
                            </div>
                            <p class="mt-3 line-clamp-2 text-sm leading-relaxed text-white/85">{{ $highlightArtikel->ringkas }}</p>
                            <span class="mt-4 inline-flex items-center gap-1 text-xs font-bold text-yellow-200 transition group-hover:gap-2">
                                Mulai baca sekarang →
                            </span>
                        </a>
                    @else
                        <div class="rounded-3xl border-2 border-white/30 bg-white/15 p-8 text-center backdrop-blur-md">
                            <p class="text-5xl">🌱</p>
                            <p class="mt-3 font-bold">Konten edukasi segera hadir</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="relative -mb-1 text-[#f0fdf4]">
            <svg viewBox="0 0 1440 80" fill="currentColor" class="block w-full">
                <path d="M0,40 C360,80 720,0 1080,40 C1260,60 1380,60 1440,40 L1440,80 L0,80 Z"/>
            </svg>
        </div>
    </section>
@endsection

@section('main_class', 'mx-auto max-w-7xl px-4 sm:px-6 lg:px-8')

@section('content')
    {{-- Stat + navigasi kategori --}}
    <section class="relative -mt-2 mb-12">
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-green-200 bg-gradient-to-br from-lime-400 to-green-500 p-5 text-white shadow-lg shadow-green-300/40">
                <p class="text-xs font-bold uppercase tracking-wider text-white/80">Artikel Edukasi</p>
                <p class="mt-1 text-4xl font-black">{{ $totalArtikel }}</p>
                <p class="mt-1 text-xs text-white/75">Materi bacaan tersedia</p>
            </div>
            <div class="rounded-2xl border border-green-100 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Topik</p>
                <p class="mt-1 text-4xl font-black text-green-700">{{ $kategori->count() }}</p>
                <p class="mt-1 text-xs text-gray-500">Kategori pengetahuan</p>
            </div>
            <div class="rounded-2xl border border-green-100 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Disusun oleh</p>
                <p class="mt-1 text-lg font-black text-green-700">Disperakimtan</p>
                <p class="mt-1 text-xs text-gray-500">Kota Batam</p>
            </div>
        </div>

        @if ($kategori->isNotEmpty())
            <div class="mt-8 overflow-x-auto rounded-2xl border border-green-100 bg-white p-3 shadow-sm">
                <p class="mb-2 px-1 text-xs font-bold uppercase tracking-wider text-gray-400">Loncat ke topik</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ($kategori as $kat)
                        <a href="#{{ $kat->slug }}"
                           class="inline-flex items-center gap-2 rounded-full border border-green-200 bg-green-50 px-4 py-2 text-sm font-semibold text-green-700 transition hover:border-green-400 hover:bg-lime-100">
                            <span>{{ $kat->icon }}</span>
                            {{ $kat->nama }}
                            <span class="rounded-full bg-green-200/80 px-1.5 py-0.5 text-[10px] font-bold">{{ $kat->artikelsPublished->count() }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </section>

    {{-- Ensiklopedia Interaktif --}}
    <section class="mb-14">
        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-400 to-purple-500 text-lg text-white shadow-md">✨</div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-violet-600">Interaktif</p>
                    <h2 class="text-2xl font-black text-gray-900">Belajar Sambil Berinteraksi</h2>
                </div>
            </div>
            <a href="{{ route('ensiklopedia.quiz') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-violet-500 to-purple-600 px-5 py-2.5 text-sm font-bold text-white shadow-md transition hover:from-violet-400 hover:to-purple-500">
                🧠 Mulai Kuis Tanaman
            </a>
        </div>

        <div class="grid gap-5 lg:grid-cols-3">
            {{-- Musim --}}
            <div @class([
                'overflow-hidden rounded-2xl border bg-gradient-to-br p-5 shadow-sm lg:col-span-1',
                'border-amber-200 from-amber-50 to-orange-50' => $musimInfo['warna'] === 'amber',
                'border-sky-200 from-sky-50 to-blue-50' => $musimInfo['warna'] !== 'amber',
            ])>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Artikel Musiman</p>
                <div class="mt-2 flex items-center gap-2">
                    <span class="text-3xl">{{ $musimInfo['icon'] }}</span>
                    <h3 class="text-lg font-black text-gray-900">{{ $musimInfo['label'] }}</h3>
                </div>
                <p class="mt-3 text-sm leading-relaxed text-gray-600">{{ $musimInfo['deskripsi'] }}</p>
                @if ($artikelMusiman->isNotEmpty())
                    <ul class="mt-4 space-y-2">
                        @foreach ($artikelMusiman->take(3) as $artikel)
                            <li>
                                <a href="{{ route('ensiklopedia.show', $artikel->slug) }}"
                                   class="flex items-center gap-2 rounded-lg bg-white/80 px-3 py-2 text-sm font-medium text-gray-800 transition hover:bg-white hover:text-green-700">
                                    <span>{{ $artikel->icon }}</span>
                                    <span class="line-clamp-1">{{ $artikel->judul }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Tips musim --}}
            <div class="rounded-2xl border border-green-100 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-green-600">Tips {{ $musimInfo['label'] }}</p>
                <div class="mt-3 space-y-3">
                    @foreach (\App\Support\EnsiklopediaInteraktif::tipsMusim() as $tip)
                        <div class="flex gap-3 rounded-xl bg-green-50/80 p-3">
                            <span class="text-xl">{{ $tip['icon'] }}</span>
                            <div>
                                <p class="text-sm font-bold text-gray-900">{{ $tip['judul'] }}</p>
                                <p class="mt-0.5 text-xs leading-relaxed text-gray-600">{{ $tip['isi'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Tips berkebun carousel --}}
            <div class="rounded-2xl border border-green-100 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-green-600">Tips Berkebun</p>
                <div id="tips-carousel" class="relative mt-3 min-h-[220px]">
                    @foreach (\App\Support\EnsiklopediaInteraktif::tipsBerkebun() as $tipIndex => $tip)
                        <div class="tips-slide {{ $tipIndex === 0 ? '' : 'hidden' }} rounded-xl border border-lime-100 bg-lime-50/50 p-4"
                             data-tip-index="{{ $tipIndex }}">
                            <span class="text-3xl">{{ $tip['icon'] }}</span>
                            <h4 class="mt-3 font-bold text-gray-900">{{ $tip['judul'] }}</h4>
                            <p class="mt-2 text-sm leading-relaxed text-gray-600">{{ $tip['isi'] }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="mt-3 flex items-center justify-between">
                    <button type="button" id="tips-prev" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50">←</button>
                    <span id="tips-indicator" class="text-xs text-gray-500">1 / {{ count(\App\Support\EnsiklopediaInteraktif::tipsBerkebun()) }}</span>
                    <button type="button" id="tips-next" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50">→</button>
                </div>
            </div>
        </div>
    </section>

    {{-- Mengapa belajar --}}
    <section class="mb-14">
        <div class="mb-6 flex items-center gap-3">
            <div class="h-8 w-1.5 rounded-full bg-gradient-to-b from-lime-400 to-green-500"></div>
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-green-500">Edukasi Lingkungan</p>
                <h2 class="text-2xl font-black text-gray-900">Mengapa Ensiklopedia Ini Penting?</h2>
            </div>
        </div>
        <div class="grid gap-4 sm:grid-cols-3">
            @foreach ([
                ['icon' => '🌍', 'title' => 'Pahami Peran RTH', 'desc' => 'Ketahui manfaat ruang hijau bagi kesehatan, iklim, dan kualitas hidup di kota.'],
                ['icon' => '🏛️', 'title' => 'Kenali Program Resmi', 'desc' => 'Pelajari kebijakan dan layanan pemerintah dalam penghijauan & pertamanan Batam.'],
                ['icon' => '🌳', 'title' => 'Pelajari Flora Lokal', 'desc' => 'Kenali jenis tanaman, perawatan, dan tips berkebun yang relevan dengan iklim setempat.'],
            ] as $item)
                <div class="rounded-2xl border border-green-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md hover:shadow-green-100">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-lime-100 to-green-100 text-xl">{{ $item['icon'] }}</div>
                    <h3 class="mt-4 font-bold text-gray-900">{{ $item['title'] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-gray-600">{{ $item['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Fakta menarik --}}
    <section class="mb-14 overflow-hidden rounded-3xl bg-gradient-to-r from-emerald-600 to-green-600 p-6 text-white shadow-xl shadow-green-400/30 sm:p-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-emerald-200">💡 Fakta Edukasi</p>
                <h2 class="mt-2 text-xl font-black sm:text-2xl">Tahukah Anda?</h2>
            </div>
            <div class="grid flex-1 gap-3 sm:grid-cols-3 lg:ml-8">
                @foreach (\App\Support\EnsiklopediaInteraktif::faktaEdukasi() as $fakta)
                    <div class="rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-sm leading-relaxed backdrop-blur-sm">
                        {{ $fakta }}
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Daftar artikel per kategori --}}
    @foreach ($kategori as $katIndex => $kat)
        <section id="{{ $kat->slug }}" class="category-section mb-16 scroll-mt-28" data-category="{{ $kat->nama }}">
            <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-lime-400 to-green-500 text-2xl text-white shadow-lg shadow-green-300/40">
                        {{ $kat->icon }}
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-green-500">Topik {{ $katIndex + 1 }}</p>
                        <h2 class="text-2xl font-black text-gray-900">{{ $kat->nama }}</h2>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">{{ $kat->deskripsi }}</p>
                    </div>
                </div>
                <span class="rounded-full bg-lime-100 px-3 py-1 text-xs font-bold text-green-700">
                    {{ $kat->artikelsPublished->count() }} artikel
                </span>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                @foreach ($kat->artikelsPublished as $artikel)
                    @php
                        $wordCount = str_word_count(strip_tags($artikel->konten));
                        $readMin = max(1, (int) ceil($wordCount / 180));
                    @endphp
                    <a href="{{ route('ensiklopedia.show', $artikel->slug) }}"
                       class="article-card group relative overflow-hidden rounded-2xl border border-green-100 bg-white shadow-sm transition hover:-translate-y-1 hover:border-green-300 hover:shadow-xl hover:shadow-green-200/50"
                       data-search="{{ strtolower($artikel->judul . ' ' . $artikel->ringkas . ' ' . $kat->nama) }}">
                        <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-gradient-to-br from-lime-100 to-green-100 opacity-60 transition group-hover:opacity-100"></div>
                        <div class="relative p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-lime-50 to-green-100 text-2xl transition group-hover:scale-110">
                                    {{ $artikel->icon }}
                                </div>
                                <span class="rounded-full bg-green-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-green-600">
                                    ~{{ $readMin }} mnt baca
                                </span>
                            </div>
                            <h3 class="mt-4 text-lg font-bold text-gray-900 transition group-hover:text-green-600">{{ $artikel->judul }}</h3>
                            <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-gray-500">{{ $artikel->ringkas }}</p>
                            <div class="mt-4 flex items-center justify-between border-t border-green-50 pt-4">
                                <span class="text-xs font-semibold text-green-600">{{ $kat->icon }} {{ $kat->nama }}</span>
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-green-600 transition group-hover:gap-2">
                                    Pelajari
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endforeach

    @if ($kategori->isEmpty())
        <div class="rounded-3xl border border-green-100 bg-white p-16 text-center shadow-sm">
            <p class="text-6xl">📚</p>
            <p class="mt-4 text-xl font-black text-gray-800">Belum ada konten ensiklopedia</p>
            <p class="mt-2 text-gray-500">Konten edukasi akan segera ditambahkan oleh admin.</p>
        </div>
    @endif

    {{-- CTA edukasi --}}
    @if ($kategori->isNotEmpty())
        <section class="mb-8 overflow-hidden rounded-3xl border border-green-200 bg-white shadow-lg">
            <div class="grid md:grid-cols-2">
                <div class="bg-gradient-to-br from-lime-400 to-green-500 p-8 text-white">
                    <p class="text-xs font-bold uppercase tracking-widest text-white/80">Langkah Selanjutnya</p>
                    <h2 class="mt-2 text-2xl font-black">Terapkan Pengetahuan Anda</h2>
                    <p class="mt-3 text-sm leading-relaxed text-white/90">
                        Setelah membaca, jelajahi taman-taman di Batam atau pelajari data RTH resmi untuk melihat penghijauan nyata di kota.
                    </p>
                </div>
                <div class="flex flex-col justify-center gap-3 p-8">
                    <a href="{{ route('tamans.index') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-lime-500 to-green-500 px-6 py-3 text-sm font-bold text-white shadow-md transition hover:from-lime-400 hover:to-green-400">
                        🌳 Jelajahi Taman
                    </a>
                    <a href="{{ route('rth.index') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-xl border-2 border-green-200 px-6 py-3 text-sm font-bold text-green-700 transition hover:bg-lime-50">
                        📊 Lihat Data RTH
                    </a>
                </div>
            </div>
        </section>
    @endif
@endsection

@push('scripts')
    <script>
        onPageReady(function () {
            const input = document.getElementById('ensiklopedia-search');
            const emptyMsg = document.getElementById('search-empty');
            const cards = document.querySelectorAll('.article-card');
            const sections = document.querySelectorAll('.category-section');

            if (!input) return;

            input.addEventListener('input', function () {
                const query = this.value.trim().toLowerCase();
                let visibleCount = 0;

                cards.forEach(function (card) {
                    const match = !query || card.dataset.search.includes(query);
                    card.classList.toggle('hidden-by-search', !match);
                    if (match) visibleCount++;
                });

                sections.forEach(function (section) {
                    const visibleInSection = section.querySelectorAll('.article-card:not(.hidden-by-search)').length;
                    section.classList.toggle('hidden-by-search', visibleInSection === 0);
                });

                emptyMsg?.classList.toggle('hidden', visibleCount > 0 || !query);
            });

            // Tips berkebun carousel
            const tipsSlides = document.querySelectorAll('.tips-slide');
            const tipsIndicator = document.getElementById('tips-indicator');
            let tipsCurrent = 0;

            function showTip(index) {
                if (tipsSlides.length === 0) return;
                tipsCurrent = (index + tipsSlides.length) % tipsSlides.length;
                tipsSlides.forEach(function (slide, i) {
                    slide.classList.toggle('hidden', i !== tipsCurrent);
                });
                if (tipsIndicator) {
                    tipsIndicator.textContent = (tipsCurrent + 1) + ' / ' + tipsSlides.length;
                }
            }

            document.getElementById('tips-prev')?.addEventListener('click', function () { showTip(tipsCurrent - 1); });
            document.getElementById('tips-next')?.addEventListener('click', function () { showTip(tipsCurrent + 1); });

            if (tipsSlides.length > 1) {
                setInterval(function () { showTip(tipsCurrent + 1); }, 7000);
            }
        });
    </script>
@endpush
