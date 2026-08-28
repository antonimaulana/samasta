@props([
    'backUrl',
    'backLabel' => '← Kembali',
])

<div {{ $attributes->merge(['class' => 'mb-6 flex flex-wrap items-center justify-between gap-3']) }}>
    <a href="{{ $backUrl }}" class="text-sm font-medium text-green-700 hover:text-green-800 hover:underline">
        {{ $backLabel }}
    </a>
    @isset($actions)
        <div class="flex flex-wrap gap-2">
            {{ $actions }}
        </div>
    @endisset
</div>
