@props([
    'records',
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
        </summary>
        <ul class="divide-y divide-gray-100 border-t border-gray-100">
            @foreach ($records as $row)
                <li class="px-4 py-3.5 sm:px-5">
                    <dl class="space-y-1.5 text-sm">
                        <div>
                            <dt class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Lokasi</dt>
                            <dd class="font-semibold text-gray-900">{{ $row->lokasi_pelaksanaan ?: ($row->taman?->nama_taman ?? '—') }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Kategori RTH</dt>
                            <dd class="text-gray-800">{{ $row->lokasiKategoriLabel() }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Tanggal &amp; waktu</dt>
                            <dd class="text-gray-800">{{ \App\Support\OperasionalPelaksanaanTime::display($row->tanggal) }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Uraian pekerjaan</dt>
                            <dd class="text-gray-700">{{ $row->uraian_pekerjaan ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Progres</dt>
                            <dd class="font-semibold text-emerald-800">{{ $row->lapanganRiwayatProgresLabel() ?? '—' }}</dd>
                        </div>
                    </dl>
                </li>
            @endforeach
        </ul>
    </details>
@endif
