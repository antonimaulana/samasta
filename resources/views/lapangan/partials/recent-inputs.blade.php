@props([
    'records',
    'guestMode' => false,
    'scopedTim' => null,
])

@if ($records->isNotEmpty())
    <details class="lapangan-card mb-5 overflow-hidden" open>
        <summary class="cursor-pointer list-none px-4 py-3.5 sm:px-5">
            <div class="flex items-center justify-between gap-3">
                <p class="lapangan-section-title text-sm font-black text-gray-900">
                    Riwayat input Anda
                </p>
                <span class="shrink-0 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-bold text-green-800">
                    {{ $records->count() }} terakhir
                </span>
            </div>
            <p class="mt-1 text-xs text-gray-500">
                @if ($guestMode)
                    Riwayat sesi PIN ini (maks. 5).
                @else
                    {{ $scopedTim ? 'Tim '.$scopedTim.' · ' : '' }}Maks. 5 catatan dari akun Anda.
                @endif
            </p>
        </summary>
        <ul class="divide-y divide-gray-100 border-t border-gray-100">
            @foreach ($records as $row)
                <li class="px-4 py-3.5 sm:px-5">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-bold text-gray-900">
                                {{ $row->lokasi_pelaksanaan ?: ($row->taman?->nama_taman ?? '—') }}
                            </p>
                            <p class="mt-0.5 text-xs text-gray-600">
                                {{ \App\Support\OperasionalPelaksanaanTime::display($row->tanggal) }}
                                · {{ $row->tim }}
                            </p>
                            @if ($row->uraian_pekerjaan)
                                <p class="mt-1 line-clamp-2 text-xs text-gray-500">{{ $row->uraian_pekerjaan }}</p>
                            @endif
                        </div>
                        @if ($row->persentase_progres !== null)
                            <span class="shrink-0 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-bold text-emerald-800">
                                {{ $row->persentase_progres }}%
                            </span>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </details>
@endif
