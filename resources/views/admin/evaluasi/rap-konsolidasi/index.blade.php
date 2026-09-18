@extends('layouts.admin')

@section('title', 'Laporan RAP Konsolidasi')
@section('header', 'Laporan RAP Konsolidasi')

@section('content')
    @include('admin.evaluasi._period-filter', [
        'action' => route('admin.evaluasi.rap-konsolidasi.index'),
        'exportRoute' => route('admin.evaluasi.rap-konsolidasi.export-pdf', request()->query()),
        'mode' => $mode,
        'bulan' => $bulan,
        'tahun' => $tahun,
        'dari' => $dari,
        'sampai' => $sampai,
        'daftarBulan' => $daftarBulan,
        'daftarTahun' => $daftarTahun,
        'accent' => 'slate',
        'title' => 'Filter Laporan RAP',
        'description' => 'Ringkasan eksekutif seluruh indikator evaluasi SIMTAMAN untuk pengambilan keputusan RAP',
        'filters' => view('admin.evaluasi.rap-konsolidasi._filters', [
            'kategori' => $kategori,
            'kecamatan_id' => $kecamatan_id,
            'daftarKategori' => $daftarKategori,
            'daftarKecamatan' => $daftarKecamatan,
        ]),
    ])

    <div class="mb-6 rounded-2xl border border-slate-200 bg-gradient-to-r from-slate-800 to-slate-700 p-6 text-white shadow-lg">
        <p class="text-sm font-medium text-slate-300">Optimalisasi Pengelolaan Data dan Monitoring Operasional RTH</p>
        <div class="mt-3 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-wider text-slate-400">Indeks Kinerja RAP</p>
                <p class="mt-1 text-5xl font-black tracking-tight">
                    @if ($indeksKinerja !== null)
                        {{ $indeksKinerja }}<span class="text-2xl font-semibold text-slate-300">%</span>
                    @else
                        —
                    @endif
                </p>
            </div>
            <div class="text-right text-sm text-slate-300">
                <p>Periode operasional: <strong class="text-white">{{ $labelPeriode }}</strong></p>
                <p class="mt-1">Snapshot basis data: {{ $labelSnapshot }}</p>
            </div>
        </div>
    </div>

    <div class="mb-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <h3 class="mb-4 text-base font-semibold text-gray-800">Radar Indikator RAP</h3>
        <div class="h-80">
            <canvas id="rapChart"></canvas>
        </div>
    </div>

    @foreach ($pillars as $pillar)
        <div class="mb-8 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 bg-gray-50 px-6 py-4">
                <h3 class="text-base font-semibold text-gray-900">{{ $pillar['pillar'] }}</h3>
                <p class="mt-1 text-sm text-gray-600">{{ $pillar['description'] }}</p>
            </div>
            <div class="grid gap-4 p-6 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($pillar['indicators'] as $indicator)
                    @php
                        $levelClasses = match ($indicator['level']) {
                            'good' => 'border-emerald-200 bg-emerald-50',
                            'warn' => 'border-amber-200 bg-amber-50',
                            'bad' => 'border-red-200 bg-red-50',
                            default => 'border-gray-200 bg-gray-50',
                        };
                        $valueClasses = match ($indicator['level']) {
                            'good' => 'text-emerald-800',
                            'warn' => 'text-amber-800',
                            'bad' => 'text-red-800',
                            default => 'text-gray-700',
                        };
                    @endphp
                    <a href="{{ $indicator['url'] }}"
                       class="block rounded-xl border p-5 transition hover:shadow-md {{ $levelClasses }}">
                        <p class="text-sm font-medium text-gray-700">{{ $indicator['label'] }}</p>
                        <p class="mt-2 text-3xl font-bold {{ $valueClasses }}">{{ $indicator['value'] }}</p>
                        <p class="mt-2 text-xs text-gray-600">{{ $indicator['detail'] }}</p>
                        <p class="mt-3 text-xs font-semibold text-slate-600">Lihat detail →</p>
                    </a>
                @endforeach
            </div>
        </div>
    @endforeach

    <x-admin.data-table>
        <x-slot:header>
            <div class="border-b border-red-100 bg-red-50/50 px-6 py-4">
                <h3 class="text-base font-semibold text-red-900">Prioritas Tindak Lanjut</h3>
                <p class="mt-1 text-sm text-red-800/80">Gabungan isu kritis dari RTH terpelihara, kelengkapan data, dan aduan masyarakat</p>
            </div>
        </x-slot:header>
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Prioritas</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Objek</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Keterangan</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($prioritas as $item)
                <tr>
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $item['prioritas'] }}</td>
                    <td class="px-4 py-3 text-sm">
                        <a href="{{ $item['url'] }}" class="font-medium text-slate-700 hover:underline">{{ $item['label'] }}</a>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $item['detail'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="px-4 py-8 text-center text-sm text-emerald-700">Tidak ada isu kritis teridentifikasi.</td>
                </tr>
            @endforelse
        </tbody>
    </x-admin.data-table>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('rapChart');
            if (!ctx) return;

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json($chartLabels),
                    datasets: [{
                        label: 'Capaian (%)',
                        data: @json($chartValues),
                        backgroundColor: @json($chartColors),
                        borderWidth: 1,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: { callback: (value) => value + '%' },
                        },
                    },
                },
            });
        });
    </script>
@endpush
