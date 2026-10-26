@php
    $href = $href ?? 'https://simponi.batam.go.id';
    $heading = $heading ?? 'Sistem Informasi Pemakaman Online (Simponi)';
    $description = $description ?? 'Ajukan dan kelola layanan pemakaman di Tempat Pemakaman Umum (TPU) Kota Batam secara online melalui portal resmi Pemerintah Kota Batam.';
    $badge = $badge ?? 'Layanan Pemakaman';
    $ctaText = $ctaText ?? 'Kunjungi simponi.batam.go.id';
    $tags = $tags ?? ['TPU Kota Batam', 'Layanan Online', 'Pemakaman'];
    $titleAttr = $titleAttr ?? 'Layanan Pemakaman Simponi';
    $imageUrl = $imageUrl ?? asset('images/logo-simponi.png');
    $imageAlt = $imageAlt ?? 'Simponi — Sistem Informasi Pemakaman Online Kota Batam';
@endphp

<div class="info-slide w-full flex-shrink-0" data-title="{{ $titleAttr }}">
    <div class="info-slide-card group relative flex h-full flex-col overflow-hidden rounded-2xl bg-white shadow-lg shadow-teal-200/60 ring-1 ring-teal-100 transition hover:-translate-y-0.5 hover:shadow-xl md:flex-row md:items-stretch">
        <a href="{{ $href }}"
           target="_blank"
           rel="noopener noreferrer"
           class="absolute inset-0 z-0 rounded-2xl"
           aria-label="Layanan Pemakaman Simponi — Kunjungi simponi.batam.go.id">
            <span class="sr-only">Kunjungi simponi.batam.go.id</span>
        </a>

        <div class="info-slide-simponi-logo info-slide-media info-slide-media--contain relative z-10 w-full bg-gradient-to-br from-slate-50 via-white to-teal-50 md:w-[38%] lg:w-[36%] pointer-events-none">
            <div class="info-slide-simponi-logo__frame">
                <img src="{{ $imageUrl }}"
                     alt="{{ $imageAlt }}"
                     width="340"
                     height="120"
                     class="info-slide-media__img">
            </div>
        </div>

        <div class="info-slide-right relative z-10 flex min-h-0 flex-1 flex-col pointer-events-none">
            <div class="flex-1">
                <span class="inline-flex rounded-full bg-teal-100 px-3 py-1 text-[10px] font-bold uppercase tracking-wide text-teal-800 sm:text-xs">
                    {{ $badge }}
                </span>
                <h3 class="mt-2 text-base font-bold leading-snug text-gray-900 transition group-hover:text-teal-700 sm:text-lg">
                    {{ $heading }}
                </h3>
                <p class="info-slide-description mt-1.5 text-sm leading-relaxed text-gray-600">
                    {{ $description }}
                </p>
                <div class="info-slide-tags flex flex-wrap gap-1.5">
                    @foreach ($tags as $tag)
                        <span class="rounded-full bg-teal-100 px-2.5 py-1 text-[11px] font-semibold text-teal-800 ring-1 ring-teal-200 sm:px-3 sm:text-xs">{{ $tag }}</span>
                    @endforeach
                </div>
            </div>

            <span class="info-slide-cta relative z-10 inline-flex items-center gap-2 text-sm font-bold text-teal-700 transition group-hover:gap-3">
                {{ $ctaText }}
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
            </span>
        </div>
    </div>
</div>
