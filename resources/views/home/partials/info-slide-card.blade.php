@props([
    'href',
    'titleAttr',
    'cardRing' => 'ring-green-100',
    'cardShadow' => 'shadow-green-200/60 hover:shadow-green-300/50',
    'leftGradient',
    'badge',
    'heroMode' => 'single',
    'heroValue' => '',
    'heroLabel' => '',
    'heroDualLeftValue' => '',
    'heroDualLeftLabel' => '',
    'heroDualRightValue' => '',
    'heroDualRightLabel' => '',
    'statLeftValue' => '',
    'statLeftLabel' => '',
    'statRightValue' => '',
    'statRightLabel' => '',
    'showStats' => true,
    'heading',
    'headingHover' => 'group-hover:text-green-600',
    'description',
    'tags' => [],
    'tagClasses' => 'bg-lime-100 text-green-700 ring-lime-200',
    'ctaText',
    'ctaColor' => 'text-green-600',
    'extra' => null,
    'externalLinkUrl' => null,
    'externalLinkLabel' => null,
])

@php
    $isExternalHref = is_string($href) && (str_starts_with($href, 'http://') || str_starts_with($href, 'https://'));
@endphp

<div class="info-slide w-full flex-shrink-0" data-title="{{ $titleAttr }}">
    <div @class([
        'info-slide-card group relative flex h-full flex-col overflow-hidden rounded-2xl bg-white shadow-lg ring-1 transition hover:-translate-y-0.5 hover:shadow-xl md:flex-row md:items-stretch',
        $cardRing,
        $cardShadow,
    ])>
        <a href="{{ $href }}"
           @if ($isExternalHref) target="_blank" rel="noopener noreferrer" @endif
           class="absolute inset-0 z-0 rounded-2xl"
           aria-label="{{ $heading }} — {{ $ctaText }}">
            <span class="sr-only">{{ $ctaText }}</span>
        </a>

        <div @class([
            'info-slide-left relative z-10 flex w-full flex-col justify-between p-5 text-white sm:p-6 md:w-[38%] md:flex-shrink-0 lg:w-[36%] pointer-events-none',
            'bg-gradient-to-br',
            $leftGradient,
        ])>
            <div class="absolute -right-8 -top-8 h-28 w-28 rounded-full bg-white/15 blur-2xl"></div>

            <span class="relative inline-flex w-fit rounded-full bg-white/25 px-3 py-1 text-[10px] font-bold uppercase tracking-wide backdrop-blur-sm sm:text-xs">
                {{ $badge }}
            </span>

            <div class="relative mt-5 flex flex-1 flex-col justify-center">
                @if ($heroMode === 'dual')
                    <div class="info-slide-metrics">
                        <div class="info-slide-metric">
                            <p class="info-slide-value drop-shadow-sm">{{ $heroDualLeftValue }}</p>
                            <p class="info-slide-label">{{ $heroDualLeftLabel }}</p>
                        </div>
                        <div class="info-slide-metrics__divider" aria-hidden="true"></div>
                        <div class="info-slide-metric">
                            <p class="info-slide-value drop-shadow-sm">{{ $heroDualRightValue }}</p>
                            <p class="info-slide-label">{{ $heroDualRightLabel }}</p>
                        </div>
                    </div>
                @else
                    <div class="info-slide-metric">
                        <p class="info-slide-value drop-shadow-sm">{{ $heroValue }}</p>
                        <p class="info-slide-label">{{ $heroLabel }}</p>
                    </div>
                @endif

                @if ($showStats)
                    <div class="info-slide-stats mt-5 border-t border-white/20 pt-5">
                        <div class="info-slide-metrics">
                            <div class="info-slide-metric">
                                <p class="info-slide-value">{{ $statLeftValue }}</p>
                                <p class="info-slide-label">{{ $statLeftLabel }}</p>
                            </div>
                            <div class="info-slide-metrics__divider" aria-hidden="true"></div>
                            <div class="info-slide-metric">
                                <p class="info-slide-value">{{ $statRightValue }}</p>
                                <p class="info-slide-label">{{ $statRightLabel }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="info-slide-right relative z-10 flex min-h-0 flex-1 flex-col p-5 sm:p-6 md:p-7 pointer-events-none">
            <div class="flex-1">
                <h3 @class(['text-lg font-bold leading-snug text-gray-900 transition sm:text-xl', $headingHover])>
                    {{ $heading }}
                </h3>
                <p class="mt-2 text-sm leading-relaxed text-gray-600">
                    {{ $description }}
                </p>
                @if ($tags !== [])
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach (array_slice($tags, 0, 4) as $tag)
                            <span @class(['rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 sm:px-3 sm:text-xs', $tagClasses])>{{ $tag }}</span>
                        @endforeach
                    </div>
                @endif
                @if ($extra)
                    <div class="mt-3 text-sm text-gray-500">
                        {!! $extra !!}
                    </div>
                @endif
                @if ($externalLinkUrl)
                    <p class="relative z-20 mt-4 rounded-xl border border-green-100 bg-green-50/80 px-3 py-2.5 text-sm leading-relaxed text-gray-700 pointer-events-auto">
                        Untuk layanan pemakaman, kunjungi
                        <a href="{{ $externalLinkUrl }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="font-bold text-green-700 underline decoration-green-300 underline-offset-2 hover:text-green-800">
                            {{ $externalLinkLabel ?? parse_url($externalLinkUrl, PHP_URL_HOST) }}
                        </a>.
                    </p>
                @endif
            </div>

            <span @class(['relative z-10 mt-5 inline-flex items-center gap-2 text-sm font-bold transition group-hover:gap-3 sm:mt-6', $ctaColor])>
                {{ $ctaText }}
                @if ($isExternalHref)
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                @else
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                @endif
            </span>
        </div>
    </div>
</div>
