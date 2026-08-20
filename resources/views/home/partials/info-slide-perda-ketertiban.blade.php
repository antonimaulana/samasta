@php
    $href = $href ?? route('masukan.index');
    $larangan = \App\Support\PerdaKetertibanUmum::laranganTaman();
    $sanksi = \App\Support\PerdaKetertibanUmum::sanksi();
@endphp

<div class="info-slide w-full flex-shrink-0" data-title="Perda Ketertiban Umum">
    <div class="info-slide-card info-slide-card--perda group relative flex h-full flex-col overflow-hidden rounded-2xl bg-white shadow-lg shadow-emerald-200/60 ring-1 ring-emerald-100 transition hover:-translate-y-0.5 hover:shadow-xl md:flex-row md:items-stretch">
        <a href="{{ $href }}"
           class="absolute inset-0 z-0 rounded-2xl"
           aria-label="Ketertiban Umum di Taman dan Ruang Hijau — Laporkan pelanggaran">
            <span class="sr-only">Laporkan pelanggaran</span>
        </a>

        <div class="info-slide-left relative z-10 flex w-full flex-col justify-between bg-gradient-to-br from-teal-600 via-emerald-600 to-green-700 p-4 text-white sm:p-5 md:w-[32%] md:flex-shrink-0 lg:w-[30%] pointer-events-none">
            <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-white/15 blur-2xl"></div>

            <span class="relative inline-flex w-fit items-center gap-1.5 rounded-full bg-white/25 px-3 py-1 text-[10px] font-bold uppercase tracking-wide backdrop-blur-sm sm:text-xs">
                <span aria-hidden="true">⚖️</span>
                Peraturan Daerah
            </span>

            <div class="relative my-4 flex flex-1 flex-col justify-center md:my-2">
                <p class="text-sm font-semibold leading-snug text-white sm:text-base">
                    Ketentuan wajib bagi warga dalam menjaga keindahan, kebersihan, dan fungsi taman serta ruang hijau.
                </p>
                <p class="relative mt-4 border-t border-white/20 pt-4 text-[11px] leading-relaxed text-white/90 sm:text-xs">
                    <span class="font-bold">Dasar hukum:</span>
                    {{ \App\Support\PerdaKetertibanUmum::PERDA_UTAMA }},
                    diperbarui {{ \App\Support\PerdaKetertibanUmum::PERDA_PEMBARUAN }}.
                </p>
            </div>
        </div>

        <div class="info-slide-right relative z-10 flex min-h-0 flex-1 flex-col p-4 sm:p-5 md:p-6 pointer-events-none">
            <div class="flex-1">
                <h3 class="text-base font-bold leading-snug text-gray-900 transition group-hover:text-emerald-700 sm:text-lg md:text-xl">
                    Ketertiban Umum di Taman &amp; Ruang Hijau
                </h3>
                <p class="mt-1.5 text-xs leading-relaxed text-gray-600 sm:text-sm">
                    Pelanggaran dapat dikenai teguran, kerja sosial, denda administratif, hingga pembongkaran bangunan liar.
                </p>

                <ul class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3 lg:gap-2.5">
                    @foreach ($larangan as $item)
                        <li class="flex gap-2 rounded-xl border border-emerald-100/90 bg-emerald-50/50 px-3 py-2.5 sm:px-3.5">
                            <span class="mt-0.5 shrink-0 text-lg leading-none" aria-hidden="true">{{ $item['icon'] }}</span>
                            <div class="min-w-0">
                                <p class="text-sm font-bold leading-snug text-emerald-900">{{ $item['title'] }}</p>
                                <p class="mt-1 text-xs leading-relaxed text-gray-600 sm:text-sm">{{ $item['text'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>

                <div class="mt-3 flex flex-wrap gap-1.5">
                    @foreach (array_slice($sanksi, 0, 4) as $item)
                        <span class="rounded-full bg-amber-50 px-2.5 py-0.5 text-[11px] font-semibold text-amber-900 ring-1 ring-amber-200 sm:text-xs">
                            {{ $item }}
                        </span>
                    @endforeach
                </div>
            </div>

            <span class="relative z-10 mt-4 inline-flex items-center gap-2 text-sm font-bold text-emerald-700 transition group-hover:gap-3 sm:mt-5">
                Laporkan pelanggaran
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </span>
        </div>
    </div>
</div>
