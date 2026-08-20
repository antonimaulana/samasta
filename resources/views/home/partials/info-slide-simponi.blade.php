@php
    $href = $href ?? 'https://simponi.batam.go.id';
@endphp

<div class="info-slide w-full flex-shrink-0" data-title="Layanan Pemakaman Simponi">
    <div class="info-slide-card group relative flex h-full flex-col overflow-hidden rounded-2xl bg-white shadow-lg shadow-teal-200/60 ring-1 ring-teal-100 transition hover:-translate-y-0.5 hover:shadow-xl md:flex-row md:items-stretch">
        <a href="{{ $href }}"
           target="_blank"
           rel="noopener noreferrer"
           class="absolute inset-0 z-0 rounded-2xl"
           aria-label="Layanan Pemakaman Simponi — Kunjungi simponi.batam.go.id">
            <span class="sr-only">Kunjungi simponi.batam.go.id</span>
        </a>

        <div class="relative z-10 flex w-full items-center justify-center bg-gradient-to-br from-slate-50 via-white to-teal-50 p-6 sm:p-8 md:w-[38%] md:flex-shrink-0 lg:w-[36%] pointer-events-none">
            <div class="relative w-full max-w-[15rem] rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100 sm:max-w-[17rem] sm:p-5">
                <img src="{{ asset('images/logo-simponi.png') }}"
                     alt="Simponi — Sistem Informasi Pemakaman Online Kota Batam"
                     width="340"
                     height="120"
                     class="h-auto w-full object-contain">
            </div>
        </div>

        <div class="relative z-10 flex min-h-0 flex-1 flex-col p-5 sm:p-6 md:p-7 pointer-events-none">
            <div class="flex-1">
                <span class="inline-flex rounded-full bg-teal-100 px-3 py-1 text-[10px] font-bold uppercase tracking-wide text-teal-800 sm:text-xs">
                    Layanan Pemakaman
                </span>
                <h3 class="mt-3 text-lg font-bold leading-snug text-gray-900 transition group-hover:text-teal-700 sm:text-xl">
                    Sistem Informasi Pemakaman Online (Simponi)
                </h3>
                <p class="mt-2 text-sm leading-relaxed text-gray-600">
                    Ajukan dan kelola layanan pemakaman di Tempat Pemakaman Umum (TPU) Kota Batam secara online melalui portal resmi Pemerintah Kota Batam.
                </p>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach (['TPU Kota Batam', 'Layanan Online', 'Pemakaman'] as $tag)
                        <span class="rounded-full bg-teal-100 px-2.5 py-1 text-[11px] font-semibold text-teal-800 ring-1 ring-teal-200 sm:px-3 sm:text-xs">{{ $tag }}</span>
                    @endforeach
                </div>
            </div>

            <span class="relative z-10 mt-5 inline-flex items-center gap-2 text-sm font-bold text-teal-700 transition group-hover:gap-3 sm:mt-6">
                Kunjungi simponi.batam.go.id
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
            </span>
        </div>
    </div>
</div>
