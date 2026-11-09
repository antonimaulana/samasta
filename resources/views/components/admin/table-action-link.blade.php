@props([
    'href' => null,
    'variant' => 'view',
    'tag' => 'a',
    'label' => null,
    'size' => 'md',
])

@php
    $labelText = trim($label ?? (string) $slot) ?: match ($variant) {
        'edit' => 'Edit',
        'delete' => 'Hapus',
        'pdf' => 'PDF',
        'history' => 'Riwayat',
        'download' => 'Unduh',
        default => 'Lihat',
    };

    $iconName = match ($variant) {
        'edit' => 'edit',
        'delete' => 'delete',
        'pdf' => 'pdf',
        'history' => 'history',
        'download' => 'download',
        default => 'view',
    };

    $sizeClass = $size === 'sm' ? 'admin-table-action--sm' : '';

    $classes = 'admin-table-action admin-table-action--'.$variant.' '.$sizeClass;
@endphp

@if ($tag === 'button')
    <button type="submit" title="{{ $labelText }}" aria-label="{{ $labelText }}" {{ $attributes->merge(['class' => $classes]) }}>
        <x-admin.table-icon :name="$iconName" />
    </button>
@else
    <a href="{{ $href }}" title="{{ $labelText }}" aria-label="{{ $labelText }}"
       @if ($variant === 'pdf') target="_blank" rel="noopener noreferrer" @endif
       {{ $attributes->merge(['class' => $classes]) }}>
        <x-admin.table-icon :name="$iconName" />
    </a>
@endif
