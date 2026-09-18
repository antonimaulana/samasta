@extends('layouts.admin')

@section('title', 'Evaluasi RTH Terpelihara')
@section('header', 'Evaluasi RTH Terpelihara')

@section('content')
    <div class="mb-6 overflow-hidden rounded-2xl border border-teal-100 bg-white shadow-sm">
        <div class="border-b border-teal-50 bg-gradient-to-r from-teal-50 to-emerald-50 px-6 py-4">
            <h2 class="text-lg font-semibold text-teal-800">Filter Evaluasi</h2>
            <p class="mt-1 text-sm text-teal-700/80">Indikator RPJMD: persentase lokasi dan luasan RTH dalam kondisi terpelihara berdasarkan riwayat pemeliharaan rutin</p>
        </div>

        <form action="{{ route('admin.evaluasi.rth-terpelihara.index') }}" method="GET" class="space-y-4 p-6">
            @include('admin.evaluasi.rth-terpelihara._filters')

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="rounded-lg bg-teal-600 px-5 py-2 text-sm font-medium text-white hover:bg-teal-700">
                    Tampilkan Evaluasi
                </button>
                <a href="{{ route('admin.evaluasi.rth-terpelihara.export-pdf', request()->query()) }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-teal-300 bg-white px-5 py-2 text-sm font-semibold text-teal-800 hover:bg-teal-50">
                    📄 Export PDF
                </a>
            </div>
        </form>
    </div>

    <p class="mb-1 text-sm text-gray-600">
        {{ $labelSnapshot }}
    </p>
    <p class="mb-4 text-sm text-teal-700">
        <strong>{{ $labelKriteria }}</strong>
        (batas: {{ $cutoffDate->timezone(config('app.timezone'))->translatedFormat('d F Y') }})
    </p>

    <div class="mb-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-2xl border border-teal-100 bg-gradient-to-br from-teal-50 to-emerald-50 p-5">
            <p class="text-sm font-medium text-teal-700">Lokasi Terpelihara</p>
            <p class="mt-2 text-3xl font-bold text-teal-900">
                {{ number_format($lokasiTerpelihara) }}
                <span class="text-lg font-semibold text-teal-700">/ {{ number_format($totalLokasi) }}</span>
            </p>
            <p class="mt-1 text-sm font-semibold text-emerald-700">{{ $persenLokasi }}%</p>
        </div>
        <div class="rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-green-50 p-5">
            <p class="text-sm font-medium text-emerald-700">Luasan Terpelihara</p>
            <p class="mt-2 text-2xl font-bold text-emerald-900">
                {{ number_format($luasanTerpelihara, 0, ',', '.') }}
                <span class="text-sm font-medium text-emerald-700">m²</span>
            </p>
            <p class="mt-1 text-sm font-semibold text-emerald-700">{{ $persenLuasan }}% dari {{ number_format($totalLuasan, 0, ',', '.') }} m²</p>
        </div>
        <div class="rounded-2xl border border-amber-100 bg-gradient-to-br from-amber-50 to-orange-50 p-5">
            <p class="text-sm font-medium text-amber-700">Perlu Perhatian</p>
            <p class="mt-2 text-3xl font-bold text-amber-900">{{ number_format($lokasiBelumTerpelihara) }}</p>
            <p class="mt-1 text-sm text-amber-700">lokasi · {{ number_format($luasanBelumTerpelihara, 0, ',', '.') }} m²</p>
        </div>
        <div class="rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50 to-indigo-50 p-5">
            <p class="text-sm font-medium text-blue-700">vs RTRW Publik</p>
            <p class="mt-2 text-3xl font-bold text-blue-900">{{ $persenRtrw }}%</p>
            <p class="mt-1 text-xs text-blue-700">dari {{ number_format($rtrwLuasan, 0, ',', '.') }} m² RTRW</p>
        </div>
    </div>

    <div class="mb-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <h3 class="mb-4 text-base font-semibold text-gray-800">Grafik Status per Kategori RTH</h3>
        <div class="h-72">
            <canvas id="rthTerpeliharaChart"></canvas>
        </div>
    </div>

    <div class="mb-8 grid gap-6 lg:grid-cols-2">
        <x-admin.data-table>
            <x-slot:header>
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-800">Rekap per Kategori</h3>
                </div>
            </x-slot:header>
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Kategori</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Lokasi</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Terpelihara</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Luasan (m²)</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">%</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($rekapPerKategori as $row)
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $row['label'] }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-700">{{ number_format($row['total_lokasi']) }}</td>
                        <td class="px-4 py-3 text-right text-sm text-teal-700">{{ number_format($row['lokasi_terpelihara']) }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-700">{{ number_format($row['total_luasan'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-sm font-semibold text-emerald-700">{{ $row['persen_lokasi'] }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada data taman.</td>
                    </tr>
                @endforelse
            </tbody>
        </x-admin.data-table>

        <x-admin.data-table>
            <x-slot:header>
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-800">Rekap per Kecamatan</h3>
                </div>
            </x-slot:header>
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Kecamatan</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Lokasi</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Terpelihara</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Luasan (m²)</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">%</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($rekapPerKecamatan as $row)
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $row['label'] }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-700">{{ number_format($row['total_lokasi']) }}</td>
                        <td class="px-4 py-3 text-right text-sm text-teal-700">{{ number_format($row['lokasi_terpelihara']) }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-700">{{ number_format($row['total_luasan'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-sm font-semibold text-emerald-700">{{ $row['persen_lokasi'] }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada data taman.</td>
                    </tr>
                @endforelse
            </tbody>
        </x-admin.data-table>
    </div>

    <x-admin.data-table>
        <x-slot:header>
            <div class="border-b border-amber-100 bg-amber-50/50 px-6 py-4">
                <h3 class="text-base font-semibold text-amber-900">Lokasi Perlu Perhatian</h3>
                <p class="mt-1 text-sm text-amber-800/80">Taman tanpa pemeliharaan rutin dalam {{ $freshDays }} hari terakhir</p>
            </div>
        </x-slot:header>
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Taman</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Kategori</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Wilayah</th>
                <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Luasan</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Pemeliharaan Terakhir</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($belumTerpelihara as $row)
                <tr class="bg-amber-50/30">
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">
                        <a href="{{ $row['taman_url'] }}" class="text-teal-700 hover:underline">{{ $row['nama'] }}</a>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $row['kategori'] }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $row['wilayah'] }}</td>
                    <td class="px-4 py-3 text-right text-sm text-gray-700">{{ number_format($row['luasan'], 0, ',', '.') }} m²</td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                        @if ($row['latest_maintenance'])
                            <a href="{{ $row['url'] }}" class="text-amber-800 hover:underline">
                                {{ $row['latest_maintenance']->timezone(config('app.timezone'))->translatedFormat('d M Y') }}
                                ({{ $row['hari_sejak'] }} hari lalu)
                            </a>
                        @else
                            <span class="font-medium text-red-600">Belum pernah</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-sm text-emerald-700">Semua lokasi dalam kondisi terpelihara.</td>
                </tr>
            @endforelse
        </tbody>
    </x-admin.data-table>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('rthTerpeliharaChart');
            if (!ctx) return;

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json($chartLabels),
                    datasets: [
                        {
                            label: 'Terpelihara',
                            data: @json($chartTerpelihara),
                            backgroundColor: 'rgba(20, 184, 166, 0.75)',
                            borderColor: 'rgb(20, 184, 166)',
                            borderWidth: 1,
                        },
                        {
                            label: 'Perlu Perhatian',
                            data: @json($chartBelum),
                            backgroundColor: 'rgba(245, 158, 11, 0.75)',
                            borderColor: 'rgb(245, 158, 11)',
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
