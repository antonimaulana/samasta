@props([
    'column',
    'label',
    'align' => 'left',
    'sort' => '',
    'direction' => 'asc',
])

@php
    $isActive = $sort === $column;
    $nextDirection = $isActive && $direction === 'asc' ? 'desc' : 'asc';
    $url = request()->fullUrlWithQuery([
        'sort' => $column,
        'direction' => $nextDirection,
        'page' => null,
    ]);
    $alignClass = match ($align) {
        'right' => 'justify-end text-right',
        'center' => 'justify-center text-center',
        default => 'justify-start text-left',
    };
@endphp

<th {{ $attributes->merge(['class' => "px-4 py-3 {$alignClass}"]) }}>
    <a href="{{ $url }}"
       class="inline-flex items-center gap-1 text-sm font-medium {{ $isActive ? 'text-green-700' : 'text-gray-700 hover:text-green-700' }}">
        <span>{{ $label }}</span>
        @if ($isActive)
            <span aria-hidden="true" class="text-xs">{{ $direction === 'asc' ? '▲' : '▼' }}</span>
        @else
            <span aria-hidden="true" class="text-xs text-gray-400">↕</span>
        @endif
    </a>
</th>
