@extends('layouts.admin')

@section('title', 'Evaluasi Kinerja Tim')
@section('header', 'Evaluasi Kinerja Tim')

@section('content')
    @include('admin.evaluasi._period-filter', [
        'action' => route('admin.evaluasi.kinerja-tim.index'),
        'exportRoute' => route('admin.evaluasi.kinerja-tim.export-pdf', request()->query()),
        'mode' => $mode,
        'bulan' => $bulan,
        'tahun' => $tahun,
        'dari' => $dari,
        'sampai' => $sampai,
        'daftarBulan' => $daftarBulan,
        'daftarTahun' => $daftarTahun,
        'accent' => 'blue',
        'title' => 'Filter Evaluasi Kinerja Tim',
        'description' => 'Perbandingan produktivitas tim pelaksana dari pemeliharaan rutin dan permohonan operasional',
        'filters' => view('admin.evaluasi.kinerja-tim._filters', compact('tim', 'daftarTim')),
    ])

    <p class="mb-4 text-sm text-gray-600">
        Periode evaluasi: <strong class="text-blue-800">{{ $labelPeriode }}</strong>
    </p>

    <div class="mb-8 grid grid-cols-2 gap-4 lg:grid-cols-5">
        <div class="rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50 to-indigo-50 p-5">
            <p class="text-sm font-medium text-blue-700">Total Kegiatan</p>
            <p class="mt-2 text-3xl font-bold text-blue-900">{{ number_format($totalKegiatan) }}</p>
        </div>
        <div class="rounded-2xl border border-green-100 bg-gradient-to-br from-green-50 to-emerald-50 p-5">
            <p class="text-sm font-medium text-green-700">Pemeliharaan</p>
            <p class="mt-2 text-3xl font-bold text-green-900">{{ number_format($totalPemeliharaan) }}</p>
        </div>
        <div class="rounded-2xl border border-violet-100 bg-gradient-to-br from-violet-50 to-purple-50 p-5">
            <p class="text-sm font-medium text-violet-700">Permohonan</p>
            <p class="mt-2 text-3xl font-bold text-violet-900">{{ number_format($totalPermohonan) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-600">Total Personil</p>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($totalPersonil) }}</p>
        </div>
        <div class="rounded-2xl border border-amber-100 bg-gradient-to-br from-amber-50 to-orange-50 p-5">
            <p class="text-sm font-medium text-amber-700">Tepat Waktu</p>
            <p class="mt-2 text-3xl font-bold text-amber-900">
                @if ($rataTepatWaktu !== null)
                    {{ $rataTepatWaktu }}%
                @else
                    —
                @endif
            </p>
            <p class="mt-1 text-xs text-amber-700/80">Penyelesaian permohonan</p>
        </div>
    </div>

    <div class="mb-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <h3 class="mb-4 text-base font-semibold text-gray-800">Grafik Volume Kegiatan per Tim</h3>
        <div class="h-80">
            <canvas id="kinerjaTimChart"></canvas>
        </div>
    </div>

    <x-admin.data-table>
        <x-slot:header>
            <div class="border-b border-gray-100 px-6 py-4">
                <h3 class="text-base font-semibold text-gray-800">Rekapitulasi Kinerja per Tim</h3>
                <p class="mt-1 text-xs text-gray-500">Klik angka pemeliharaan atau permohonan untuk drill-down ke data operasional</p>
            </div>
        </x-slot:header>
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Tim</th>
                <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Pemeliharaan</th>
                <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Permohonan</th>
                <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Total</th>
                <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Personil</th>
                <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Rata. Personil</th>
                <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Taman</th>
                <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Tepat Waktu</th>
                <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Survey</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($rekapPerTim as $row)
                <tr class="{{ $row['total_kegiatan'] === 0 ? 'bg-gray-50/80' : '' }}">
                    <td class="px-4 py-3 text-sm">
                        <p class="font-medium text-gray-900">{{ $row['tim'] }}</p>
                        @if ($row['nama_pengawas'])
                            <p class="text-xs text-gray-500">Pengawas: {{ $row['nama_pengawas'] }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right text-sm">
                        <a href="{{ $row['url_pemeliharaan'] }}" class="font-medium text-green-700 hover:underline">
                            {{ number_format($row['jumlah_pemeliharaan']) }}
                        </a>
                    </td>
                    <td class="px-4 py-3 text-right text-sm">
                        <a href="{{ $row['url_permohonan'] }}" class="font-medium text-violet-700 hover:underline">
                            {{ number_format($row['jumlah_permohonan']) }}
                        </a>
                    </td>
                    <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">{{ number_format($row['total_kegiatan']) }}</td>
                    <td class="px-4 py-3 text-right text-sm text-gray-700">{{ number_format($row['total_personil']) }}</td>
                    <td class="px-4 py-3 text-right text-sm text-gray-700">{{ number_format($row['rata_personil'], 1) }}</td>
                    <td class="px-4 py-3 text-right text-sm text-gray-700">{{ number_format($row['taman_terlayani']) }}</td>
                    <td class="px-4 py-3 text-right text-sm">
                        @if ($row['persen_tepat_waktu'] !== null)
                            <span class="{{ $row['persen_tepat_waktu'] >= 80 ? 'font-semibold text-green-700' : ($row['persen_tepat_waktu'] >= 60 ? 'font-semibold text-amber-700' : 'font-semibold text-red-700') }}">
                                {{ $row['persen_tepat_waktu'] }}%
                            </span>
                            <p class="text-xs text-gray-500">{{ number_format($row['permohonan_terlambat']) }} terlambat</p>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right text-sm text-gray-700">
                        @if ($row['survey_rata'] !== null)
                            <span class="font-semibold text-blue-700">{{ $row['survey_rata'] }}/5</span>
                            <p class="text-xs text-gray-500">{{ number_format($row['survey_jumlah']) }} respon</p>
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada data tim pelaksana.</td>
                </tr>
            @endforelse
        </tbody>
    </x-admin.data-table>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('kinerjaTimChart');
            if (!ctx) return;

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json($chartLabels),
                    datasets: [
                        {
                            label: 'Pemeliharaan',
                            data: @json($chartPemeliharaan),
                            backgroundColor: 'rgba(22, 163, 74, 0.7)',
                            borderColor: 'rgb(22, 163, 74)',
                            borderWidth: 1,
                        },
                        {
                            label: 'Permohonan',
                            data: @json($chartPermohonan),
                            backgroundColor: 'rgba(37, 99, 235, 0.7)',
                            borderColor: 'rgb(37, 99, 235)',
                            borderWidth: 1,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { stacked: true },
                        y: { stacked: true, beginAtZero: true, ticks: { precision: 0 } },
                    },
                },
            });
        });
    </script>
@endpush
