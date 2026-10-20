@php
    $toneRing = match ($aspect['tone']) {
        'good' => 'text-green-600',
        'warn' => 'text-amber-600',
        'risk' => 'text-red-600',
        default => 'text-gray-500',
    };
    $toneBg = match ($aspect['tone']) {
        'good' => 'from-green-50 to-emerald-50 border-green-100',
        'warn' => 'from-amber-50 to-orange-50 border-amber-100',
        'risk' => 'from-red-50 to-rose-50 border-red-100',
        default => 'from-gray-50 to-slate-50 border-gray-200',
    };
@endphp

<article id="{{ $aspect['id'] }}" class="scroll-mt-24 overflow-hidden rounded-2xl border bg-white shadow-sm">
    <div class="border-b bg-gradient-to-r px-5 py-4 {{ $toneBg }}">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex min-w-0 items-start gap-3">
                <span class="text-2xl" aria-hidden="true">{{ $aspect['icon'] }}</span>
                <div>
                    <h3 class="text-base font-bold text-gray-900">{{ $aspect['title'] }}</h3>
                    <p class="mt-1 text-sm leading-relaxed text-gray-700">{{ $aspect['insight'] }}</p>
                    @if ($aspect['footnote'] ?? null)
                        <p class="mt-2 text-xs text-gray-500">{{ $aspect['footnote'] }}</p>
                    @endif
                </div>
            </div>
            <div class="flex shrink-0 flex-col items-center rounded-xl bg-white/80 px-4 py-2 shadow-sm">
                <span class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Skor</span>
                <span class="text-2xl font-black {{ $toneRing }}">{{ $aspect['scoreLabel'] }}</span>
            </div>
        </div>
    </div>

    <div class="grid gap-px bg-gray-100 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($aspect['metrics'] as $metric)
            <div class="bg-white px-4 py-3">
                <p class="text-xs font-medium text-gray-500">{{ $metric['label'] }}</p>
                <p class="mt-0.5 text-lg font-bold text-gray-900">{{ $metric['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="flex flex-wrap gap-2 border-t border-gray-100 bg-gray-50/80 px-4 py-3">
        <a href="{{ $aspect['detailUrl'] }}"
           class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">
            Lihat detail &amp; grafik
        </a>
        <a href="{{ $aspect['exportUrl'] }}"
           class="rounded-lg border border-green-300 bg-white px-3 py-1.5 text-xs font-semibold text-green-800 hover:bg-green-50">
            Export PDF
        </a>
    </div>
</article>
