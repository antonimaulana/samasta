@extends('layouts.admin')

@section('title', 'Evaluasi Masukan Masyarakat')
@section('header', 'Evaluasi Masukan Masyarakat')

@section('content')
    @include('admin.evaluasi._period-filter', [
        'action' => route('admin.evaluasi.masukan-masyarakat.index'),
        'exportRoute' => route('admin.evaluasi.masukan-masyarakat.export-pdf', request()->query()),
        'mode' => $mode,
        'bulan' => $bulan,
        'tahun' => $tahun,
        'dari' => $dari,
        'sampai' => $sampai,
        'daftarBulan' => $daftarBulan,
        'daftarTahun' => $daftarTahun,
        'accent' => 'rose',
        'title' => 'Filter Evaluasi Masukan',
        'description' => 'Analisis aduan masyarakat dan survey kepuasan untuk evaluasi respons layanan publik',
        'filters' => view('admin.evaluasi.masukan-masyarakat._filters', [
            'jenisAduan' => $jenisAduan,
            'status' => $status,
            'kategoriSurvey' => $kategoriSurvey,
            'daftarJenisAduan' => $daftarJenisAduan,
            'daftarStatus' => $daftarStatus,
            'daftarKategoriSurvey' => $daftarKategoriSurvey,
        ]),
    ])

    <p class="mb-4 text-sm text-gray-600">
        Periode evaluasi: <strong class="text-rose-800">{{ $labelPeriode }}</strong>
    </p>

    <div class="mb-8 grid grid-cols-2 gap-4 lg:grid-cols-6">
        <div class="rounded-2xl border border-rose-100 bg-gradient-to-br from-rose-50 to-pink-50 p-5">
            <p class="text-sm font-medium text-rose-700">Total Masukan</p>
            <p class="mt-2 text-3xl font-bold text-rose-900">{{ number_format($totalMasukan) }}</p>
            <p class="mt-1 text-xs text-rose-700/80">{{ number_format($totalAduan) }} aduan · {{ number_format($totalSurvey) }} survey</p>
        </div>
        <div class="rounded-2xl border border-green-100 bg-gradient-to-br from-green-50 to-emerald-50 p-5">
            <p class="text-sm font-medium text-green-700">Aduan Selesai</p>
            <p class="mt-2 text-3xl font-bold text-green-900">{{ number_format($aduanSelesai) }}</p>
            <p class="mt-1 text-xs text-green-700/80">{{ $persenSelesai }}% dari aduan periode</p>
        </div>
        <div class="rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50 to-indigo-50 p-5">
            <p class="text-sm font-medium text-blue-700">Aduan Aktif</p>
            <p class="mt-2 text-3xl font-bold text-blue-900">{{ number_format($aduanOpen) }}</p>
            <p class="mt-1 text-xs text-blue-700/80">Baru / Ditinjau / Diproses</p>
        </div>
        <div class="rounded-2xl border border-red-100 bg-gradient-to-br from-red-50 to-rose-50 p-5">
            <p class="text-sm font-medium text-red-700">Belum Ditinjau</p>
            <p class="mt-2 text-3xl font-bold text-red-900">{{ number_format($aduanOverdue) }}</p>
            <p class="mt-1 text-xs text-red-700/80">&gt; {{ $unreviewedDays }} hari status Baru</p>
        </div>
        <div class="rounded-2xl border border-violet-100 bg-gradient-to-br from-violet-50 to-purple-50 p-5">
            <p class="text-sm font-medium text-violet-700">Rata. Penyelesaian</p>
            <p class="mt-2 text-3xl font-bold text-violet-900">
                @if ($rataHariPenyelesaian !== null)
                    {{ number_format($rataHariPenyelesaian, 1) }}<span class="text-lg font-semibold text-violet-700"> hari</span>
                @else
                    —
                @endif
            </p>
            <p class="mt-1 text-xs text-violet-700/80">
                @if ($persenDitanggapi !== null)
                    Ditanggapi: {{ $persenDitanggapi }}%
                @else
                    Aduan → selesai
                @endif
            </p>
        </div>
        <div class="rounded-2xl border border-amber-100 bg-gradient-to-br from-amber-50 to-yellow-50 p-5">
            <p class="text-sm font-medium text-amber-700">Rating Survey</p>
            <p class="mt-2 text-3xl font-bold text-amber-900">
                @if ($surveyAverage !== null)
                    {{ number_format($surveyAverage, 1) }}<span class="text-lg font-semibold text-amber-700"> / 5</span>
                @else
                    —
                @endif
            </p>
            <p class="mt-1 text-xs text-amber-700/80">
                @if ($persenPuas !== null)
                    Puas (≥4): {{ $persenPuas }}%
                @else
                    Belum ada survey
                @endif
            </p>
        </div>
    </div>

    <div class="mb-8 grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-base font-semibold text-gray-800">Grafik Masukan Harian</h3>
            <div class="h-72">
                <canvas id="masukanChart"></canvas>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-base font-semibold text-gray-800">Distribusi Rating Survey</h3>
            <div class="h-72">
                <canvas id="ratingChart"></canvas>
            </div>
        </div>
    </div>

    <div class="mb-8 grid gap-6 lg:grid-cols-2">
        <x-admin.data-table>
            <x-slot:header>
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-800">Rekap Aduan per Jenis</h3>
                </div>
            </x-slot:header>
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Jenis Aduan</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Total</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Selesai</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Aktif</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Terlambat</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($rekapPerJenis as $row)
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $row['jenis'] }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-700">{{ number_format($row['total']) }}</td>
                        <td class="px-4 py-3 text-right text-sm text-green-700">{{ number_format($row['selesai']) }}</td>
                        <td class="px-4 py-3 text-right text-sm text-blue-700">{{ number_format($row['open']) }}</td>
                        <td class="px-4 py-3 text-right text-sm text-red-700">{{ number_format($row['overdue']) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada aduan pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </x-admin.data-table>

        <x-admin.data-table>
            <x-slot:header>
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-800">Rekap Survey per Kategori</h3>
                </div>
            </x-slot:header>
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Kategori</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Total</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Rata-rata</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Puas (≥4)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($rekapSurvey as $row)
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $row['kategori'] }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-700">{{ number_format($row['total']) }}</td>
                        <td class="px-4 py-3 text-right text-sm font-semibold text-amber-700">
                            {{ $row['average'] !== null ? number_format($row['average'], 1) : '—' }}
                        </td>
                        <td class="px-4 py-3 text-right text-sm text-green-700">{{ number_format($row['puas']) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada survey pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </x-admin.data-table>
    </div>

    <div class="mb-8">
        <x-admin.data-table>
            <x-slot:header>
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-800">Taman dengan Aduan Terbanyak</h3>
                </div>
            </x-slot:header>
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Taman</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Kecamatan</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Aduan Periode</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Masih Aktif</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($topTaman as $row)
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            @if ($row['url'])
                                <a href="{{ $row['url'] }}" class="text-rose-700 hover:underline">{{ $row['nama'] }}</a>
                            @else
                                {{ $row['nama'] }}
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $row['wilayah'] }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-700">{{ number_format($row['total']) }}</td>
                        <td class="px-4 py-3 text-right text-sm text-blue-700">{{ number_format($row['open']) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada aduan terkait taman.</td>
                    </tr>
                @endforelse
            </tbody>
        </x-admin.data-table>
    </div>

    <x-admin.data-table>
        <x-slot:header>
            <div class="border-b border-blue-100 bg-blue-50/50 px-6 py-4">
                <h3 class="text-base font-semibold text-blue-900">Backlog Aduan Aktif</h3>
                <p class="mt-1 text-sm text-blue-800/80">Semua aduan berstatus Baru, Ditinjau, atau Diproses (snapshot saat ini)</p>
            </div>
        </x-slot:header>
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Nomor</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Jenis</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Lokasi</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Status</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Diterima</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($openAduans as $aduan)
                <tr class="{{ $aduan->status === 'Baru' && $aduan->created_at->lte(now()->subDays($unreviewedDays)) ? 'bg-red-50/40' : '' }}">
                    <td class="px-4 py-3 text-sm font-medium">
                        <a href="{{ route('admin.aduan-masyarakats.show', $aduan) }}" class="text-rose-700 hover:underline">
                            {{ $aduan->nomor_aduan }}
                        </a>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $aduan->jenis_aduan }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $aduan->taman?->nama_taman ?? $aduan->lokasi }}</td>
                    <td class="px-4 py-3 text-sm">
                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ \App\Models\AduanMasyarakat::statusBadgeClass($aduan->status) }}">
                            {{ $aduan->status }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                        {{ $aduan->created_at->timezone(config('app.timezone'))->translatedFormat('d M Y') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-sm text-emerald-700">Tidak ada aduan aktif.</td>
                </tr>
            @endforelse
        </tbody>
    </x-admin.data-table>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const masukanCtx = document.getElementById('masukanChart');
            if (masukanCtx) {
                new Chart(masukanCtx, {
                    type: 'bar',
                    data: {
                        labels: @json($chartLabels),
                        datasets: [
                            {
                                label: 'Aduan',
                                data: @json($chartAduan),
                                backgroundColor: 'rgba(244, 63, 94, 0.75)',
                                borderColor: 'rgb(244, 63, 94)',
                                borderWidth: 1,
                            },
                            {
                                label: 'Survey',
                                data: @json($chartSurvey),
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
            }

            const ratingCtx = document.getElementById('ratingChart');
            if (ratingCtx) {
                new Chart(ratingCtx, {
                    type: 'bar',
                    data: {
                        labels: ['1 ★', '2 ★', '3 ★', '4 ★', '5 ★'],
                        datasets: [{
                            label: 'Jumlah Survey',
                            data: @json(array_values($surveyDistribution)),
                            backgroundColor: [
                                'rgba(239, 68, 68, 0.75)',
                                'rgba(249, 115, 22, 0.75)',
                                'rgba(234, 179, 8, 0.75)',
                                'rgba(34, 197, 94, 0.75)',
                                'rgba(16, 185, 129, 0.75)',
                            ],
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: { beginAtZero: true, ticks: { precision: 0 } },
                        },
                    },
                });
            }
        });
    </script>
@endpush
