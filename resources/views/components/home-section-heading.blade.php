@props([
    'tag' => null,
    'title',
    'lead' => null,
    'headingId' => null,
    'tagUppercase' => true,
])

<div {{ $attributes->class(['home-section-heading']) }}>
    @if ($tag)
        <span @class([
            'home-section-heading__tag',
            'home-section-heading__tag--mixed' => ! $tagUppercase,
        ])>{{ $tag }}</span>
    @endif
    <h2 @if ($headingId) id="{{ $headingId }}" @endif class="home-section-heading__title">{{ $title }}</h2>
    @if ($lead)
        <p class="home-section-heading__lead">{{ $lead }}</p>
    @endif
</div>
