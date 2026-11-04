@php
    $iconClass = $class ?? 'h-5 w-5';
@endphp
@switch($kategori)
    @case('Taman Kota')
        {{-- Pohon + bangku --}}
        <svg class="{{ $iconClass }} shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 20v-5"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 15c-2.5 0-3.5-2.5-2-4.5S6.5 7 6 9s-1 4.5 0 6"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 18h6"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 18v2M19 18v2"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h8"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 16V14h6v2"/>
        </svg>
        @break
    @case('Taman Lingkungan')
        {{-- Playground / ayunan --}}
        <svg class="{{ $iconClass }} shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 7v12M19 7v12"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 7h14"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7v3"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10l-3 5h6l-3-5z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 19h8"/>
        </svg>
        @break
    @case('Jalur Hijau Jalan')
        {{-- Pohon besar & kecil --}}
        <svg class="{{ $iconClass }} shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 20h16"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20V13"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 13c0-2.5 1-4.5 0-6.5S5 4 7 6s0 5.5 0 7"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 20v-6"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 14c0-1.5.5-2.5 0-3.5S14.5 9 16 10s0 3 0 4"/>
        </svg>
        @break
    @case('Kebun Raya')
        {{-- Daun --}}
        <svg class="{{ $iconClass }} shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 21c-4-3.5-7-7.5-7-12a7 7 0 0114 0c0 4.5-3 8.5-7 12z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21V9"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12c1.5-1 3-1.5 6 0"/>
        </svg>
        @break
    @case('TPU')
        {{-- Burung --}}
        <svg class="{{ $iconClass }} shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 14c2-4 6-6 10-5 2 .5 3.5 2 4 4-3 1-6 3-8 6-1.5 2-3 3-5 3-2 0-3.5-1.5-4-4 1-2 2-3.5 3-4z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 13l2-1"/>
            <circle cx="15.5" cy="11" r="0.75" fill="currentColor" stroke="none"/>
        </svg>
        @break
    @default
        <svg class="{{ $iconClass }} shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 21c-4-3.5-7-7.5-7-12a7 7 0 0114 0c0 4.5-3 8.5-7 12z"/>
        </svg>
@endswitch
