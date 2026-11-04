@props([
    'statuses' => [],
    'title' => 'Progres per tim',
    'panelId' => null,
    'interactive' => false,
])

@if ($statuses !== [])
    <div @if ($panelId) id="{{ $panelId }}" @endif {{ $attributes->class(['rounded-xl border border-gray-200 bg-white px-3 py-2.5']) }}>
        <p class="text-[10px] font-bold uppercase tracking-wide text-gray-500">{{ $title }}</p>
        <ul class="mt-2 flex flex-wrap gap-2">
            @foreach ($statuses as $tim => $status)
                @php
                    $done = $status === \App\Support\PermohonanPelaksanaProgress::STATUS_DIKERJAKAN;
                @endphp
                <li @if ($interactive) data-tim-status="{{ $tim }}" @endif
                    class="inline-flex max-w-full items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-bold {{ $done ? 'border-emerald-200 bg-emerald-50 text-emerald-900' : 'border-gray-200 bg-gray-50 text-gray-600' }}">
                    <span class="truncate">{{ $tim }}</span>
                    <span @if ($interactive) data-tim-status-label @endif
                          class="shrink-0 rounded-full px-1.5 py-0.5 text-[10px] {{ $done ? 'bg-emerald-200 text-emerald-900' : 'bg-gray-200 text-gray-700' }}">
                        {{ $done ? 'Dikerjakan' : 'Menunggu' }}
                    </span>
                </li>
            @endforeach
        </ul>
    </div>
@endif
