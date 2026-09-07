@extends('layouts.admin')

@section('title', 'Detail Operasional')
@section('header', 'Detail Operasional Pertamanan')

@section('content')
    @php
        $isMiniGarden = $pemangkasan->jenis_layanan === 'Pemasangan Mini Garden';
        $isTumbang = $pemangkasan->jenis_layanan === 'Penanganan Pohon Tumbang';
        $statusClass = match ($pemangkasan->status) {
            'Selesai' => 'bg-emerald-100 text-emerald-800',
            'Diproses' => 'bg-amber-100 text-amber-800',
            default => 'bg-blue-100 text-blue-800',
        };
        $isLate = \App\Support\PemangkasanSchedule::isLate($pemangkasan);
    @endphp

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ $backUrl }}" class="text-sm text-green-700 hover:underline">← {{ $backLabel }}</a>
        <x-admin.can-write>
            <a href="{{ route('admin.pemangkasans.edit', $pemangkasan) }}"
               class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                Edit Data
            </a>
        </x-admin.can-write>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ \App\Models\Pemangkasan::badgeClass($pemangkasan->jenis_layanan) }}">
                            {{ $pemangkasan->jenis_layanan }}
                        </span>
                        <h2 class="mt-3 text-xl font-bold text-gray-900">{{ $pemangkasan->lokasi_pohon }}</h2>
                        @if ($isLate)
                            <p class="mt-1 text-sm font-medium text-red-600">
                                Terlambat — jadwal {{ \App\Support\PemangkasanSchedule::labelRentang($pemangkasan) }}
                            </p>
                        @endif
                    </div>
                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ $statusClass }}">
                        {{ $pemangkasan->status }}
                    </span>
                </div>

                <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-500">
                            {{ $isTumbang ? 'Asal Laporan' : 'Asal Permohonan' }}
                        </dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $pemangkasan->asal }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-500">Penanggung Jawab</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $pemangkasan->penanggungjawab ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-500">
                            {{ $isTumbang ? 'Kontak Laporan' : 'Kontak Permohonan' }}
                        </dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $pemangkasan->kontak_permohonan ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-500">Kategori</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $pemangkasan->kategori }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-500">
                            {{ $isTumbang ? 'Tanggal Laporan' : 'Tanggal Permohonan' }}
                        </dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $pemangkasan->tanggal_permohonan->translatedFormat('d F Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-500">Jadwal Pelaksanaan</dt>
                        <dd class="mt-1 font-medium text-gray-900">
                            {{ \App\Support\PemangkasanSchedule::labelRentang($pemangkasan) }}
                            <span class="block text-sm font-normal text-gray-600">
                                {{ (int) ($pemangkasan->total_hari ?? 1) }} hari rencana · {{ \App\Support\PemangkasanSchedule::progressSummary($pemangkasan) }}
                            </span>
                        </dd>
                    </div>
                    @if ($pemangkasan->status === 'Selesai' && $pemangkasan->tanggal_penyelesaian)
                        <div>
                            <dt class="text-xs font-semibold uppercase text-gray-500">Tanggal Penyelesaian</dt>
                            <dd class="mt-1 font-medium text-emerald-800">{{ $pemangkasan->tanggal_penyelesaian->translatedFormat('d F Y') }}</dd>
                        </div>
                    @endif
                    @unless ($isMiniGarden)
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-semibold uppercase text-gray-500">Lokasi</dt>
                            <dd class="mt-1 font-medium text-gray-900">{{ $pemangkasan->lokasiLabel() }}</dd>
                        </div>
                    @endunless
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold uppercase text-gray-500">Pelaksana</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $pemangkasan->pelaksanaLabel() ?: '—' }}</dd>
                    </div>
                    @if ($isTumbang && $pemangkasan->dampak)
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-semibold uppercase text-gray-500">Dampak</dt>
                            <dd class="mt-1 whitespace-pre-line text-sm text-gray-700">{{ $pemangkasan->dampak }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            @if ($pemangkasan->progres->isNotEmpty())
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-bold text-gray-900">Progres Harian</h3>
                    <div class="mt-4 space-y-3">
                        @foreach ($pemangkasan->progres as $entry)
                            <div class="rounded-lg border border-gray-100 bg-gray-50 px-4 py-3 text-sm">
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <p class="font-semibold text-gray-900">
                                        Hari ke-{{ $entry->hari_ke }} · {{ \App\Support\OperasionalPelaksanaanTime::display($entry->tanggal) }}
                                        · {{ $entry->jumlah_personil }} personil
                                    </p>
                                    <div class="flex flex-wrap gap-2">
                                        <x-admin.can-write>
                                            <a href="{{ route('admin.pemangkasans.progres.edit', ['pemangkasan' => $pemangkasan, 'pemangkasanProgres' => $entry]) }}"
                                               class="rounded border border-blue-300 px-2.5 py-1 text-xs font-semibold text-blue-700 hover:bg-blue-50">
                                                Edit
                                            </a>
                                        </x-admin.can-write>
                                        <a href="{{ route('admin.pemangkasans.export-pdf-progres', ['pemangkasan' => $pemangkasan, 'pemangkasanProgres' => $entry]) }}"
                                           class="rounded border border-red-300 px-2.5 py-1 text-xs font-semibold text-red-700 hover:bg-red-50"
                                           target="_blank" rel="noopener">
                                            PDF
                                        </a>
                                    </div>
                                </div>
                                @if ($entry->catatan)
                                    <p class="mt-1 text-gray-600">{{ $entry->catatan }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="font-bold text-gray-900">Pendukung Pelaksanaan</h3>
                @if ($pemangkasan->hasPendukungPelaksanaanFile())
                    <div class="mt-4">
                        @if ($pemangkasan->pendukungPelaksanaanIsPdf())
                            <a href="{{ $pemangkasan->pendukung_pelaksanaan_url }}" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm font-medium text-green-700 hover:bg-gray-100">
                                📄 {{ $pemangkasan->pendukungPelaksanaanFilename() }}
                            </a>
                        @else
                            <a href="{{ $pemangkasan->pendukung_pelaksanaan_url }}" target="_blank" rel="noopener">
                                <img src="{{ $pemangkasan->pendukung_pelaksanaan_url }}" alt="Pendukung pelaksanaan"
                                     class="max-h-48 rounded-lg border border-gray-200 object-cover">
                            </a>
                        @endif
                    </div>
                @else
                    <p class="mt-3 text-sm text-gray-500">Belum ada file pendukung.</p>
                @endif
            </div>

            <div class="rounded-xl border border-gray-100 bg-gray-50 p-6 text-sm text-gray-600">
                <p class="font-semibold text-gray-800">Mode tampilan</p>
                <p class="mt-2">Halaman ini hanya untuk melihat detail. Gunakan tombol <strong>Edit Data</strong> jika perlu mengubah operasional.</p>
            </div>
        </div>
    </div>
@endsection
