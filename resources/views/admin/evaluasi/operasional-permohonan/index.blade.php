@extends('layouts.admin')

@section('title', 'Evaluasi Operasional Permohonan')
@section('header', 'Evaluasi Operasional Permohonan')

@section('content')
    @include('admin.evaluasi._period-filter', [
        'action' => route('admin.evaluasi.operasional-permohonan.index'),
        'exportRoute' => route('admin.evaluasi.operasional-permohonan.export-pdf', request()->query()),
        'mode' => $mode,
        'bulan' => $bulan,
        'tahun' => $tahun,
        'dari' => $dari,
        'sampai' => $sampai,
        'daftarBulan' => $daftarBulan,
        'daftarTahun' => $daftarTahun,
        'accent' => 'orange',
        'title' => 'Filter Evaluasi Permohonan',
        'description' => 'Analisis SLA, status penyelesaian, dan kelengkapan dokumentasi permohonan operasional',
        'filters' => view('admin.evaluasi.operasional-permohonan._filters', [
            'jenisLayanan' => $jenisLayanan,
            'status' => $status,
            'pelaksana' => $pelaksana,
            'daftarJenisLayanan' => $daftarJenisLayanan,
            'daftarStatus' => $daftarStatus,
            'daftarPelaksana' => $daftarPelaksana,
        ]),
    ])

    <p class="mb-4 text-sm text-gray-600">
        Periode evaluasi: <strong class="text-orange-800">{{ $labelPeriode }}</strong>
    </p>

    <div class="mb-8 grid grid-cols-2 gap-4 lg:grid-cols-6">
        <div class="rounded-2xl border border-orange-100 bg-gradient-to-br from-orange-50 to-amber-50 p-5">
            <p class="text-sm font-medium text-orange-700">Total Permohonan</p>
            <p class="mt-2 text-3xl font-bold text-orange-900">{{ number_format($totalPermohonan) }}</p>
        </div>
        <div class="rounded-2xl border border-green-100 bg-gradient-to-br from-green-50 to-emerald-50 p-5">
            <p class="text-sm font-medium text-green-700">Selesai</p>
            <p class="mt-2 text-3xl font-bold text-green-900">{{ number_format($totalSelesai) }}</p>
            <p class="mt-1 text-xs text-green-700/80">{{ $persenSelesai }}% dari total</p>
        </div>
        <div class="rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50 to-indigo-50 p-5">
            <p class="text-sm font-medium text-blue-700">Diproses</p>
            <p class="mt-2 text-3xl font-bold text-blue-900">{{ number_format($totalDiproses) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-600">Rencana</p>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($totalRencana) }}</p>
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
            <p class="mt-1 text-xs text-violet-700/80">Permohonan → selesai</p>
        </div>
        <div class="rounded-2xl border border-amber-100 bg-gradient-to-br from-amber-50 to-yellow-50 p-5">
            <p class="text-sm font-medium text-amber-700">Tepat Waktu</p>
            <p class="mt-2 text-3xl font-bold text-amber-900">
                @if ($persenTepatWaktu !== null)
                    {{ $persenTepatWaktu }}%
                @else
                    —
                @endif
            </p>
            <p class="mt-1 text-xs text-amber-700/80">Dokumentasi lengkap: {{ $persenDokumentasiLengkap }}%</p>
        </div>
    </div>

    <div class="mb-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <h3 class="mb-4 text-base font-semibold text-gray-800">Grafik Permohonan Aktif Harian per Jenis</h3>
        <div class="h-72">
            <canvas id="permohonanChart"></canvas>
        </div>
    </div>

    <div class="mb-8">
        <x-admin.data-table>
            <x-slot:header>
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-800">Rekap per Jenis Layanan</h3>
                </div>
            </x-slot:header>
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Jenis Layanan</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Total</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Rencana</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Diproses</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Selesai</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Rata. Hari</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Tepat Waktu</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Dokumentasi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($rekapPerJenis as $row)
                    <tr class="{{ $row['total'] === 0 ? 'bg-gray-50/80' : '' }}">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            <span class="mr-1">{{ \App\Models\Pemangkasan::layananIcon($row['jenis']) }}</span>
                            {{ $row['jenis'] }}
                        </td>
                        <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">{{ number_format($row['total']) }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-700">{{ number_format($row['rencana']) }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-700">{{ number_format($row['diproses']) }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-700">{{ number_format($row['selesai']) }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-700">
                            {{ $row['rata_hari'] !== null ? number_format($row['rata_hari'], 1).' hari' : '—' }}
                        </td>
                        <td class="px-4 py-3 text-right text-sm">
                            @if ($row['persen_tepat_waktu'] !== null)
                                <span class="{{ $row['persen_tepat_waktu'] >= 80 ? 'font-semibold text-green-700' : ($row['persen_tepat_waktu'] >= 60 ? 'font-semibold text-amber-700' : 'font-semibold text-red-700') }}">
                                    {{ $row['persen_tepat_waktu'] }}%
                                </span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right text-sm text-gray-700">{{ $row['persen_dokumentasi'] }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada data permohonan.</td>
                    </tr>
                @endforelse
            </tbody>
        </x-admin.data-table>
    </div>

    <x-admin.data-table>
        <x-slot:header>
            <div class="border-b border-gray-100 px-6 py-4">
                <h3 class="text-base font-semibold text-gray-800">Detail Permohonan</h3>
                <p class="mt-1 text-xs text-gray-500">SLA diukur dari tanggal permohonan hingga penyelesaian; dokumentasi dari foto permohonan dan progres harian</p>
            </div>
        </x-slot:header>
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Lokasi</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Jenis</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Status</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Pelaksana</th>
                <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Durasi</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">SLA</th>
                <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Dok.</th>
                <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Progres</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($detailRows as $row)
                <tr>
                    <td class="px-4 py-3 text-sm">
                        <a href="{{ $row['url'] }}" class="font-medium text-orange-700 hover:underline">{{ Str::limit($row['lokasi'], 40) }}</a>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $row['jenis'] }}</td>
                    <td class="px-4 py-3 text-sm">
                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium
                            @if ($row['status'] === 'Selesai') bg-green-100 text-green-800
                            @elseif ($row['status'] === 'Diproses') bg-blue-100 text-blue-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ $row['status'] }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ Str::limit($row['pelaksana'], 24) }}</td>
                    <td class="px-4 py-3 text-right text-sm text-gray-700">
                        {{ $row['durasi_hari'] !== null ? number_format($row['durasi_hari']).' hari' : '—' }}
                    </td>
                    <td class="px-4 py-3 text-sm">
                        @if ($row['sla_label'] === 'Tepat waktu')
                            <span class="font-medium text-green-700">{{ $row['sla_label'] }}</span>
                        @elseif ($row['sla_label'] === 'Terlambat')
                            <span class="font-medium text-red-700">{{ $row['sla_label'] }}</span>
                        @else
                            <span class="text-gray-400">{{ $row['sla_label'] }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right text-sm text-gray-700">{{ $row['dokumentasi_percent'] }}%</td>
                    <td class="px-4 py-3 text-right text-sm text-gray-700">{{ number_format($row['jumlah_progres']) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada permohonan pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </x-admin.data-table>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('permohonanChart');
            if (!ctx) return;

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json($chartLabels),
                    datasets: @json($chartDatasets),
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
