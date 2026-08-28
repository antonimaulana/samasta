@extends('layouts.admin')

@section('title', 'Laporan Operasional Pertamanan')
@section('header', 'Laporan Operasional Pertamanan')

@section('content')
    <div class="mb-6 overflow-hidden rounded-2xl border border-amber-100 bg-white shadow-sm">
        <div class="border-b border-amber-50 bg-gradient-to-r from-amber-50 to-orange-50 px-6 py-4">
            <h2 class="text-lg font-semibold text-amber-900">Filter Periode</h2>
            <p class="mt-1 text-sm text-amber-800/80">Laporan pelaksanaan operasional pemangkasan dan penanganan pohon tumbang</p>
        </div>

        <form action="{{ route('admin.operasional-pertamanan-laporan.index') }}" method="GET" class="space-y-4 p-6">
            <div class="flex flex-wrap gap-4">
                <label class="flex cursor-pointer items-center gap-2">
                    <input type="radio" name="mode" value="bulan" @checked($mode === 'bulan')
                           class="text-amber-600 focus:ring-amber-500" onchange="this.form.submit()">
                    <span class="text-sm font-medium text-gray-700">Per Bulan</span>
                </label>
                <label class="flex cursor-pointer items-center gap-2">
                    <input type="radio" name="mode" value="periode" @checked($mode === 'periode')
                           class="text-amber-600 focus:ring-amber-500" onchange="togglePeriodeFields()">
                    <span class="text-sm font-medium text-gray-700">Rentang Tanggal</span>
                </label>
            </div>

            <div id="filter-bulan" class="grid gap-4 sm:grid-cols-3 {{ $mode === 'periode' ? 'hidden' : '' }}">
                <div>
                    <label for="bulan" class="mb-1 block text-sm font-medium text-gray-700">Bulan</label>
                    <select name="bulan" id="bulan"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                        @foreach ($daftarBulan as $num => $nama)
                            <option value="{{ $num }}" @selected($bulan == $num)>{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="tahun" class="mb-1 block text-sm font-medium text-gray-700">Tahun</label>
                    <select name="tahun" id="tahun"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
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
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                </div>
                <div>
                    <label for="sampai" class="mb-1 block text-sm font-medium text-gray-700">Sampai Tanggal</label>
                    <input type="date" name="sampai" id="sampai" value="{{ $sampai }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                </div>
            </div>

            <div class="max-w-sm">
                <label for="jenis_layanan" class="mb-1 block text-sm font-medium text-gray-700">Jenis Operasional</label>
                <select name="jenis_layanan" id="jenis_layanan"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                    <option value="" @selected($jenisLayanan === '')>Semua Jenis</option>
                    @foreach (\App\Models\Pemangkasan::JENIS_LAYANAN as $jenis)
                        <option value="{{ $jenis }}" @selected($jenisLayanan === $jenis)>{{ $jenis }}</option>
                    @endforeach
                </select>
            </div>

            <div class="max-w-md">
                <label for="search" class="mb-1 block text-sm font-medium text-gray-700">Cari di Tabel</label>
                <input type="search" name="search" id="search" value="{{ request('search') }}"
                       placeholder="Cari lokasi, pelaksana, jenis operasional, status..."
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit"
                        class="rounded-lg bg-amber-600 px-5 py-2 text-sm font-medium text-white hover:bg-amber-700">
                    Tampilkan Laporan
                </button>
                <a href="{{ route('admin.operasional-pertamanan-laporan.export-pdf', request()->query()) }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-amber-300 bg-white px-5 py-2 text-sm font-semibold text-amber-800 hover:bg-amber-50">
                    📄 Export PDF
                </a>
            </div>
        </form>
    </div>

    <p class="mb-4 text-sm text-gray-600">
        Periode laporan: <strong class="text-amber-900">{{ $labelPeriode }}</strong>
        <span class="text-gray-400">· berdasarkan tanggal pelaksanaan</span>
    </p>

    <div class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-amber-100 bg-gradient-to-br from-amber-50 to-yellow-50 p-5">
            <p class="text-sm font-medium text-amber-800">Total Operasional</p>
            <p class="mt-2 text-3xl font-bold text-amber-950">{{ number_format($totalLayanan) }}</p>
        </div>
        @foreach ($totalsPerJenis as $jenis => $total)
            @php
                $cardClass = match ($jenis) {
                    'Penanganan Pohon Tumbang' => 'border-orange-100 from-orange-50 to-amber-50 text-orange-800',
                    'Pemasangan Mini Garden' => 'border-violet-100 from-violet-50 to-purple-50 text-violet-800',
                    default => 'border-emerald-100 from-emerald-50 to-green-50 text-emerald-700',
                };
            @endphp
            <div class="rounded-2xl border bg-gradient-to-br p-5 {{ $cardClass }}">
                <p class="text-sm font-medium">{{ $jenis }}</p>
                <p class="mt-2 text-3xl font-bold">{{ number_format($total) }}</p>
            </div>
        @endforeach
    </div>

    <div class="mb-8 grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Rencana</p>
            <p class="mt-1 text-2xl font-bold text-blue-800">{{ number_format($totalRencana) }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Diproses</p>
            <p class="mt-1 text-2xl font-bold text-amber-700">{{ number_format($totalDiproses) }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Selesai</p>
            <p class="mt-1 text-2xl font-bold text-emerald-700">{{ number_format($totalSelesai) }}</p>
        </div>
    </div>

    <div class="mb-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <h3 class="mb-4 text-base font-semibold text-gray-800">Grafik Pelaksanaan Harian</h3>
        <div class="h-72">
            <canvas id="layananChart"></canvas>
        </div>
    </div>

    <x-admin.data-table class="mb-8">
        <x-slot:header>
            <div class="border-b border-gray-100 px-6 py-4">
                <h3 class="text-base font-semibold text-gray-800">Ringkasan per Jenis Operasional</h3>
            </div>
        </x-slot:header>
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Jenis Operasional</th>
                <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Rencana</th>
                <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Diproses</th>
                <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Selesai</th>
                <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Total</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($ringkasanPerJenis as $row)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm font-medium">{{ $row['jenis'] }}</td>
                    <td class="px-4 py-3 text-right text-sm text-blue-700">{{ number_format($row['rencana']) }}</td>
                    <td class="px-4 py-3 text-right text-sm text-amber-700">{{ number_format($row['diproses']) }}</td>
                    <td class="px-4 py-3 text-right text-sm text-emerald-700">{{ number_format($row['selesai']) }}</td>
                    <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">{{ number_format($row['total']) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                        Tidak ada data operasional pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-admin.data-table>

    <x-admin.data-table class="mb-8">
        <x-slot:header>
            <div class="border-b border-gray-100 px-6 py-4">
                <h3 class="text-base font-semibold text-gray-800">Daftar Operasional dalam Periode</h3>
            </div>
        </x-slot:header>
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Tgl. Pelaksanaan</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Jenis</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Lokasi</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Pelaksana</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($daftarLayanan as $layanan)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $layanan->tanggal_eksekusi->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-sm">
                        <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ \App\Models\Pemangkasan::badgeClass($layanan->jenis_layanan) }}">
                            {{ $layanan->jenis_layanan }}
                        </span>
                    </td>
                    <td class="max-w-xs truncate px-4 py-3 text-sm">{{ $layanan->lokasi_pohon }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $layanan->pelaksanaLabel() ?: '—' }}</td>
                    <td class="px-4 py-3 text-sm">
                        @php
                            $statusClass = match ($layanan->status) {
                                'Selesai' => 'bg-emerald-100 text-emerald-800',
                                'Diproses' => 'bg-amber-100 text-amber-800',
                                default => 'bg-blue-100 text-blue-800',
                            };
                        @endphp
                        <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $statusClass }}">{{ $layanan->status }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                        Tidak ada operasional pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-admin.data-table>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-emerald-50 bg-emerald-50 px-4 py-3">
            <h3 class="text-sm font-semibold text-emerald-800">Operasional Selesai Terbaru</h3>
        </div>
        <ul class="divide-y divide-gray-100">
            @forelse ($riwayatSelesai as $layanan)
                <li class="flex items-center justify-between px-4 py-3 text-sm">
                    <div>
                        <p class="font-medium">{{ $layanan->lokasi_pohon }}</p>
                        <p class="text-xs text-gray-500">
                            {{ $layanan->tanggal_eksekusi->format('d M Y') }} · {{ $layanan->jenis_layanan }} · {{ $layanan->pelaksanaLabel() }}
                        </p>
                    </div>
                    @if ($canWrite ?? auth()->user()?->canWrite())
                    <a href="{{ route('admin.pemangkasans.edit', $layanan) }}"
                       class="text-xs font-medium text-emerald-700 hover:underline">Detail</a>
                    @endif
                </li>
            @empty
                <li class="px-4 py-6 text-center text-sm text-gray-500">Belum ada operasional selesai pada periode ini.</li>
            @endforelse
        </ul>
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
            const ctx = document.getElementById('layananChart');
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
