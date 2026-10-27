@php
    $fullName = config('app.full_name');
    $subtitleLine2 = 'Manajemen Pertamanan';

    if (str_contains($fullName, $subtitleLine2)) {
        $subtitleLine1 = trim(str_replace($subtitleLine2, '', $fullName));
    } else {
        $subtitleLine1 = $fullName;
        $subtitleLine2 = null;
    }
@endphp

<div class="flex items-center gap-2.5">
    @include('layouts.partials.app-logo', ['size' => 'sm'])
    <div class="min-w-0 flex-1 leading-snug">
        <p class="text-base font-semibold text-white">{{ config('app.name') }}</p>
        <p class="mt-0.5 text-[11px] leading-snug text-gray-400">{{ $subtitleLine1 }}</p>
        @if ($subtitleLine2)
            <p class="text-[11px] leading-snug text-gray-400">{{ $subtitleLine2 }}</p>
        @endif
    </div>
</div>
