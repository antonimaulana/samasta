@extends('layouts.admin')

@section('title', 'Laporan Taman')
@section('header', 'Laporan Taman')

@section('content')
    <div class="mb-6 overflow-hidden rounded-2xl border border-green-100 bg-white shadow-sm">
        <div class="border-b border-green-50 bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-4">
            <h2 class="text-lg font-semibold text-green-800">Filter Laporan</h2>
            <p class="mt-1 text-sm text-green-700/80">Rekap data RTH/taman berdasarkan kategori, wilayah, dan status data</p>
        </div>

        <form action="{{ route('admin.taman-laporan.index') }}" method="GET" class="space-y-4 p-6">
            <div class="grid gap-4 md:grid-cols-3">
                <div>
                    <label for="kategori" class="mb-1 block text-sm font-medium text-gray-700">Kategori</label>
                    <select name="kategori" id="kategori"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                        <option value="">Semua kategori</option>
                        @foreach (\App\Models\Taman::KATEGORI as $item)
                            <option value="{{ $item }}" @selected($kategori === $item)>{{ $item }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="status_data" class="mb-1 block text-sm font-medium text-gray-700">Status Data</label>
                    <select name="status_data" id="status_data"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                        <option value="">Semua status</option>
                        <option value="{{ \App\Models\Taman::STATUS_DATA_LENGKAP }}" @selected($statusData === \App\Models\Taman::STATUS_DATA_LENGKAP)>Lengkap</option>
                        <option value="{{ \App\Models\Taman::STATUS_DATA_BELUM_LENGKAP }}" @selected($statusData === \App\Models\Taman::STATUS_DATA_BELUM_LENGKAP)>Belum Lengkap</option>
                    </select>
                </div>

                <div>
                    <label for="search" class="mb-1 block text-sm font-medium text-gray-700">Cari</label>
                    <input type="search" name="search" id="search" value="{{ $search }}"
                           placeholder="Nama, alamat, kontraktor..."
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit"
                        class="rounded-lg bg-green-600 px-5 py-2 text-sm font-medium text-white hover:bg-green-700">
                    Tampilkan Laporan
                </button>
                <a href="{{ route('admin.taman-laporan.export-pdf', request()->query()) }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-green-300 bg-white px-5 py-2 text-sm font-semibold text-green-800 hover:bg-green-50">
                    Export PDF
                </a>
                <a href="{{ route('admin.taman-laporan.index') }}"
                   class="rounded-lg border border-gray-300 px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <x-admin.collapsible-card
        title="Ringkasan"
        description="Total taman, luasan, dan status data"
        :open="true">
        <div class="grid grid-cols-2 gap-4 p-5 lg:grid-cols-4">
            <div class="rounded-2xl border border-green-100 bg-gradient-to-br from-green-50 to-emerald-50 p-5">
                <p class="text-sm font-medium text-green-700">Total Taman</p>
                <p class="mt-2 text-3xl font-bold text-green-900">{{ number_format($totalTaman) }}</p>
            </div>
            <div class="rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-teal-50 p-5">
                <p class="text-sm font-medium text-emerald-700">Total Luasan</p>
                <p class="mt-2 text-3xl font-bold text-emerald-900">{{ number_format($totalLuasan, 0, ',', '.') }} <span class="text-base font-semibold">M²</span></p>
            </div>
            <div class="rounded-2xl border border-green-100 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-green-700">Data Lengkap</p>
                <p class="mt-2 text-3xl font-bold text-green-800">{{ number_format($rekapStatusData['Lengkap'] ?? 0) }}</p>
            </div>
            <div class="rounded-2xl border border-amber-100 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-amber-700">Belum Lengkap</p>
                <p class="mt-2 text-3xl font-bold text-amber-800">{{ number_format($rekapStatusData['Belum Lengkap'] ?? 0) }}</p>
            </div>
        </div>
    </x-admin.collapsible-card>

    <x-admin.collapsible-card
        title="Capaian Ruang Terbuka Hijau (RTH) per Tahun"
        description="Perhitungan capaian RTH yang dikelola Dinas per tahun">
        @include('admin.taman_laporan.partials.rth-yearly-summary')
    </x-admin.collapsible-card>

    <x-admin.collapsible-card
        title="Rekap Ruang Terbuka Hijau (RTH) per Kategori"
        description="Distribusi jumlah dan luasan RTH berdasarkan kategori">
        <div class="p-5">
            <x-admin.data-table>
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Kategori</th>
                        <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Jumlah</th>
                        <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Luasan (M²)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($rekapKategori as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <x-admin.taman-kategori-badge :kategori="$row['kategori']" />
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-900">{{ number_format($row['jumlah']) }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-900">{{ number_format($row['luasan'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-sm text-gray-500">Tidak ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-admin.data-table>
        </div>
    </x-admin.collapsible-card>

    <x-admin.collapsible-card
        title="Rekap Ruang Terbuka Hijau (RTH) per Wilayah"
        description="Rekap per kecamatan dan rincian per kelurahan">
        <div class="space-y-6 p-5">
            <div>
                <h4 class="mb-3 text-sm font-semibold text-gray-900">Per Kecamatan</h4>
                <x-admin.data-table>
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Kecamatan</th>
                            <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Jumlah</th>
                            <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Luasan (M²)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($rekapKecamatan as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $row['kecamatan'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-900">{{ number_format($row['jumlah']) }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-900">{{ number_format($row['luasan'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-sm text-gray-500">Tidak ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-admin.data-table>
            </div>

            <div>
                <h4 class="mb-3 text-sm font-semibold text-gray-900">Per Kelurahan</h4>
                @forelse ($rekapKelurahanPerKecamatan as $kecamatanGroup)
                    <div class="mb-5 last:mb-0">
                        <p class="mb-2 text-sm font-medium text-green-800">
                            {{ $kecamatanGroup['kecamatan'] }}
                            <span class="font-normal text-gray-500">
                                — {{ number_format($kecamatanGroup['jumlah']) }} lokasi,
                                {{ number_format($kecamatanGroup['luasan'], 0, ',', '.') }} M²
                            </span>
                        </p>
                        <x-admin.data-table>
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Kelurahan</th>
                                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Jumlah</th>
                                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Luasan (M²)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($kecamatanGroup['kelurahan'] as $row)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $row['kelurahan'] }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-900">{{ number_format($row['jumlah']) }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-900">{{ number_format($row['luasan'], 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </x-admin.data-table>
                    </div>
                @empty
                    <p class="rounded-xl border border-dashed border-gray-200 px-4 py-8 text-center text-sm text-gray-500">Tidak ada data wilayah.</p>
                @endforelse
            </div>
        </div>
    </x-admin.collapsible-card>

    @if ($tamansPerKategori->flatten()->isNotEmpty())
        <x-admin.collapsible-card
            title="Daftar Ruang Terbuka Hijau (RTH) per Kategori"
            description="Rincian RTH berdasarkan kategori">
            <div class="space-y-6 p-5">
                @foreach ($tamansPerKategori as $kategoriLabel => $items)
                    @if ($items->isNotEmpty())
                        <div>
                            <div class="mb-3 flex flex-wrap items-center gap-2">
                                <x-admin.taman-kategori-badge :kategori="$kategoriLabel" />
                                <span class="text-sm text-gray-500">{{ $items->count() }} taman · {{ number_format($items->sum('luasan'), 0, ',', '.') }} M²</span>
                            </div>

                            <x-admin.data-table fixed>
                                <colgroup>
                                    <col class="min-w-[7rem] md:w-[34%]">
                                    <col class="min-w-[3.25rem] md:w-[16%]">
                                    <col class="min-w-[2.75rem] md:w-[10%]">
                                    <col class="hidden md:table-column md:w-[8%]">
                                    <col class="min-w-[4.5rem] md:w-[14%]">
                                    <col class="hidden md:table-column md:w-[18%]">
                                </colgroup>
                                @include('admin.tamans.partials.list-table-head', [
                                    'sortState' => $sortState,
                                    'showKategori' => false,
                                    'showActions' => false,
                                ])
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($items as $taman)
                                        @include('admin.tamans.partials.list-table-row', [
                                            'taman' => $taman,
                                            'showKategori' => false,
                                            'showActions' => false,
                                        ])
                                    @endforeach
                                </tbody>
                            </x-admin.data-table>
                        </div>
                    @endif
                @endforeach
            </div>
        </x-admin.collapsible-card>
    @endif

    @if ($totalTaman === 0)
        <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-10 text-center text-gray-500">
            Tidak ada data taman untuk filter yang dipilih.
        </div>
    @endif
@endsection
