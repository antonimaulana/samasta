@props([
    'href' => null,
    'variant' => 'view',
    'tag' => 'a',
])

@php
    $classes = match ($variant) {
        'edit' => 'rounded border border-blue-300 px-2.5 py-1 text-sm font-medium text-blue-700 hover:bg-blue-50',
        'delete' => 'rounded border border-red-300 px-2.5 py-1 text-sm font-medium text-red-700 hover:bg-red-50',
        default => 'rounded border border-gray-300 px-2.5 py-1 text-sm font-medium text-gray-700 hover:bg-gray-50',
    };
@endphp

@if ($tag === 'button')
    <button type="submit" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@else
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@endif
