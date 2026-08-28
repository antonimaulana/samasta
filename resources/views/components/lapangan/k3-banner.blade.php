@php
    $tip = \App\Support\LapanganUi::k3TipOfDay();
@endphp

<div class="lapangan-k3-banner mb-4 rounded-2xl p-4 sm:p-5" role="note" aria-label="Himbauan K3">
    <div class="flex items-start gap-3">
        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-orange-100 text-2xl shadow-inner" aria-hidden="true">
            {{ $tip['icon'] }}
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-[11px] font-bold uppercase tracking-wider text-orange-800">Himbauan K3 — Keselamatan Kerja</p>
            <p class="mt-1 text-sm font-bold text-orange-950">{{ $tip['title'] }}</p>
            <p class="mt-1 text-sm leading-relaxed text-orange-900/90">{{ $tip['body'] }}</p>
        </div>
    </div>
</div>
