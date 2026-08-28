@props([
    'title',
    'description' => null,
    'open' => false,
])

<details {{ $attributes->merge(['class' => 'group mb-6 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm']) }} @if ($open) open @endif>
    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-5 py-4 marker:content-none hover:bg-gray-50">
        <div class="min-w-0">
            <h3 class="font-semibold text-gray-900">{{ $title }}</h3>
            @if ($description)
                <p class="mt-1 text-sm text-gray-600">{{ $description }}</p>
            @endif
        </div>
        <div class="flex shrink-0 items-center gap-2">
            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600 group-open:hidden">Buka</span>
            <span class="hidden rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600 group-open:inline">Tutup</span>
            <svg class="h-5 w-5 text-gray-500 transition group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>
    </summary>
    <div class="border-t border-gray-100">
        {{ $slot }}
    </div>
</details>
