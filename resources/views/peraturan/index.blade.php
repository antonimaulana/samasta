@extends('layouts.public')

@section('title', 'Peraturan Taman & Ruang Hijau')
@section('meta_description', 'Ringkasan Perda Ketertiban Umum Kota Batam tentang taman, jalur hijau, dan ruang terbuka hijau — larangan, kewajiban, dan sanksi dalam bahasa mudah dipahami.')

@section('hero')
    <section class="relative overflow-hidden bg-gradient-to-br from-teal-700 via-emerald-700 to-green-800 text-white">
        <div class="pointer-events-none absolute -right-16 top-0 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
        <div class="relative mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <p class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-bold uppercase tracking-widest text-emerald-100">
                <span aria-hidden="true">⚖️</span> Jelajahi · Peraturan
            </p>
            <h1 class="mt-4 text-3xl font-black leading-tight sm:text-4xl">
                Peraturan Taman &amp; Ruang Hijau
            </h1>
            <p class="mt-4 max-w-3xl text-base leading-relaxed text-emerald-50/95 sm:text-lg">
                {{ $pengantar }}
            </p>
            <p class="mt-4 max-w-3xl text-sm text-white/80">
                <span class="font-semibold text-white">Dasar hukum:</span>
                {{ $perdaUtama }}, diperbarui {{ $perdaPembaruan }}.
            </p>
        </div>
        <div class="relative -mb-1 text-[#f0fdf4]">
            <svg viewBox="0 0 1440 40" fill="currentColor" class="block w-full" aria-hidden="true">
                <path d="M0,20 Q360,40 720,20 T1440,20 L1440,40 L0,40 Z"/>
            </svg>
        </div>
    </section>
@endsection

@section('content')
    <div class="relative -mt-4 pb-16">
        <div class="grid gap-5 sm:grid-cols-2">
            @foreach ($aturan as $index => $item)
                <article class="flex flex-col overflow-hidden rounded-2xl border border-emerald-100 bg-white shadow-sm ring-1 ring-emerald-50 transition hover:shadow-md hover:shadow-emerald-100/60">
                    <div class="border-b border-emerald-50 bg-gradient-to-r from-emerald-50/80 to-teal-50/50 px-5 py-4">
                        <div class="flex items-start gap-3">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white text-2xl shadow-sm ring-1 ring-emerald-100" aria-hidden="true">
                                {{ $item['icon'] }}
                            </span>
                            <div class="min-w-0">
                                <span class="inline-block rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide
                                    {{ $item['jenis'] === 'Kewajiban' ? 'bg-sky-100 text-sky-800' : ($item['jenis'] === 'Ketertiban' ? 'bg-violet-100 text-violet-800' : 'bg-amber-100 text-amber-900') }}">
                                    {{ $item['jenis'] }}
                                </span>
                                <h2 class="mt-2 text-lg font-bold leading-snug text-emerald-950">
                                    {{ $index + 1 }}. {{ $item['title'] }}
                                </h2>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-1 flex-col px-5 py-4">
                        <p class="text-sm leading-relaxed text-gray-700">
                            {{ $item['text'] }}
                        </p>
                        <p class="mt-3 rounded-lg bg-lime-50 px-3 py-2 text-sm font-medium text-emerald-900 ring-1 ring-lime-100">
                            💡 {{ $item['ringkas'] }}
                        </p>
                    </div>
                </article>
            @endforeach
        </div>

        <section class="mt-10 overflow-hidden rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50 to-orange-50/80 p-6 sm:p-8">
            <h2 class="text-xl font-bold text-amber-950">Apa sanksinya jika melanggar?</h2>
            <p class="mt-2 text-sm leading-relaxed text-amber-900/90">
                Pelanggar ketertiban umum di area taman dan ruang hijau dapat dikenai tindakan sesuai Perda, antara lain:
            </p>
            <ul class="mt-4 flex flex-wrap gap-2">
                @foreach ($sanksi as $item)
                    <li class="rounded-full bg-white px-3 py-1.5 text-sm font-semibold text-amber-950 ring-1 ring-amber-200">
                        {{ $item }}
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="mt-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
            <h2 class="text-lg font-bold text-gray-900">Melihat pelanggaran di lapangan?</h2>
            <p class="mt-2 text-sm leading-relaxed text-gray-600">
                Laporkan kondisi taman rusak, sampah, bangunan liar, atau gangguan di area hijau melalui aduan resmi.
                Tim Disperakimtan Kota Batam akan menindaklanjuti sesuai kewenangan.
            </p>
            <div class="mt-5 flex flex-wrap gap-3">
                <a href="{{ route('aduan.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700">
                    📢 Buat Aduan Masyarakat
                </a>
                <a href="{{ route('tamans.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-white px-5 py-2.5 text-sm font-bold text-emerald-800 transition hover:bg-emerald-50">
                    🌳 Jelajahi Taman
                </a>
            </div>
        </section>

        <p class="mt-8 text-center text-xs leading-relaxed text-gray-500">
            Ringkasan informasi publik — bukan nasihat hukum. Rincian pasal dan ketentuan teknis merujuk
            {{ $sumber[0] ?? 'JDIH Pemkot Batam' }}.
        </p>
    </div>
@endsection
