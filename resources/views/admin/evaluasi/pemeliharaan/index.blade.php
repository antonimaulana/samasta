@extends('layouts.admin')

@section('title', 'Evaluasi Pemeliharaan per Taman')
@section('header', 'Evaluasi Pemeliharaan per Taman')

@section('content')
    <div class="mb-6 overflow-hidden rounded-2xl border border-green-100 bg-white shadow-sm">
        <div class="border-b border-green-50 bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-4">
            <h2 class="text-lg font-semibold text-green-800">Filter Evaluasi</h2>
            <p class="mt-1 text-sm text-green-700/80">Rekapitulasi frekuensi pemeliharaan rutin per taman untuk pengambilan keputusan</p>
        </div>

        <form action="{{ route('admin.evaluasi.pemeliharaan.index') }}" method="GET" class="space-y-4 p-6">
            @include('admin.evaluasi.pemeliharaan._filters')

            <div class="flex flex-wrap gap-4">
                <label class="flex cursor-pointer items-center gap-2">
                    <input type="radio" name="mode" value="bulan" @checked($mode === 'bulan')
                           class="text-green-600 focus:ring-green-500" onchange="this.form.submit()">
                    <span class="text-sm font-medium text-gray-700">Per Bulan</span>
                </label>
                <label class="flex cursor-pointer items-center gap-2">
                    <input type="radio" name="mode" value="periode" @checked($mode === 'periode')
                           class="text-green-600 focus:ring-green-500" onchange="toggleEvaluasiPeriodeFields()">
                    <span class="text-sm font-medium text-gray-700">Rentang Tanggal</span>
                </label>
            </div>

            <div id="evaluasi-filter-bulan" class="grid gap-4 sm:grid-cols-3 {{ $mode === 'periode' ? 'hidden' : '' }}">
                <div>
                    <label for="bulan" class="mb-1 block text-sm font-medium text-gray-700">Bulan</label>
                    <select name="bulan" id="bulan" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        @foreach ($daftarBulan as $num => $nama)
                            <option value="{{ $num }}" @selected($bulan == $num)>{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="tahun" class="mb-1 block text-sm font-medium text-gray-700">Tahun</label>
                    <select name="tahun" id="tahun" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        @foreach ($daftarTahun as $thn)
                            <option value="{{ $thn }}" @selected($tahun == $thn)>{{ $thn }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div id="evaluasi-filter-periode" class="grid gap-4 sm:grid-cols-2 {{ $mode === 'bulan' ? 'hidden' : '' }}">
                <div>
                    <label for="dari" class="mb-1 block text-sm font-medium text-gray-700">Dari Tanggal</label>
                    <input type="date" name="dari" id="dari" value="{{ $dari }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label for="sampai" class="mb-1 block text-sm font-medium text-gray-700">Sampai Tanggal</label>
                    <input type="date" name="sampai" id="sampai" value="{{ $sampai }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="rounded-lg bg-green-600 px-5 py-2 text-sm font-medium text-white hover:bg-green-700">
                    Tampilkan Evaluasi
                </button>
                <a href="{{ route('admin.evaluasi.pemeliharaan.export-pdf', request()->query()) }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-green-300 bg-white px-5 py-2 text-sm font-semibold text-green-800 hover:bg-green-50">
                    📄 Export PDF
                </a>
            </div>
        </form>
    </div>

    <p class="mb-4 text-sm text-gray-600">
        Periode evaluasi: <strong class="text-green-800">{{ $labelPeriode }}</strong>
    </p>

    <div class="mb-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-2xl border border-green-100 bg-gradient-to-br from-green-50 to-emerald-50 p-5">
            <p class="text-sm font-medium text-green-700">Total Kegiatan</p>
            <p class="mt-2 text-3xl font-bold text-green-900">{{ number_format($totalKegiatan) }}</p>
        </div>
        <div class="rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50 to-indigo-50 p-5">
            <p class="text-sm font-medium text-blue-700">Taman Terlayani</p>
            <p class="mt-2 text-3xl font-bold text-blue-900">{{ number_format($totalTamanTerlayani) }}<span class="text-lg font-semibold text-blue-700"> / {{ number_format($totalTamanInScope) }}</span></p>
        </div>
        <div class="rounded-2xl border border-amber-100 bg-gradient-to-br from-amber-50 to-orange-50 p-5">
            <p class="text-sm font-medium text-amber-700">Cakupan Pemeliharaan</p>
            <p class="mt-2 text-3xl font-bold text-amber-900">{{ $coveragePercent }}%</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-600">Total Personil</p>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($totalPersonil) }}</p>
        </div>
    </div>

    <div class="mb-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <h3 class="mb-4 text-base font-semibold text-gray-800">Grafik Kegiatan Pemeliharaan Harian</h3>
        <div class="h-72">
            <canvas id="pemeliharaanChart"></canvas>
        </div>
    </div>

    <div class="mb-8 grid gap-6 lg:grid-cols-2">
        <x-admin.data-table>
            <x-slot:header>
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-800">Rekap per Tim</h3>
                </div>
            </x-slot:header>
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Tim</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Kegiatan</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Personil</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Taman</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($ringkasanTim as $row)
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $row['tim'] }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-700">{{ number_format($row['jumlah']) }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-700">{{ number_format($row['personil']) }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-700">{{ number_format($row['taman']) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada kegiatan pemeliharaan pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </x-admin.data-table>

        <x-admin.data-table>
            <x-slot:header>
                <div class="border-b border-red-50 bg-red-50 px-6 py-4">
                    <h3 class="text-base font-semibold text-red-800">Taman Belum Dipelihara</h3>
                    <p class="mt-1 text-xs text-red-700">Taman terdaftar tanpa kegiatan pemeliharaan pada periode ini</p>
                </div>
            </x-slot:header>
            <tbody class="divide-y divide-gray-100">
                @forelse ($tamanBelumDipelihara as $taman)
                    <tr>
                        <td class="px-4 py-3">
                            <p class="text-sm font-medium text-gray-900">{{ $taman->nama_taman }}</p>
                            <p class="text-xs text-gray-500">{{ $taman->kategori }} · {{ $taman->kelurahan?->nama ?? '—' }}</p>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-8 text-center text-sm text-gray-500">Semua taman dalam cakupan sudah dipelihara pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </x-admin.data-table>
    </div>

    <x-admin.data-table>
        <x-slot:header>
            <div class="border-b border-gray-100 px-6 py-4">
                <h3 class="text-base font-semibold text-gray-800">Rekapitulasi Pemeliharaan per Taman / Lokasi</h3>
            </div>
        </x-slot:header>
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Taman / Lokasi</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Kategori</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Wilayah</th>
                <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Kegiatan</th>
                <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Personil</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Tim</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Terakhir</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($rekapPerTaman as $row)
                <tr>
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">
                        @if ($row['url'])
                            <a href="{{ $row['url'] }}" class="text-green-700 hover:underline">{{ $row['label'] }}</a>
                        @else
                            {{ $row['label'] }}
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $row['kategori'] }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $row['wilayah'] }}</td>
                    <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">{{ number_format($row['jumlah_kegiatan']) }}</td>
                    <td class="px-4 py-3 text-right text-sm text-gray-700">{{ number_format($row['total_personil']) }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ implode(', ', $row['tim_terlibat']) }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ \App\Support\OperasionalPelaksanaanTime::display($row['tanggal_terakhir']) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada data pemeliharaan pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </x-admin.data-table>
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
            const ctx = document.getElementById('pemeliharaanChart');
            if (!ctx) return;

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json($chartLabels),
                    datasets: [{
                        label: 'Kegiatan Pemeliharaan',
                        data: @json($chartData),
                        backgroundColor: 'rgba(22, 163, 74, 0.7)',
                        borderColor: 'rgb(22, 163, 74)',
                        borderWidth: 1,
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
        });
    </script>
@endpush
