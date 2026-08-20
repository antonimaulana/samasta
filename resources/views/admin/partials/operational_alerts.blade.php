@php
    $alerts = $operationalAlerts ?? [];
    $total = $operationalAlertsTotal ?? count($alerts);
    $severityStyles = [
        'danger' => ['border' => 'border-red-200', 'accent' => 'bg-red-500', 'text' => 'text-red-800', 'bg' => 'bg-red-50'],
        'warning' => ['border' => 'border-amber-200', 'accent' => 'bg-amber-500', 'text' => 'text-amber-800', 'bg' => 'bg-amber-50'],
        'info' => ['border' => 'border-gray-200', 'accent' => 'bg-gray-400', 'text' => 'text-gray-800', 'bg' => 'bg-gray-50'],
    ];
@endphp

<section id="peringatan-operasional" class="mb-8 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-4">
        <div>
            <h3 class="font-bold text-gray-900">Catatan Operasional</h3>
            <p class="text-xs text-gray-500">Indikator yang memerlukan monitoring lanjutan</p>
        </div>
        @if ($total > 0)
            <span class="rounded-full bg-red-600 px-3 py-1 text-xs font-bold text-white">{{ $total }} poin</span>
        @else
            <span class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-800">
                <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                Semua indikator normal
            </span>
        @endif
    </div>

    @if ($total === 0)
        <p class="px-5 py-4 text-sm text-gray-500">Tidak ada catatan operasional yang perlu ditindaklanjuti saat ini.</p>
    @else
        <div class="divide-y divide-gray-100">
            @foreach ($alerts as $alert)
                @php $style = $severityStyles[$alert['severity']] ?? $severityStyles['warning']; @endphp
                <a href="{{ $alert['url'] }}" class="flex items-center gap-4 px-5 py-4 transition hover:bg-gray-50">
                    <span class="h-10 w-1 shrink-0 rounded-full {{ $style['accent'] }}"></span>
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-gray-900">{{ $alert['label'] }}</p>
                        <p class="text-xs text-gray-500">{{ $alert['description'] }}</p>
                    </div>
                    <span class="inline-flex min-w-[2.5rem] items-center justify-center rounded-full px-3 py-1 text-sm font-black {{ $style['bg'] }} {{ $style['text'] }}">
                        {{ $alert['count'] }}
                    </span>
                </a>
            @endforeach
        </div>
    @endif
</section>
