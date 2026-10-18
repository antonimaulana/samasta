@props([
    'kategori',
    'size' => 'sm',
])

@php
    $label = trim((string) $kategori);
    $sizeClass = match ($size) {
        'md' => 'px-3 py-1 text-sm',
        'lg' => 'px-3.5 py-1.5 text-sm',
        default => 'px-2.5 py-0.5 text-xs',
    };
@endphp

@if ($label !== '')
    <span {{ $attributes->class(['inline-flex max-w-full items-center rounded-full border font-bold', $sizeClass]) }}
          style="{{ \App\Models\Taman::kategoriBadgeInlineStyle($label) }}"
          title="{{ $label }}">
        <span class="min-w-0 truncate">{{ $label }}</span>
    </span>
@endif
