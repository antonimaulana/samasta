<section class="relative" id="info-terkini-slider" aria-labelledby="info-terkini-heading">
    <div class="relative">
        <x-home-section-heading
            tag="Berita"
            title="Informasi Terkini"
            lead="Informasi seputar pertamanan Kota Batam."
            heading-id="info-terkini-heading"
            class="sm:px-16 lg:px-20"
        />
        <div class="absolute right-0 top-8 hidden items-center gap-2 sm:flex lg:top-10">
            <button type="button" id="info-slider-prev"
                    class="flex h-9 w-9 items-center justify-center rounded-full border border-green-200 bg-white text-green-600 shadow-sm transition hover:bg-lime-50"
                    aria-label="Slide sebelumnya">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <button type="button" id="info-slider-next"
                    class="flex h-9 w-9 items-center justify-center rounded-full border border-green-200 bg-white text-green-600 shadow-sm transition hover:bg-lime-50"
                    aria-label="Slide berikutnya">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>
    </div>

    <div class="info-terkini-panel overflow-hidden rounded-3xl border border-green-100 p-3 shadow-xl shadow-green-100/50 ring-1 ring-green-50 sm:p-4">
        <div class="info-slide-viewport relative overflow-hidden rounded-2xl">
            <div id="info-slider-track" class="flex items-stretch transition-transform duration-500 ease-out">
                @forelse ($berandaSlides ?? [] as $slideProps)
                    @php $slide = $slideProps['slide']; @endphp
                    @if ($slide->jenis === \App\Models\BerandaSlide::JENIS_SIMPONI)
                        @include('home.partials.info-slide-simponi', collect($slideProps)->except('slide')->all())
                    @elseif ($slide->jenis === \App\Models\BerandaSlide::JENIS_PERDA)
                        @include('home.partials.info-slide-perda-ketertiban', collect($slideProps)->except('slide')->all())
                    @else
                        @include('home.partials.info-slide-card', collect($slideProps)->except('slide')->all())
                    @endif
                @empty
                    <p class="w-full p-8 text-center text-sm text-gray-500">Belum ada slide beranda aktif.</p>
                @endforelse
            </div>
        </div>
    </div>

    @php $slideCount = isset($berandaSlides) ? $berandaSlides->count() : 0; @endphp
    @if ($slideCount > 0)
        <div class="mt-6 flex items-center justify-center gap-4 sm:mt-8">
            <button type="button" id="info-slider-prev-mobile"
                    class="flex h-8 w-8 items-center justify-center rounded-full border border-green-200 bg-white text-green-600 sm:hidden"
                    aria-label="Slide sebelumnya">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <div id="info-slider-dots" class="flex gap-2">
                @foreach ($berandaSlides as $index => $slideProps)
                    <button type="button"
                            @class([
                                'info-slider-dot h-2.5 rounded-full transition-all',
                                'w-8 bg-green-500' => $index === 0,
                                'w-2.5 bg-green-200' => $index !== 0,
                            ])
                            data-index="{{ $index }}"
                            aria-label="Slide {{ $slideProps['slide']->title_attr }}"></button>
                @endforeach
            </div>
            <button type="button" id="info-slider-next-mobile"
                    class="flex h-8 w-8 items-center justify-center rounded-full border border-green-200 bg-white text-green-600 sm:hidden"
                    aria-label="Slide berikutnya">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>
    @endif
</section>

@include('home.partials.info-terkini-slider-script')
