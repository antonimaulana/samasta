@props([
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm']) }}>
    <div class="border-b border-gray-100 bg-emerald-50/90 px-5 py-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h3 class="text-sm font-semibold text-emerald-900">{{ $title }}</h3>
                @if ($description)
                    <p class="mt-0.5 text-xs leading-relaxed text-emerald-800/75">{{ $description }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    {{ $actions }}
                </div>
            @endisset
        </div>
    </div>
    <div class="p-5 sm:p-6">
        {{ $slot }}
    </div>
</div>
