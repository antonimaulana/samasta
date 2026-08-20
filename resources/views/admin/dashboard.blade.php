@extends('layouts.admin')

@section('title', 'Dashboard')
@section('header', 'Dashboard')

@section('content')
    @php
        $statusConfig = match ($executive_status['tone'] ?? 'success') {
            'danger' => [
                'label' => $executive_status['label'] ?? 'Perlu Tindakan',
                'badge' => 'bg-red-500/20 text-red-100 ring-red-300/40',
                'dot' => 'bg-red-400',
            ],
            'warning' => [
                'label' => $executive_status['label'] ?? 'Waspada',
                'badge' => 'bg-amber-500/20 text-amber-100 ring-amber-300/40',
                'dot' => 'bg-amber-400',
            ],
            default => [
                'label' => $executive_status['label'] ?? 'Operasional Baik',
                'badge' => 'bg-white/15 text-white ring-white/25',
                'dot' => 'bg-emerald-300',
            ],
        };

        $surveyLabel = match (true) {
            $survey_summary['total'] === 0 => 'Belum ada data',
            $survey_summary['average'] >= 4.5 => 'Sangat Baik',
            $survey_summary['average'] >= 4.0 => 'Baik',
            $survey_summary['average'] >= 3.0 => 'Cukup',
            default => 'Perlu Perhatian',
        };
    @endphp

    {{-- Hero ringkasan eksekutif --}}
    <section class="relative mb-8 overflow-hidden rounded-2xl bg-gradient-to-br from-green-700 via-emerald-600 to-teal-600 p-6 text-white shadow-lg shadow-emerald-200/50 sm:p-8">
        <div class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-white/10 blur-2xl"></div>
        <div class="pointer-events-none absolute -bottom-20 -left-10 h-48 w-48 rounded-full bg-teal-300/20 blur-2xl"></div>

        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
            <div class="max-w-2xl">
                <div class="flex flex-wrap items-center gap-3">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-green-100">
                        {{ $is_viewer ? 'Ringkasan Pimpinan' : 'Dashboard Operasional' }}
                    </p>
                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $statusConfig['badge'] }}">
                        <span class="h-2 w-2 rounded-full {{ $statusConfig['dot'] }}"></span>
                        {{ $statusConfig['label'] }}
                    </span>
                </div>
                <h2 class="mt-3 text-2xl font-black sm:text-3xl">
                    @if ($is_viewer)
                        Snapshot Operasional Pertamanan Batam
                    @else
                        Ringkasan Operasional Dinas
                    @endif
                </h2>
                <p class="mt-2 text-sm leading-relaxed text-green-50/90">
                    {{ now()->translatedFormat('l, d F Y') }} · {{ now()->translatedFormat('H:i') }} WIB
                    — {{ $executive_status['description'] ?? 'Ringkasan operasional pertamanan Kota Batam.' }}
                </p>
            </div>

            <div class="flex flex-col gap-4 lg:items-end">
                <a href="{{ route('admin.dashboard.export-pdf') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/25 bg-white/15 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur-sm transition hover:bg-white/25">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Unduh Ringkasan PDF
                </a>
                <div class="grid grid-cols-3 gap-3 sm:gap-4">
                <div class="rounded-xl border border-white/15 bg-white/10 px-4 py-3 text-center backdrop-blur-sm">
                    <p class="text-2xl font-black">{{ number_format($total_taman) }}</p>
                    <p class="mt-1 text-[10px] font-semibold uppercase tracking-wide text-green-100">Taman</p>
                </div>
                <div class="rounded-xl border border-white/15 bg-white/10 px-4 py-3 text-center backdrop-blur-sm">
                    <p class="text-2xl font-black">{{ $layanan_selesai_persen }}%</p>
                    <p class="mt-1 text-[10px] font-semibold uppercase tracking-wide text-green-100">Operasional Selesai</p>
                </div>
                <div class="rounded-xl border border-white/15 bg-white/10 px-4 py-3 text-center backdrop-blur-sm">
                    <p class="text-2xl font-black">{{ $survey_summary['total'] > 0 ? number_format($survey_summary['average'], 1) : '—' }}</p>
                    <p class="mt-1 text-[10px] font-semibold uppercase tracking-wide text-green-100">Kepuasan</p>
                </div>
                </div>
            </div>
        </div>
    </section>

    @include('admin.partials.operator_inbox')

    {{-- Indikator prioritas pimpinan --}}
    <div class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <a href="{{ route('admin.survey-kepuasan.index') }}"
           class="group overflow-hidden rounded-2xl border border-violet-100 bg-gradient-to-br from-violet-50 to-fuchsia-50 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-violet-600">Kepuasan Masyarakat</p>
                    <p class="mt-2 text-3xl font-black text-violet-900">
                        {{ $survey_summary['total'] > 0 ? number_format($survey_summary['average'], 1) : '—' }}
                        <span class="text-lg text-violet-400">/5</span>
                    </p>
                    <p class="mt-1 text-sm font-medium text-violet-700">{{ $surveyLabel }}</p>
                </div>
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-violet-100 text-violet-600">
                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                </span>
            </div>
            <p class="mt-3 text-xs text-violet-600/80">{{ number_format($survey_summary['total']) }} respons · {{ now()->translatedFormat('F Y') }}</p>
        </a>

        <a href="{{ route('admin.operasional-pertamanan-laporan.index') }}"
           class="group overflow-hidden rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-teal-50 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-bold uppercase tracking-wide text-emerald-600">Penyelesaian Operasional</p>
                    <p class="mt-2 text-3xl font-black text-emerald-900">{{ $layanan_selesai_persen }}%</p>
                    <p class="mt-1 text-sm text-emerald-700">{{ number_format($layanan_selesai) }} dari {{ number_format($total_layanan) }} selesai</p>
                </div>
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="mt-3 h-2 overflow-hidden rounded-full bg-emerald-100">
                <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-500" style="width: {{ $layanan_selesai_persen }}%"></div>
            </div>
        </a>

        <a href="{{ route('admin.aduan-masyarakats.index') }}"
           class="group overflow-hidden rounded-2xl border p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md {{ $aduan_baru > 0 ? 'border-red-200 bg-gradient-to-br from-red-50 to-orange-50' : 'border-sky-100 bg-gradient-to-br from-sky-50 to-blue-50' }}">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide {{ $aduan_baru > 0 ? 'text-red-600' : 'text-sky-600' }}">Aduan Masyarakat</p>
                    <p class="mt-2 text-3xl font-black {{ $aduan_baru > 0 ? 'text-red-800' : 'text-sky-900' }}">{{ number_format($aduan_baru) }}</p>
                    <p class="mt-1 text-sm {{ $aduan_baru > 0 ? 'text-red-700' : 'text-sky-700' }}">Baru · {{ number_format($aduan_aktif) }} aktif</p>
                </div>
                <span class="flex h-11 w-11 items-center justify-center rounded-xl {{ $aduan_baru > 0 ? 'bg-red-100 text-red-600' : 'bg-sky-100 text-sky-600' }}">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/></svg>
                </span>
            </div>
        </a>

        <a href="{{ route('admin.pemangkasans.index', ['view' => 'terlambat']) }}"
           class="group overflow-hidden rounded-2xl border p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md {{ ($jadwal_counts['terlambat'] ?? 0) > 0 ? 'border-red-200 bg-gradient-to-br from-rose-50 to-red-50' : 'border-gray-200 bg-white' }}">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide {{ ($jadwal_counts['terlambat'] ?? 0) > 0 ? 'text-red-600' : 'text-gray-500' }}">Jadwal Terlambat</p>
                    <p class="mt-2 text-3xl font-black {{ ($jadwal_counts['terlambat'] ?? 0) > 0 ? 'text-red-800' : 'text-gray-900' }}">{{ $jadwal_counts['terlambat'] ?? 0 }}</p>
                    <p class="mt-1 text-sm {{ ($jadwal_counts['terlambat'] ?? 0) > 0 ? 'text-red-700' : 'text-gray-500' }}">
                        {{ ($jadwal_counts['terlambat'] ?? 0) > 0 ? 'Perlu tindak lanjut segera' : 'Tidak ada keterlambatan' }}
                    </p>
                </div>
                <span class="flex h-11 w-11 items-center justify-center rounded-xl {{ ($jadwal_counts['terlambat'] ?? 0) > 0 ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-500' }}">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
        </a>
    </div>

    {{-- Jadwal operasional --}}
    <section class="mb-8 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 bg-gray-50/80 px-5 py-4">
            <div>
                <h3 class="font-bold text-gray-900">Jadwal Operasional Lapangan</h3>
                <p class="text-xs text-gray-500">Monitoring pelaksanaan harian tim pertamanan</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.pemangkasans.index', ['view' => 'hari_ini']) }}"
                   class="rounded-lg bg-green-600 px-4 py-2 text-xs font-bold text-white hover:bg-green-700">
                    Buka Antrian
                </a>
                <x-admin.can-write>
                    <a href="{{ route('admin.pemangkasans.create') }}"
                       class="rounded-lg border border-green-200 bg-white px-4 py-2 text-xs font-bold text-green-700 hover:bg-green-50">
                        + Tambah Operasional
                    </a>
                </x-admin.can-write>
            </div>
        </div>
        <div class="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['key' => 'hari_ini', 'label' => 'Hari Ini', 'desc' => 'Pelaksanaan hari ini', 'accent' => 'emerald'],
                ['key' => 'besok', 'label' => 'Besok (H-1)', 'desc' => 'Persiapan besok', 'accent' => 'blue'],
                ['key' => 'diproses', 'label' => 'Sedang Diproses', 'desc' => 'Dikerjakan di lapangan', 'accent' => 'amber'],
                ['key' => 'terlambat', 'label' => 'Terlambat', 'desc' => 'Lewat jadwal', 'accent' => 'red', 'danger' => true],
            ] as $item)
                @php
                    $count = $jadwal_counts[$item['key']] ?? 0;
                    $isDanger = ($item['danger'] ?? false) && $count > 0;
                @endphp
                <a href="{{ route('admin.pemangkasans.index', ['view' => $item['key']]) }}"
                   class="rounded-xl border p-4 transition hover:shadow-md {{ $isDanger ? 'border-red-200 bg-red-50/80' : 'border-gray-100 bg-white hover:border-green-200' }}">
                    <p class="text-3xl font-black {{ $isDanger ? 'text-red-700' : 'text-gray-900' }}">{{ $count }}</p>
                    <p class="mt-1 text-sm font-semibold text-gray-900">{{ $item['label'] }}</p>
                    <p class="mt-0.5 text-xs text-gray-500">{{ $item['desc'] }}</p>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Aset hijau --}}
    <div class="mb-8 grid gap-4 lg:grid-cols-2">
        <a href="{{ route('admin.tamans.index') }}"
           class="flex items-center gap-5 rounded-2xl border border-green-100 bg-gradient-to-r from-green-50 to-emerald-50 p-5 shadow-sm transition hover:shadow-md">
            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-green-600 text-2xl text-white">🌳</span>
            <div>
                <p class="text-xs font-bold uppercase tracking-wide text-green-700">Data Taman</p>
                <p class="text-2xl font-black text-gray-900">{{ number_format($total_taman) }} <span class="text-base font-semibold text-gray-500">taman terdaftar</span></p>
                <p class="text-xs text-gray-600">Data taman publik {{ config('app.name') }}</p>
            </div>
        </a>
        <a href="{{ route('admin.bibits.index') }}"
           class="flex items-center gap-5 rounded-2xl border border-teal-100 bg-gradient-to-r from-teal-50 to-cyan-50 p-5 shadow-sm transition hover:shadow-md">
            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-teal-600 text-2xl text-white">🌱</span>
            <div>
                <p class="text-xs font-bold uppercase tracking-wide text-teal-700">Pembibitan</p>
                <p class="text-2xl font-black text-gray-900">{{ number_format($total_stok_bibit) }} <span class="text-base font-semibold text-gray-500">unit stok</span></p>
                <p class="text-xs text-gray-600">{{ number_format($total_varietas_bibit) }} varietas · {{ $bibit_siap_persen }}% siap tanam</p>
            </div>
        </a>
    </div>

    @include('admin.partials.operational_alerts')

    {{-- Statistik detail — untuk drill-down --}}
    <details class="group overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-5 py-4 marker:content-none hover:bg-gray-50">
            <div>
                <h3 class="font-bold text-gray-900">Analisis Detail</h3>
                <p class="text-xs text-gray-500">Breakdown status operasional, pembibitan, dan per jenis operasional</p>
            </div>
            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600 group-open:hidden">Buka</span>
            <span class="hidden rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600 group-open:inline">Tutup</span>
        </summary>

        <div class="border-t border-gray-100 p-5">
            <div class="mb-6 grid gap-4 sm:grid-cols-3">
                @foreach ([
                    ['key' => 'rencana', 'label' => 'Rencana', 'color' => 'text-blue-700', 'bg' => 'bg-blue-50 border-blue-100'],
                    ['key' => 'diproses', 'label' => 'Diproses', 'color' => 'text-amber-700', 'bg' => 'bg-amber-50 border-amber-100'],
                    ['key' => 'selesai', 'label' => 'Selesai', 'color' => 'text-green-700', 'bg' => 'bg-green-50 border-green-100'],
                ] as $item)
                    <div class="rounded-xl border p-4 text-center {{ $item['bg'] }}">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $item['label'] }}</p>
                        <p class="mt-1 text-3xl font-black {{ $item['color'] }}">{{ number_format($status_global[$item['key']]) }}</p>
                    </div>
                @endforeach
            </div>

            <div class="overflow-hidden rounded-xl border border-gray-100">
                <div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
                    <h4 class="font-semibold text-gray-900">Operasional per Jenis</h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-white">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Jenis Operasional</th>
                                <th class="px-4 py-3 text-center font-medium text-gray-600">Rencana</th>
                                <th class="px-4 py-3 text-center font-medium text-gray-600">Diproses</th>
                                <th class="px-4 py-3 text-center font-medium text-gray-600">Selesai</th>
                                <th class="px-4 py-3 text-center font-medium text-gray-600">Progress</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($layanan_per_jenis as $row)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <span class="mr-2">{{ $row['icon'] }}</span>
                                        <span class="font-medium text-gray-900">{{ $row['jenis'] }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center">{{ $row['rencana'] }}</td>
                                    <td class="px-4 py-3 text-center">{{ $row['diproses'] }}</td>
                                    <td class="px-4 py-3 text-center">{{ $row['selesai'] }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-gray-100">
                                                <div class="h-full rounded-full bg-green-500" style="width: {{ $row['progress'] }}%"></div>
                                            </div>
                                            <span class="w-8 text-xs font-semibold text-gray-600">{{ $row['progress'] }}%</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('admin.pemangkasans.index', ['jenis' => $row['jenis']]) }}"
                                           class="text-xs font-semibold text-green-700 hover:underline">Detail</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </details>

    @unless ($canWrite ?? auth()->user()?->canWrite())
        <p class="mt-8 text-center text-xs text-gray-400">
            Klik kartu di atas untuk melihat detail. Gunakan tombol <strong>Kembali ke Dashboard</strong> untuk kembali ke ringkasan.
        </p>
    @endunless
@endsection
