@props([
    'title',
    'description' => null,
    'titleVariant' => 'section',
    'titleTone' => 'emerald',
])

@php
    $titleClass = $titleVariant === 'kicker'
        ? "dash-kicker dash-kicker--{$titleTone}"
        : 'dash-section__title';
    $descClass = $titleVariant === 'kicker'
        ? 'dash-block-heading__subtitle mt-1'
        : 'dash-section__desc';
@endphp

<section {{ $attributes->merge(['class' => 'dash-section mb-8']) }}>
    <div class="dash-section__header">
        <div class="min-w-0 flex-1">
            @isset($icon)
                <div @class([
                    'flex gap-3',
                    'items-center' => ! $description,
                    'items-start' => (bool) $description,
                ])>
                    <span class="dash-section__icon">{{ $icon }}</span>
                    <div class="min-w-0">
                        @if ($titleVariant === 'kicker')
                            <p class="{{ $titleClass }}">{{ $title }}</p>
                        @else
                            <h3 class="{{ $titleClass }}">{{ $title }}</h3>
                        @endif
                        @if ($description)
                            <p class="{{ $descClass }}">{{ $description }}</p>
                        @endif
                    </div>
                </div>
            @else
                @if ($titleVariant === 'kicker')
                    <p class="{{ $titleClass }}">{{ $title }}</p>
                @else
                    <h3 class="{{ $titleClass }}">{{ $title }}</h3>
                @endif
                @if ($description)
                    <p class="{{ $descClass }}">{{ $description }}</p>
                @endif
            @endisset
        </div>
        @isset($badge)
            <div class="shrink-0">{{ $badge }}</div>
        @endisset
    </div>
    <div class="dash-section__body">
        {{ $slot }}
    </div>
</section>
