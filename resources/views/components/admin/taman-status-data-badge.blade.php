@props([
    'status' => '',
    'size' => 'sm',
])

@php
    $isComplete = $status === \App\Models\Taman::STATUS_DATA_LENGKAP;
    $classes = $isComplete
        ? 'bg-green-100 text-green-800 ring-green-200'
        : 'bg-amber-100 text-amber-800 ring-amber-200';
    $text = $isComplete ? 'Lengkap' : 'Belum Lengkap';
    $dotClass = $isComplete ? 'bg-green-500' : 'bg-amber-500';
    $padding = match ($size) {
        'lg' => 'px-3 py-1.5 text-sm',
        'table' => 'px-2 py-0.5 text-xs whitespace-nowrap',
        default => 'px-2 py-0.5 text-xs whitespace-nowrap',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 rounded-full font-semibold ring-1 {$classes} {$padding}"]) }}>
    <span class="inline-block h-2 w-2 rounded-full {{ $dotClass }}"></span>
    {{ $text }}
</span>
