@props([
    'records',
])

@if ($records->isNotEmpty())
    <details class="lapangan-card mb-5 overflow-hidden">
        <summary class="cursor-pointer list-none px-4 py-3.5 sm:px-5">
            <div class="flex items-center justify-between gap-3">
                <p class="lapangan-section-title text-sm font-black text-gray-900">
                    Riwayat input Anda
                </p>
                <span class="shrink-0 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-bold text-green-800">
                    {{ $records->count() }} terakhir
                </span>
            </div>
        </summary>
        <ul class="divide-y divide-gray-100 border-t border-gray-100">
            @foreach ($records as $row)
                @php
                    $lokasi = $row->lokasi_pelaksanaan ?: ($row->taman?->nama_taman ?? '—');
                    $metaParts = array_filter([
                        $row->lokasiKategoriLabel(),
                        \App\Support\OperasionalPelaksanaanTime::display($row->tanggal),
                        $row->uraian_pekerjaan ?: null,
                        $row->lapanganRiwayatProgresLabel(),
                    ]);
                @endphp
                <li class="px-4 py-2.5 sm:px-5">
                    <p class="text-sm font-semibold text-gray-900">{{ $lokasi }}</p>
                    @if ($metaParts !== [])
                        <p class="mt-1 text-xs leading-relaxed text-gray-600">
                            {{ implode(' · ', $metaParts) }}
                        </p>
                    @endif
                </li>
            @endforeach
        </ul>
    </details>
@endif
