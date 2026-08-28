@extends('layouts.admin')

@section('title', 'Laporan Bibit')
@section('header', 'Laporan Bibit')

@section('content')
    <div class="mb-6 overflow-hidden rounded-2xl border border-teal-100 bg-white shadow-sm">
        <div class="border-b border-teal-50 bg-gradient-to-r from-teal-50 to-cyan-50 px-6 py-4">
            <h2 class="text-lg font-semibold text-teal-800">Filter Periode</h2>
            <p class="mt-1 text-sm text-teal-700/80">Laporan mutasi stok masuk dan keluar bibit</p>
        </div>

        <form action="{{ route('admin.bibit-laporan.index') }}" method="GET" class="space-y-4 p-6">
            <div class="flex flex-wrap gap-4">
                <label class="flex cursor-pointer items-center gap-2">
                    <input type="radio" name="mode" value="bulan" @checked($mode === 'bulan')
                           class="text-teal-600 focus:ring-teal-500" onchange="this.form.submit()">
                    <span class="text-sm font-medium text-gray-700">Per Bulan</span>
                </label>
                <label class="flex cursor-pointer items-center gap-2">
                    <input type="radio" name="mode" value="periode" @checked($mode === 'periode')
                           class="text-teal-600 focus:ring-teal-500" onchange="togglePeriodeFields()">
                    <span class="text-sm font-medium text-gray-700">Rentang Tanggal</span>
                </label>
            </div>

            <div id="filter-bulan" class="grid gap-4 sm:grid-cols-3 {{ $mode === 'periode' ? 'hidden' : '' }}">
                <div>
                    <label for="bulan" class="mb-1 block text-sm font-medium text-gray-700">Bulan</label>
                    <select name="bulan" id="bulan"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                        @foreach ($daftarBulan as $num => $nama)
                            <option value="{{ $num }}" @selected($bulan == $num)>{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="tahun" class="mb-1 block text-sm font-medium text-gray-700">Tahun</label>
                    <select name="tahun" id="tahun"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                        @foreach ($daftarTahun as $thn)
                            <option value="{{ $thn }}" @selected($tahun == $thn)>{{ $thn }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div id="filter-periode" class="grid gap-4 sm:grid-cols-2 {{ $mode === 'bulan' ? 'hidden' : '' }}">
                <div>
                    <label for="dari" class="mb-1 block text-sm font-medium text-gray-700">Dari Tanggal</label>
                    <input type="date" name="dari" id="dari" value="{{ $dari }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                </div>
                <div>
                    <label for="sampai" class="mb-1 block text-sm font-medium text-gray-700">Sampai Tanggal</label>
                    <input type="date" name="sampai" id="sampai" value="{{ $sampai }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                </div>
            </div>

            <div class="max-w-md">
                <label for="search" class="mb-1 block text-sm font-medium text-gray-700">Cari di Tabel</label>
                <input type="search" name="search" id="search" value="{{ request('search') }}"
                       placeholder="Cari nama tanaman, jenis, sumber, peruntukan, lokasi..."
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit"
                        class="rounded-lg bg-teal-600 px-5 py-2 text-sm font-medium text-white hover:bg-teal-700">
                    Tampilkan Laporan
                </button>
                <a href="{{ route('admin.bibit-laporan.export-pdf', request()->query()) }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-teal-300 bg-white px-5 py-2 text-sm font-semibold text-teal-800 hover:bg-teal-50">
                    📄 Export PDF
                </a>
            </div>
        </form>
    </div>

    <p class="mb-4 text-sm text-gray-600">
        Periode laporan: <strong class="text-teal-800">{{ $labelPeriode }}</strong>
    </p>

    <div class="mb-8 grid grid-cols-2 gap-4 md:grid-cols-4">
        <div class="rounded-2xl border border-teal-100 bg-gradient-to-br from-teal-50 to-cyan-50 p-5">
            <p class="text-sm font-medium text-teal-700">Total Stok Masuk</p>
            <p class="mt-2 text-3xl font-bold text-teal-900">+{{ number_format($totalMasuk) }}</p>
        </div>
        <div class="rounded-2xl border border-red-100 bg-gradient-to-br from-red-50 to-orange-50 p-5">
            <p class="text-sm font-medium text-red-700">Total Stok Keluar</p>
            <p class="mt-2 text-3xl font-bold text-red-900">-{{ number_format($totalKeluar) }}</p>
        </div>
        <div class="rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50 to-indigo-50 p-5">
            <p class="text-sm font-medium text-blue-700">Mutasi Bersih</p>
            <p class="mt-2 text-3xl font-bold {{ $mutasiBersih >= 0 ? 'text-blue-900' : 'text-red-800' }}">
                {{ $mutasiBersih >= 0 ? '+' : '' }}{{ number_format($mutasiBersih) }}
            </p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-600">Jumlah Transaksi</p>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($jumlahTransaksi) }}</p>
        </div>
    </div>

    <div class="mb-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <h3 class="mb-4 text-base font-semibold text-gray-800">Grafik Mutasi Harian</h3>
        <div class="h-72">
            <canvas id="mutasiChart"></canvas>
        </div>
    </div>

    <x-admin.data-table class="mb-8">
        <x-slot:header>
            <div class="border-b border-gray-100 px-6 py-4">
                <h3 class="text-base font-semibold text-gray-800">Mutasi per Jenis Tanaman</h3>
            </div>
        </x-slot:header>
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Nama Tanaman</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Jenis</th>
                <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Masuk</th>
                <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Keluar</th>
                <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Mutasi</th>
                <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Stok Sekarang</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($mutasiPerBibit as $row)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm">
                        <x-admin.bibit-nama :bibit="$row['bibit']" />
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $row['bibit']->jenis }}</td>
                    <td class="px-4 py-3 text-right text-sm font-medium text-teal-700">+{{ number_format($row['masuk']) }}</td>
                    <td class="px-4 py-3 text-right text-sm font-medium text-red-700">-{{ number_format($row['keluar']) }}</td>
                    <td class="px-4 py-3 text-right text-sm font-semibold {{ $row['mutasi'] >= 0 ? 'text-blue-700' : 'text-red-700' }}">
                        {{ $row['mutasi'] >= 0 ? '+' : '' }}{{ number_format($row['mutasi']) }}
                    </td>
                    <td class="px-4 py-3 text-right text-sm text-gray-700">{{ number_format($row['stok_sekarang']) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                        Tidak ada mutasi stok pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-admin.data-table>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-teal-50 bg-teal-50 px-4 py-3">
                <h3 class="text-sm font-semibold text-teal-800">Riwayat Masuk Terbaru</h3>
            </div>
            <ul class="divide-y divide-gray-100">
                @forelse ($riwayatMasuk as $masuk)
                    <li class="flex items-center justify-between px-4 py-3 text-sm">
                        <div>
                            <x-admin.bibit-nama :bibit="$masuk->bibit" />
                            <p class="text-xs text-gray-500">{{ $masuk->tanggal_masuk->format('d M Y') }} · {{ $masuk->sumber }}</p>
                        </div>
                        <span class="font-semibold text-teal-700">+{{ $masuk->jumlah }}</span>
                    </li>
                @empty
                    <li class="px-4 py-6 text-center text-sm text-gray-500">Tidak ada data masuk.</li>
                @endforelse
            </ul>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-red-50 bg-red-50 px-4 py-3">
                <h3 class="text-sm font-semibold text-red-800">Riwayat Keluar Terbaru</h3>
            </div>
            <ul class="divide-y divide-gray-100">
                @forelse ($riwayatKeluar as $keluar)
                    <li class="flex items-center justify-between px-4 py-3 text-sm">
                        <div>
                            <x-admin.bibit-nama :bibit="$keluar->bibit" />
                            <p class="text-xs text-gray-500">{{ $keluar->tanggal_keluar->format('d M Y') }} · {{ $keluar->peruntukan }}@if ($keluar->taman) · {{ $keluar->taman->nama_taman }}@endif</p>
                        </div>
                        <span class="font-semibold text-red-700">-{{ $keluar->jumlah }}</span>
                    </li>
                @empty
                    <li class="px-4 py-6 text-center text-sm text-gray-500">Tidak ada data keluar.</li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function togglePeriodeFields() {
            const mode = document.querySelector('input[name="mode"]:checked')?.value;
            document.getElementById('filter-bulan').classList.toggle('hidden', mode === 'periode');
            document.getElementById('filter-periode').classList.toggle('hidden', mode === 'bulan');
        }

        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('mutasiChart');
            if (!ctx) return;

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json($chartLabels),
                    datasets: [
                        {
                            label: 'Stok Masuk',
                            data: @json($chartMasuk),
                            backgroundColor: 'rgba(20, 184, 166, 0.7)',
                            borderColor: 'rgb(20, 184, 166)',
                            borderWidth: 1,
                        },
                        {
                            label: 'Stok Keluar',
                            data: @json($chartKeluar),
                            backgroundColor: 'rgba(239, 68, 68, 0.7)',
                            borderColor: 'rgb(239, 68, 68)',
                            borderWidth: 1,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 },
                        },
                    },
                    plugins: {
                        legend: { position: 'top' },
                    },
                },
            });
        });
    </script>
@endpush
