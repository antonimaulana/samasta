@props([
    'kategori',
])

@php
    $label = filled($kategori) ? (string) $kategori : '—';
@endphp

<span {{ $attributes->class([
    'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold',
    \App\Models\Pemangkasan::kategoriBadgeClass($label !== '—' ? $label : null),
]) }}>
    {{ $label }}
</span>
