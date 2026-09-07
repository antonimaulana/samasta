<div class="flex items-start justify-between gap-3">
    <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xl" aria-hidden="true">{{ \App\Models\Pemangkasan::layananIcon($permohonan->jenis_layanan) }}</span>
            <span class="rounded-full px-2.5 py-0.5 text-[11px] font-bold {{ \App\Models\Pemangkasan::badgeClass($permohonan->jenis_layanan) }}">
                {{ $permohonan->jenis_layanan }}
            </span>
            <span class="rounded-full px-2.5 py-0.5 text-[11px] font-bold {{ $statusClass }}">
                {{ $permohonan->status }}
            </span>
        </div>
        <p class="mt-2 text-base font-black text-gray-900">{{ $permohonan->lokasi_pohon }}</p>
        <p class="mt-1 text-sm text-gray-600">{{ $permohonan->asal }} · {{ $permohonan->penanggungjawab }}</p>

        @php
            $totalHari = (int) ($permohonan->total_hari ?? 1);
            $hariTercapai = (int) ($permohonan->hari_tercapai ?? 0);
            $persen = (int) ($permohonan->persentase_progres ?? 0);
        @endphp

        <div class="mt-3 rounded-xl bg-gray-50 px-3 py-2.5">
            <div class="flex items-center justify-between text-xs font-semibold text-gray-600">
                <span>📅 {{ \App\Support\PemangkasanSchedule::labelRentang($permohonan) }}</span>
                <span class="text-green-700">{{ $hariTercapai }}/{{ $totalHari }} hari · {{ $persen }}%</span>
            </div>
            <div class="mt-2 h-2 overflow-hidden rounded-full bg-gray-200">
                <div class="lapangan-progress-bar h-full rounded-full transition-all" style="width: {{ max(4, $persen) }}%"></div>
            </div>
        </div>

        @if ($isLate)
            <p class="mt-2 flex items-center gap-1 text-xs font-bold text-red-600">
                <span aria-hidden="true">⏰</span> Terlambat dari jadwal — segera tindak lanjuti
            </p>
        @elseif ($permohonan->status !== 'Selesai' && \App\Support\PemangkasanSchedule::isActiveToday($permohonan))
            <p class="mt-2 flex items-center gap-1 text-xs font-bold text-emerald-700">
                <span aria-hidden="true">📍</span> Jadwal aktif hari ini
            </p>
        @endif
    </div>
    <span @class([
        'shrink-0 rounded-xl px-3 py-2 text-xs font-black',
        'bg-gray-100 text-gray-500' => $actionMuted,
        'bg-green-600 text-white shadow-md' => ! $actionMuted,
    ])>{{ $actionLabel }}</span>
</div>
