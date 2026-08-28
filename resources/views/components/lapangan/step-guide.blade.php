@props(['steps' => [], 'title' => 'Langkah mudah'])

<div class="mb-5 rounded-2xl border border-green-100 bg-white/90 p-4">
    <p class="lapangan-section-title text-sm font-bold text-gray-900">{{ $title }}</p>
    <ol class="mt-3 space-y-2">
        @foreach ($steps as $item)
            <li class="flex items-start gap-3 rounded-xl bg-gray-50 px-3 py-2.5">
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full lapangan-step-active text-xs font-black">
                    {{ $item['step'] }}
                </span>
                <div class="min-w-0 pt-0.5">
                    <p class="text-sm font-semibold text-gray-900">{{ $item['label'] }}</p>
                    <p class="text-xs text-gray-500">{{ $item['hint'] }}</p>
                </div>
            </li>
        @endforeach
    </ol>
</div>
