@extends('layouts.public')

@section('title', $artikel->judul)

@php
    $wordCount = str_word_count(strip_tags($artikel->konten));
    $readMin = max(1, (int) ceil($wordCount / 180));
@endphp

@push('styles')
    <style>
        #reading-progress {
            transform-origin: left;
            transform: scaleX(0);
            transition: transform 0.1s linear;
        }
    </style>
@endpush

@section('content')
    {{-- Progress bar baca --}}
    <div class="fixed left-0 top-[65px] z-40 h-1 w-full bg-green-100">
        <div id="reading-progress" class="h-full bg-gradient-to-r from-lime-400 to-green-500"></div>
    </div>

    <nav class="mb-6 flex flex-wrap items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('ensiklopedia.index') }}" class="font-semibold transition hover:text-green-600">Ensiklopedia</a>
        <svg class="h-4 w-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('ensiklopedia.index') }}#{{ $artikel->kategori->slug }}"
           class="transition hover:text-green-600">{{ $artikel->kategori->nama }}</a>
        <svg class="h-4 w-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="font-semibold text-gray-700">{{ $artikel->judul }}</span>
    </nav>

    <div class="grid gap-8 lg:grid-cols-3">
        {{-- Sidebar edukasi --}}
        <aside class="order-2 lg:order-1 lg:col-span-1">
            <div class="sticky top-24 space-y-5">
                {{-- Meta baca --}}
                <div class="rounded-2xl border border-green-100 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Info Bacaan</p>
                    <ul class="mt-3 space-y-2 text-sm">
                        <li class="flex items-center gap-2 text-gray-700">
                            <span class="text-base">{{ $artikel->kategori->icon }}</span>
                            <span>{{ $artikel->kategori->nama }}</span>
                        </li>
                        <li class="flex items-center gap-2 text-gray-700">
                            <span>⏱️</span>
                            <span>~{{ $readMin }} menit baca</span>
                        </li>
                        <li class="flex items-center gap-2 text-gray-700">
                            <span>📄</span>
                            <span>{{ count($paragraf) }} bagian</span>
                        </li>
                    </ul>
                </div>

                {{-- Daftar isi --}}
                @if (count($paragraf) > 1)
                    <div class="rounded-2xl border border-green-100 bg-white p-5 shadow-sm">
                        <p class="text-xs font-bold uppercase tracking-wider text-green-600">📋 Daftar Isi</p>
                        <ol class="mt-3 space-y-2">
                            @foreach ($paragraf as $index => $p)
                                <li>
                                    <a href="#bagian-{{ $index + 1 }}"
                                       class="flex gap-2 text-sm text-gray-600 transition hover:text-green-600">
                                        <span class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-lime-100 text-[10px] font-bold text-green-700">{{ $index + 1 }}</span>
                                        <span class="line-clamp-2">{{ Str::limit($p, 60) }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                @endif

                {{-- Tips edukasi --}}
                <div class="rounded-2xl border border-lime-200 bg-gradient-to-br from-lime-50 to-green-50 p-5">
                    <p class="text-xs font-bold uppercase tracking-wider text-green-600">💡 Tips Belajar</p>
                    <p class="mt-2 text-sm leading-relaxed text-gray-600">
                        Baca dengan santai, catat poin penting, lalu terapkan pengetahuan dengan menjaga taman lingkungan atau menanam tanaman di rumah.
                    </p>
                </div>
            </div>
        </aside>

        {{-- Konten utama --}}
        <article class="order-1 lg:order-2 lg:col-span-2">
            <div class="overflow-hidden rounded-3xl border border-green-100 bg-white shadow-xl shadow-green-100/60">
                {{-- Header --}}
                <div class="relative overflow-hidden bg-gradient-to-br from-lime-400 via-green-400 to-emerald-500 px-6 py-10 sm:px-10">
                    <div class="pointer-events-none absolute -right-8 -top-8 h-40 w-40 rounded-full bg-white/15 blur-2xl"></div>
                    <span class="relative inline-flex items-center gap-2 rounded-full bg-white/25 px-3 py-1 text-xs font-bold uppercase tracking-wide backdrop-blur-sm">
                        {{ $artikel->kategori->icon }} {{ $artikel->kategori->nama }}
                    </span>
                    <div class="relative mt-4 flex items-start gap-4">
                        <div class="flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-2xl bg-white/25 text-4xl backdrop-blur-sm">
                            {{ $artikel->icon }}
                        </div>
                        <div>
                            <h1 class="text-2xl font-black text-white sm:text-3xl">{{ $artikel->judul }}</h1>
                            <p class="mt-2 text-sm leading-relaxed text-white/85">{{ $artikel->ringkas }}</p>
                        </div>
                    </div>
                </div>

                {{-- Intisari edukatif --}}
                <div class="border-b border-green-100 bg-gradient-to-r from-lime-50 to-green-50 px-6 py-5 sm:px-10">
                    <div class="flex gap-3">
                        <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-green-500 text-sm text-white">✦</span>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-green-600">Intisari Materi</p>
                            <p class="mt-1 text-sm leading-relaxed text-gray-700">{{ $artikel->ringkas }}</p>
                        </div>
                    </div>
                </div>

                {{-- Isi artikel --}}
                <div class="px-6 py-8 sm:px-10">
                    @foreach ($paragraf as $index => $p)
                        <section id="bagian-{{ $index + 1 }}" class="scroll-mt-28 {{ $index > 0 ? 'mt-8 border-t border-green-50 pt-8' : '' }}">
                            @if (count($paragraf) > 1)
                                <div class="mb-3 flex items-center gap-2">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-lime-400 text-xs font-black text-green-900">{{ $index + 1 }}</span>
                                    <span class="text-xs font-bold uppercase tracking-wider text-green-500">Bagian {{ $index + 1 }}</span>
                                </div>
                            @endif
                            <p class="text-base leading-[1.85] text-gray-700 {{ $index === 0 ? 'first-letter:float-left first-letter:mr-2 first-letter:text-5xl first-letter:font-black first-letter:text-green-500' : '' }}">
                                {{ $p }}
                            </p>
                        </section>
                    @endforeach

                    {{-- Poin kunci --}}
                    @if (count($paragraf) >= 2)
                        <div class="mt-10 rounded-2xl border border-green-200 bg-green-50 p-6">
                            <p class="text-xs font-bold uppercase tracking-wider text-green-700">🎯 Poin Kunci</p>
                            <ul class="mt-3 space-y-2">
                                @foreach (array_slice($paragraf, 0, 3) as $p)
                                    <li class="flex gap-2 text-sm text-gray-700">
                                        <span class="mt-1.5 h-1.5 w-1.5 flex-shrink-0 rounded-full bg-green-500"></span>
                                        {{ Str::limit($p, 120) }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Navigasi --}}
                    <div class="mt-10 flex flex-wrap gap-3 border-t border-green-100 pt-8">
                        <a href="{{ route('ensiklopedia.index') }}"
                           class="inline-flex items-center gap-2 rounded-xl border-2 border-green-200 px-5 py-2.5 text-sm font-bold text-green-700 transition hover:bg-lime-50">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Kembali
                        </a>
                        <a href="{{ route('ensiklopedia.index') }}#{{ $artikel->kategori->slug }}"
                           class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-lime-500 to-green-500 px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-green-400/30 transition hover:from-lime-400 hover:to-green-400">
                            Topik {{ $artikel->kategori->nama }}
                        </a>
                    </div>
                </div>
            </div>

            {{-- Artikel terkait --}}
            @if ($artikelTerkaits->isNotEmpty())
                <section class="mt-10">
                    <div class="mb-5 flex items-center gap-3">
                        <div class="h-6 w-1 rounded-full bg-gradient-to-b from-lime-400 to-green-500"></div>
                        <h2 class="text-lg font-black text-gray-900">Pelajari Juga</h2>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-3">
                        @foreach ($artikelTerkaits as $terkait)
                            <a href="{{ route('ensiklopedia.show', $terkait->slug) }}"
                               class="group rounded-2xl border border-green-100 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-green-300 hover:shadow-md">
                                <span class="text-2xl">{{ $terkait->icon }}</span>
                                <h3 class="mt-2 text-sm font-bold text-gray-900 group-hover:text-green-600">{{ $terkait->judul }}</h3>
                                <p class="mt-1 line-clamp-2 text-xs text-gray-500">{{ $terkait->ringkas }}</p>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </article>
    </div>
@endsection

@push('scripts')
    <script>
        onPageReady(function () {
            const bar = document.getElementById('reading-progress');
            if (!bar) return;

            function updateProgress() {
                const scrollTop = window.scrollY;
                const docHeight = document.documentElement.scrollHeight - window.innerHeight;
                const progress = docHeight > 0 ? Math.min(scrollTop / docHeight, 1) : 0;
                bar.style.transform = 'scaleX(' + progress + ')';
            }

            window.addEventListener('scroll', updateProgress, { passive: true });
            updateProgress();
        });
    </script>
@endpush
