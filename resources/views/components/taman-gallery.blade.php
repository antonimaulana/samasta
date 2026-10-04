@props([
    'taman',
    'variant' => 'public',
    'showHeader' => true,
    'embedded' => false,
    'nested' => false,
    'constrained' => true,
])

@php
    $taman->loadMissing('images');
    $items = collect($taman->gallery_urls)
        ->map(fn (string $url) => ['url' => $url, 'alt' => $taman->nama_taman])
        ->all();
    $count = count($items);
    $galleryId = 'taman-gallery-'.$taman->id;
    $isPublic = $variant === 'public';
    $useSlider = $count > 1;

    $shellClass = $isPublic
        ? 'border-green-50 bg-gradient-to-r from-green-50 to-emerald-50'
        : 'border-gray-200 bg-white';
    $titleClass = $isPublic ? 'text-green-800' : 'text-gray-900';
    $badgeClass = $isPublic
        ? 'bg-green-100 text-green-800'
        : 'bg-gray-100 text-gray-700';
    $frameClass = $nested
        ? ((! $isPublic && $constrained) ? 'border border-gray-200' : '')
        : ($isPublic ? 'ring-1 ring-green-100' : 'border border-gray-200');
    $navClass = $isPublic ? '!text-green-700' : '!text-gray-600';
    $emptyClass = $isPublic
        ? 'bg-gradient-to-br from-lime-100 via-green-100 to-emerald-100 text-7xl'
        : ($nested
            ? ((! $isPublic && $constrained)
                ? 'border border-gray-200 bg-gray-50 text-sm text-gray-400'
                : 'bg-gray-50 text-sm text-gray-400')
            : 'border border-dashed border-gray-200 bg-gray-50 text-sm text-gray-400');

    $outerClass = $nested
        ? ''
        : ($embedded
            ? 'border-b '.$shellClass.($isPublic ? ' p-4 sm:p-6' : ' p-5')
            : 'overflow-hidden rounded-xl border border-gray-200 '.$shellClass.($isPublic ? ' p-4 sm:p-6' : ' p-5 shadow-sm'));

    $contentClass = ($isPublic || ! $constrained)
        ? 'w-full'
        : 'mx-auto w-full max-w-xl';

    $frameRadiusClass = ($nested && ! $isPublic && $constrained)
        ? 'rounded-lg'
        : ($nested ? '' : 'rounded-xl');
    $imageClass = 'aspect-[16/9] h-full w-full object-cover object-center';
    $headerClass = $nested && ! $isPublic
        ? 'text-xs font-semibold uppercase tracking-wide text-gray-500'
        : ($isPublic ? 'text-lg font-bold' : 'text-base font-semibold').' '.$titleClass;
    $headerWrapperClass = $nested && $isPublic
        ? 'mb-3 flex items-center justify-between gap-2 px-4 pt-4 sm:px-6 sm:pt-6'
        : ($nested && ! $isPublic
            ? 'mb-2 flex items-center justify-between gap-2'
            : 'mb-3 flex items-center justify-between gap-2');
@endphp

<div {{ $attributes->merge(['class' => $outerClass]) }}>
    @if ($showHeader)
        <div class="{{ $headerWrapperClass }}">
            <h2 class="{{ $headerClass }}">
                Galeri Foto
            </h2>
            @if ($count > 0)
                <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $badgeClass }}">
                    {{ $count }} foto
                </span>
            @endif
        </div>
    @endif

    <div class="{{ $contentClass }}">
        @if ($count === 0)
            <div class="flex aspect-[16/9] w-full items-center justify-center {{ $frameRadiusClass }} {{ $emptyClass }}">
                @if ($isPublic)
                    <span aria-hidden="true">🌳</span>
                @else
                    Tidak ada foto
                @endif
            </div>
        @elseif (! $useSlider)
            <div class="overflow-hidden {{ $frameRadiusClass }} {{ $frameClass }}">
                <img src="{{ $items[0]['url'] }}"
                     alt="{{ $items[0]['alt'] }}"
                     width="{{ \App\Models\Taman::GALLERY_RECOMMENDED_WIDTH }}"
                     height="{{ \App\Models\Taman::GALLERY_RECOMMENDED_HEIGHT }}"
                     decoding="async"
                     fetchpriority="high"
                     class="{{ $imageClass }}">
            </div>
        @else
            <div class="relative {{ $frameRadiusClass }} {{ $frameClass }}">
                <div class="swiper {{ $galleryId }}-swiper aspect-[16/9] w-full overflow-hidden">
                    <div class="swiper-wrapper">
                        @foreach ($items as $index => $item)
                            <div class="swiper-slide !h-auto">
                                <img src="{{ $item['url'] }}"
                                     alt="{{ $item['alt'] }} — foto {{ $index + 1 }}"
                                     width="{{ \App\Models\Taman::GALLERY_RECOMMENDED_WIDTH }}"
                                     height="{{ \App\Models\Taman::GALLERY_RECOMMENDED_HEIGHT }}"
                                     decoding="async"
                                     @if ($index === 0) fetchpriority="high" @else loading="lazy" @endif
                                     class="{{ $imageClass }}">
                            </div>
                        @endforeach
                    </div>
                    <div class="swiper-button-prev {{ $navClass }} {{ (!$isPublic ? '!h-8 !w-8' : '') }}"></div>
                    <div class="swiper-button-next {{ $navClass }} {{ (!$isPublic ? '!h-8 !w-8' : '') }}"></div>
                    <div class="swiper-pagination {{ $isPublic ? '!bottom-3' : '!bottom-2' }}"></div>
                </div>
            </div>

        @once
            @push('styles')
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
                <style>
                    [class*="-swiper"] .swiper-button-prev::after,
                    [class*="-swiper"] .swiper-button-next::after {
                        font-size: 0.875rem;
                    }

                    [class*="-swiper"] .swiper-pagination-bullet {
                        width: 0.375rem;
                        height: 0.375rem;
                    }

                    [class*="-swiper"] .swiper-pagination-fraction {
                        color: #fff;
                        background: rgba(0, 0, 0, 0.45);
                        border-radius: 9999px;
                        padding: 0.15rem 0.55rem;
                        font-size: 0.7rem;
                        font-weight: 600;
                        width: auto;
                        left: auto;
                        right: 0.75rem;
                        bottom: 0.75rem;
                    }
                </style>
            @endpush
            @push('scripts')
                <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
            @endpush
        @endonce

        @push('scripts')
            <script>
                onPageReady(function () {
                    window.tamanGalleryInstances = window.tamanGalleryInstances || {};

                    const galleryId = @json($galleryId);
                    const slideCount = @json($count);
                    const swiperSelector = '.' + galleryId + '-swiper';

                    if (window.tamanGalleryInstances[galleryId]) {
                        window.tamanGalleryInstances[galleryId].destroy(true, true);
                    }

                    window.tamanGalleryInstances[galleryId] = new Swiper(swiperSelector, {
                        loop: slideCount > 2,
                        rewind: slideCount === 2,
                        slidesPerView: 1,
                        spaceBetween: 0,
                        autoHeight: false,
                        autoplay: slideCount > 1 ? { delay: 4500, disableOnInteraction: false } : false,
                        pagination: {
                            el: swiperSelector + ' .swiper-pagination',
                            clickable: true,
                            type: slideCount > 1 ? 'fraction' : 'bullets',
                        },
                        navigation: {
                            nextEl: swiperSelector + ' .swiper-button-next',
                            prevEl: swiperSelector + ' .swiper-button-prev',
                        },
                        keyboard: { enabled: true },
                        a11y: { enabled: true },
                    });
                });
            </script>
        @endpush
        @endif
    </div>
</div>
