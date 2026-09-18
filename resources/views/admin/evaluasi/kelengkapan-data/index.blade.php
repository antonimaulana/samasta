@extends('layouts.admin')

@section('title', 'Evaluasi Kelengkapan & Kemutakhiran Data')
@section('header', 'Evaluasi Kelengkapan & Kemutakhiran Data')

@section('content')
    <div class="mb-6 overflow-hidden rounded-2xl border border-indigo-100 bg-white shadow-sm">
        <div class="border-b border-indigo-50 bg-gradient-to-r from-indigo-50 to-violet-50 px-6 py-4">
            <h2 class="text-lg font-semibold text-indigo-800">Filter Evaluasi</h2>
            <p class="mt-1 text-sm text-indigo-700/80">Indikator kualitas basis data RTH: kelengkapan profil taman dan kemutakhiran verifikasi data</p>
        </div>

        <form action="{{ route('admin.evaluasi.kelengkapan-data.index') }}" method="GET" class="space-y-4 p-6">
            @include('admin.evaluasi.kelengkapan-data._filters')

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Tampilkan Evaluasi
                </button>
                <a href="{{ route('admin.evaluasi.kelengkapan-data.export-pdf', request()->query()) }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-indigo-300 bg-white px-5 py-2 text-sm font-semibold text-indigo-800 hover:bg-indigo-50">
                    📄 Export PDF
                </a>
            </div>
        </form>
    </div>

    <p class="mb-1 text-sm text-gray-600">{{ $labelSnapshot }}</p>
    <p class="mb-4 text-sm text-indigo-700">
        <strong>{{ $labelKriteria }}</strong>
        (batas: {{ $cutoffDate->timezone(config('app.timezone'))->translatedFormat('d F Y') }})
    </p>

    <div class="mb-8 grid grid-cols-2 gap-4 lg:grid-cols-6">
        <div class="rounded-2xl border border-indigo-100 bg-gradient-to-br from-indigo-50 to-violet-50 p-5">
            <p class="text-sm font-medium text-indigo-700">Total Lokasi</p>
            <p class="mt-2 text-3xl font-bold text-indigo-900">{{ number_format($totalLokasi) }}</p>
            <p class="mt-1 text-xs text-indigo-700/80">Rata. skor {{ $rataScore }}%</p>
        </div>
        <div class="rounded-2xl border border-green-100 bg-gradient-to-br from-green-50 to-emerald-50 p-5">
            <p class="text-sm font-medium text-green-700">Data Lengkap</p>
            <p class="mt-2 text-3xl font-bold text-green-900">
                {{ number_format($lokasiLengkap) }}
                <span class="text-lg font-semibold text-green-700">/ {{ number_format($totalLokasi) }}</span>
            </p>
            <p class="mt-1 text-sm font-semibold text-emerald-700">{{ $persenLengkap }}%</p>
        </div>
        <div class="rounded-2xl border border-amber-100 bg-gradient-to-br from-amber-50 to-orange-50 p-5">
            <p class="text-sm font-medium text-amber-700">Belum Lengkap</p>
            <p class="mt-2 text-3xl font-bold text-amber-900">{{ number_format($lokasiBelumLengkap) }}</p>
            <p class="mt-1 text-xs text-amber-700/80">{{ number_format($totalLuasan - $luasanLengkap, 0, ',', '.') }} m² terdampak</p>
        </div>
        <div class="rounded-2xl border border-teal-100 bg-gradient-to-br from-teal-50 to-cyan-50 p-5">
            <p class="text-sm font-medium text-teal-700">Data Mutakhir</p>
            <p class="mt-2 text-3xl font-bold text-teal-900">
                {{ number_format($lokasiMutakhir) }}
                <span class="text-lg font-semibold text-teal-700">/ {{ number_format($totalLokasi) }}</span>
            </p>
            <p class="mt-1 text-sm font-semibold text-teal-700">{{ $persenMutakhir }}%</p>
        </div>
        <div class="rounded-2xl border border-red-100 bg-gradient-to-br from-red-50 to-rose-50 p-5">
            <p class="text-sm font-medium text-red-700">Kedaluwarsa</p>
            <p class="mt-2 text-3xl font-bold text-red-900">{{ number_format($lokasiKedaluwarsa) }}</p>
            <p class="mt-1 text-xs text-red-700/80">verifikasi &gt; {{ $freshDays }} hari</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-600">Belum Diverifikasi</p>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($lokasiBelumVerifikasi) }}</p>
            <p class="mt-1 text-xs text-gray-500">tanpa data_verified_at</p>
        </div>
    </div>

    <div class="mb-8 grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-base font-semibold text-gray-800">Kelengkapan per Kategori RTH</h3>
            <div class="h-72">
                <canvas id="kelengkapanChart"></canvas>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-base font-semibold text-gray-800">Field Paling Sering Kosong</h3>
            <div class="h-72">
                <canvas id="fieldGapChart"></canvas>
            </div>
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
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Lengkap</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Mutakhir</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Skor</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($rekapPerKategori as $row)
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $row['label'] }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-700">{{ number_format($row['total_lokasi']) }}</td>
                        <td class="px-4 py-3 text-right text-sm text-green-700">{{ $row['persen_lengkap'] }}%</td>
                        <td class="px-4 py-3 text-right text-sm text-teal-700">{{ $row['persen_mutakhir'] }}%</td>
                        <td class="px-4 py-3 text-right text-sm font-semibold text-indigo-700">{{ $row['rata_score'] }}%</td>
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
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Lengkap</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Mutakhir</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Skor</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($rekapPerKecamatan as $row)
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $row['label'] }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-700">{{ number_format($row['total_lokasi']) }}</td>
                        <td class="px-4 py-3 text-right text-sm text-green-700">{{ $row['persen_lengkap'] }}%</td>
                        <td class="px-4 py-3 text-right text-sm text-teal-700">{{ $row['persen_mutakhir'] }}%</td>
                        <td class="px-4 py-3 text-right text-sm font-semibold text-indigo-700">{{ $row['rata_score'] }}%</td>
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
                <p class="mt-1 text-sm text-amber-800/80">Profil belum lengkap atau verifikasi data kedaluwarsa</p>
            </div>
        </x-slot:header>
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Taman</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Kategori</th>
                <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Skor</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Field Kosong</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Verifikasi Terakhir</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($perluPerhatian as $row)
                <tr class="{{ ! $row['is_lengkap'] ? 'bg-amber-50/30' : '' }}">
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">
                        <a href="{{ $row['taman_url'] }}" class="text-indigo-700 hover:underline">{{ $row['nama'] }}</a>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $row['kategori'] }}</td>
                    <td class="px-4 py-3 text-right text-sm font-semibold {{ $row['is_lengkap'] ? 'text-green-700' : 'text-amber-700' }}">
                        {{ $row['score'] }}%
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                        {{ implode(', ', array_slice($row['missing_labels'], 0, 3)) }}
                        @if (count($row['missing_labels']) > 3)
                            <span class="text-gray-400">+{{ count($row['missing_labels']) - 3 }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm">
                        @if ($row['verified_at'])
                            <a href="{{ $row['edit_url'] }}" class="{{ $row['is_mutakhir'] ? 'text-teal-700' : 'text-red-700' }} hover:underline">
                                {{ $row['verified_at']->timezone(config('app.timezone'))->translatedFormat('d M Y') }}
                                ({{ $row['hari_sejak_verifikasi'] }} hari lalu)
                            </a>
                        @else
                            <a href="{{ $row['edit_url'] }}" class="font-medium text-red-600 hover:underline">Belum diverifikasi</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-sm text-emerald-700">Semua profil lengkap dan mutakhir.</td>
                </tr>
            @endforelse
        </tbody>
    </x-admin.data-table>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const kelengkapanCtx = document.getElementById('kelengkapanChart');
            if (kelengkapanCtx) {
                new Chart(kelengkapanCtx, {
                    type: 'bar',
                    data: {
                        labels: @json($chartLabels),
                        datasets: [
                            {
                                label: 'Lengkap',
                                data: @json($chartLengkap),
                                backgroundColor: 'rgba(99, 102, 241, 0.75)',
                                borderColor: 'rgb(99, 102, 241)',
                                borderWidth: 1,
                            },
                            {
                                label: 'Belum Lengkap',
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
            }

            const fieldGapCtx = document.getElementById('fieldGapChart');
            if (fieldGapCtx) {
                new Chart(fieldGapCtx, {
                    type: 'bar',
                    data: {
                        labels: @json($fieldGaps->pluck('label')),
                        datasets: [{
                            label: 'Lokasi kosong',
                            data: @json($fieldGaps->pluck('missing')),
                            backgroundColor: 'rgba(239, 68, 68, 0.75)',
                        }],
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: { beginAtZero: true, ticks: { precision: 0 } },
                        },
                    },
                });
            }
        });
    </script>
@endpush
