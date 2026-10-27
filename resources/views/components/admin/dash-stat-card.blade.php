@props([
    'href',
    'tone' => 'emerald',
    'label',
    'value',
    'subtitle' => null,
    'footer' => null,
    'animate' => false,
])

@php
    $animateClass = match (true) {
        $animate === false, $animate === null, $animate === '' => '',
        (string) $animate === '1' => 'dash-animate-in dash-animate-in-delay-1',
        (string) $animate === '2' => 'dash-animate-in dash-animate-in-delay-2',
        (string) $animate === '3' => 'dash-animate-in dash-animate-in-delay-3',
        default => 'dash-animate-in',
    };
@endphp

<a href="{{ $href }}"
   {{ $attributes->merge(['class' => trim("group dash-card dash-card--{$tone} p-5 {$animateClass}")]) }}>
    <span class="dash-card__glow dash-card__glow--{{ $tone }}"></span>
    <div class="relative flex items-start justify-between gap-3">
        <div class="min-w-0 flex-1">
            <p class="dash-kicker dash-kicker--{{ $tone }}">{{ $label }}</p>
            <p class="dash-stat-value mt-2">{{ $value }}</p>
            @if ($subtitle)
                <p class="text-sm font-medium text-gray-600">{{ $subtitle }}</p>
            @endif
            @isset($meta)
                <div class="mt-2">{{ $meta }}</div>
            @endisset
        </div>
        @isset($icon)
            <span class="dash-icon-box dash-icon-box--{{ $tone }}">
                {{ $icon }}
            </span>
        @endisset
    </div>
    @isset($extra)
        <div class="relative">{{ $extra }}</div>
    @endisset
    @if ($footer)
        <p class="relative mt-4 text-xs font-semibold dash-link--{{ $tone }} group-hover:underline">{{ $footer }}</p>
    @endif
</a>
