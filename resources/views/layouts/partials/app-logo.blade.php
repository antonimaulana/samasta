@php
    $size = $size ?? 'md';
    $alt = $alt ?? 'Logo Pemerintah Kota Batam';
    $class = $class ?? '';

    $heightClass = match ($size) {
        'sm' => 'h-8',
        'lg' => 'h-12',
        default => 'h-10',
    };
@endphp

<img src="{{ asset('images/logo-pemkot-batam.png') }}"
     alt="{{ $alt }}"
     width="1024"
     height="724"
     class="{{ $heightClass }} aspect-[1024/724] w-auto max-w-none flex-shrink-0 object-contain {{ $class }}">
