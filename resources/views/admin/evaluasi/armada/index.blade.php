@extends('layouts.admin')

@section('title', 'Evaluasi Utilisasi Armada')
@section('header', 'Evaluasi Utilisasi Armada')

@section('content')
    @include('admin.evaluasi._period-filter', [
        'action' => route('admin.evaluasi.armada.index'),
        'exportRoute' => route('admin.evaluasi.armada.export-pdf', request()->query()),
        'mode' => $mode,
        'bulan' => $bulan,
        'tahun' => $tahun,
        'dari' => $dari,
        'sampai' => $sampai,
        'daftarBulan' => $daftarBulan,
        'daftarTahun' => $daftarTahun,
        'accent' => 'violet',
        'title' => 'Filter Evaluasi Armada',
        'description' => 'Rekapitulasi penggunaan armada dari pemeliharaan rutin dan permohonan operasional',
    ])

    <p class="mb-4 text-sm text-gray-600">
        Periode evaluasi: <strong class="text-violet-800">{{ $labelPeriode }}</strong>
    </p>

    <div class="mb-8 grid grid-cols-2 gap-4 lg:grid-cols-5">
        <div class="rounded-2xl border border-violet-100 bg-gradient-to-br from-violet-50 to-purple-50 p-5">
            <p class="text-sm font-medium text-violet-700">Total Penugasan</p>
            <p class="mt-2 text-3xl font-bold text-violet-900">{{ number_format($totalPenugasan) }}</p>
        </div>
        <div class="rounded-2xl border border-green-100 bg-gradient-to-br from-green-50 to-emerald-50 p-5">
            <p class="text-sm font-medium text-green-700">Pemeliharaan</p>
            <p class="mt-2 text-3xl font-bold text-green-900">{{ number_format($totalPemeliharaan) }}</p>
        </div>
        <div class="rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50 to-indigo-50 p-5">
            <p class="text-sm font-medium text-blue-700">Permohonan</p>
            <p class="mt-2 text-3xl font-bold text-blue-900">{{ number_format($totalPermohonan) }}</p>
        </div>
        <div class="rounded-2xl border border-amber-100 bg-gradient-to-br from-amber-50 to-orange-50 p-5">
            <p class="text-sm font-medium text-amber-700">Armada Terpakai</p>
            <p class="mt-2 text-3xl font-bold text-amber-900">{{ number_format($armadaTerpakai) }}<span class="text-lg font-semibold text-amber-700"> / {{ number_format($totalArmada) }}</span></p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-600">Armada Idle</p>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($armadaIdle) }}</p>
        </div>
    </div>

    <div class="mb-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <h3 class="mb-4 text-base font-semibold text-gray-800">Grafik Penugasan Armada Harian</h3>
        <div class="h-72">
            <canvas id="armadaChart"></canvas>
        </div>
    </div>

    <div class="mb-8 grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-admin.data-table>
                <x-slot:header>
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-base font-semibold text-gray-800">Rekap Utilisasi per Armada</h3>
                    </div>
                </x-slot:header>
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Armada</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Pemeliharaan</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Permohonan</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Total</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Sopir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($rekapPerArmada as $row)
                        <tr class="{{ $row['total_penugasan'] === 0 ? 'bg-gray-50/80' : '' }}">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                <a href="{{ $row['url'] }}" class="text-violet-700 hover:underline">{{ $row['armada']->nama }}</a>
                                <p class="text-xs text-gray-500">{{ $row['armada']->jenis }} · {{ $row['armada']->no_plat ?: '—' }}</p>
                            </td>
                            <td class="px-4 py-3 text-right text-sm text-gray-700">{{ number_format($row['jumlah_pemeliharaan']) }}</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-700">{{ number_format($row['jumlah_permohonan']) }}</td>
                            <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">{{ number_format($row['total_penugasan']) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ implode(', ', $row['sopir']) ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada inventaris armada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-admin.data-table>
        </div>

        <x-admin.data-table>
            <x-slot:header>
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-800">Sopir Paling Aktif</h3>
                </div>
            </x-slot:header>
            <tbody class="divide-y divide-gray-100">
                @forelse ($rekapSopir as $row)
                    <tr>
                        <td class="flex items-center justify-between px-4 py-3 text-sm">
                            <span class="font-medium text-gray-900">{{ $row['sopir'] }}</span>
                            <span class="font-semibold text-violet-700">{{ number_format($row['jumlah']) }}×</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-8 text-center text-sm text-gray-500">Belum ada penugasan sopir.</td>
                    </tr>
                @endforelse
            </tbody>
        </x-admin.data-table>
    </div>
@endsection

@push('scripts')
    <script>
        function toggleEvaluasiPeriodeFields() {
            const mode = document.querySelector('input[name="mode"]:checked')?.value;
            document.getElementById('evaluasi-filter-bulan')?.classList.toggle('hidden', mode === 'periode');
            document.getElementById('evaluasi-filter-periode')?.classList.toggle('hidden', mode === 'bulan');
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('armadaChart');
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
                            backgroundColor: 'rgba(124, 58, 237, 0.7)',
                            borderColor: 'rgb(124, 58, 237)',
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
