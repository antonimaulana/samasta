@php
    $photoUrl = filled($pejabat['image'] ?? null) && file_exists(public_path($pejabat['image']))
        ? asset($pejabat['image'])
        : null;

    $initials = collect(preg_split('/\s+/', $pejabat['name']))
        ->filter(fn ($part) => ! preg_match('/^(H\.|Drs\.|Dra\.|Hj\.|Ir\.|Dr\.)/u', $part))
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
@endphp

<article @class([
    'group flex h-full flex-col overflow-hidden rounded-3xl bg-white shadow-lg shadow-gray-200/60 ring-1 ring-gray-100 transition hover:-translate-y-1 hover:shadow-xl',
    $cardClass ?? '',
])>
    <div class="px-4 pt-4 sm:px-5 sm:pt-5">
        <div @class([
            'relative mx-auto flex h-48 w-full overflow-hidden rounded-tl-[2.75rem] rounded-tr-2xl rounded-br-2xl rounded-bl-2xl ring-1 sm:h-44',
            $pejabat['accent_bg'],
            $pejabat['accent_ring'],
            $pejabat['photo_frame_class'] ?? 'items-end justify-center',
        ])>
            @if ($photoUrl)
                <img src="{{ $photoUrl }}"
                     alt="Foto {{ $pejabat['name'] }}"
                     loading="lazy"
                     @class([
                         'transition duration-500 group-hover:scale-[1.02]',
                         $pejabat['photo_class'] ?? 'max-h-full max-w-[94%] object-contain object-bottom',
                     ])>
            @else
                <div class="flex h-full w-full items-center justify-center pb-4">
                    <span class="flex h-16 w-16 items-center justify-center rounded-full bg-white/80 text-xl font-black text-gray-700 shadow-inner sm:h-20 sm:w-20 sm:text-2xl">
                        {{ $initials }}
                    </span>
                </div>
            @endif
        </div>
    </div>

    <div class="flex flex-1 flex-col justify-center px-4 pb-5 pt-3 text-center sm:px-5 sm:pb-6 sm:pt-4">
        <h3 class="text-base font-bold leading-snug text-gray-900 sm:text-base">{{ $pejabat['name'] }}</h3>
        <p class="mt-1.5 text-xs leading-snug text-gray-500 sm:text-sm">{{ $pejabat['jabatan'] }}</p>
    </div>
</article>
