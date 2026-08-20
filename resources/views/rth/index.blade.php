@extends('layouts.public')

@section('title', 'RTH Kota Batam')

@section('meta_description', 'Data luas dan lokasi Ruang Terbuka Hijau (RTH) Kota Batam — taman kota, taman lingkungan, jalur hijau, Kebun Raya, dan TPU.')

@section('content')
    <div class="mb-10">
        <p class="text-xs font-bold tracking-wide text-emerald-600">Disperkimtan Kota Batam</p>
        <h1 class="mt-2 text-3xl font-bold text-emerald-950 sm:text-4xl">Ruang Terbuka Hijau (RTH) Kota Batam</h1>
        <p class="mt-3 max-w-3xl text-gray-600 leading-relaxed">
            Data jumlah luas RTH Kota Batam yang dikelola Disperakimtan dalam kondisi terpelihara —
            meliputi taman kota, taman lingkungan, jalur hijau jalan, Kebun Raya Batam, dan TPU.
        </p>
    </div>

    <div class="mb-10 grid gap-4 sm:grid-cols-2">
        <div class="rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-600 to-teal-600 p-6 text-white shadow-lg shadow-emerald-600/20">
            <p class="text-sm font-medium text-emerald-100">Total Luas RTH</p>
            <p class="mt-2 text-4xl font-black tracking-tight">{{ number_format($rthTotalLuas, 0, ',', '.') }}</p>
            <p class="mt-1 text-sm text-emerald-100/80">meter persegi (M²)</p>
        </div>
        <div class="rounded-2xl border border-teal-200 bg-gradient-to-br from-teal-50 to-emerald-50 p-6 shadow-sm">
            <p class="text-sm font-medium text-teal-800">Total Lokasi</p>
            <p class="mt-2 text-4xl font-black tracking-tight text-teal-950">{{ number_format($rthTotalLokasi) }}</p>
            <p class="mt-1 text-sm text-teal-700/80">titik RTH terpelihara</p>
        </div>
    </div>

    <div class="mb-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($rthStats as $rth)
            @php
                $persenLuas = $rthTotalLuas > 0 ? round(($rth['luas'] / $rthTotalLuas) * 100, 1) : 0;
            @endphp
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-xl">
                        {{ $rth['icon'] }}
                    </div>
                    <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">
                        {{ $persenLuas }}% luas
                    </span>
                </div>
                <h2 class="mt-4 font-bold text-gray-900">{{ $rth['nama'] }}</h2>
                <p class="mt-2 text-sm leading-relaxed text-gray-600">{{ $rth['ringkas'] }}</p>
                <div class="mt-4 flex items-end justify-between gap-4 border-t border-gray-100 pt-4">
                    <div>
                        <p class="text-xl font-bold text-emerald-800">{{ number_format($rth['luas'], 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-500">M²</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xl font-bold text-teal-800">{{ number_format($rth['lokasi']) }}</p>
                        <p class="text-xs text-gray-500">Lokasi</p>
                    </div>
                </div>
                <div class="mt-4 h-2 overflow-hidden rounded-full bg-gray-100">
                    <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-500"
                         style="width: {{ min($persenLuas, 100) }}%"></div>
                </div>
            </div>
        @endforeach
    </div>

    @if (($tamanStats['jumlah_taman'] ?? 0) > 0)
        <div class="mb-10 overflow-hidden rounded-2xl border border-teal-200 bg-white shadow-sm">
            <div class="border-b border-teal-100 bg-gradient-to-r from-teal-50 to-emerald-50 px-6 py-5">
                <p class="text-xs font-bold uppercase tracking-wider text-teal-700">Data Taman Terdaftar</p>
                <h2 class="mt-1 text-xl font-bold text-teal-950">Statistik RTH per Wilayah Administratif</h2>
                <p class="mt-2 text-sm text-teal-800/80">
                    Agregasi dari {{ number_format($tamanStats['jumlah_taman']) }} taman terdaftar di sistem
                    @if ($tamanStats['belum_wilayah'] > 0)
                        ({{ number_format($tamanStats['belum_wilayah']) }} belum memiliki kelurahan)
                    @endif
                </p>
            </div>

            <div class="grid gap-4 border-b border-gray-100 p-6 sm:grid-cols-3">
                <div class="rounded-xl bg-emerald-50 p-4">
                    <p class="text-xs font-semibold uppercase text-emerald-700">Total Taman</p>
                    <p class="mt-1 text-2xl font-black text-emerald-900">{{ number_format($tamanStats['jumlah_taman']) }}</p>
                </div>
                <div class="rounded-xl bg-teal-50 p-4">
                    <p class="text-xs font-semibold uppercase text-teal-700">Total Luas</p>
                    <p class="mt-1 text-2xl font-black text-teal-900">{{ number_format($tamanStats['total_luasan'], 0, ',', '.') }} M²</p>
                </div>
                <div class="rounded-xl bg-sky-50 p-4">
                    <p class="text-xs font-semibold uppercase text-sky-700">Kecamatan Terdata</p>
                    <p class="mt-1 text-2xl font-black text-sky-900">{{ number_format($perKecamatan->count()) }}</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left font-semibold text-gray-700">Kecamatan</th>
                            <th class="px-5 py-3 text-right font-semibold text-gray-700">Jumlah Taman</th>
                            <th class="px-5 py-3 text-right font-semibold text-gray-700">Total Luas (M²)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($perKecamatan as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 font-medium text-gray-900">{{ $row->kecamatan }}</td>
                                <td class="px-5 py-3 text-right text-gray-700">{{ number_format($row->jumlah_taman) }}</td>
                                <td class="px-5 py-3 text-right text-gray-700">{{ number_format($row->total_luasan, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-5 py-6 text-center text-gray-500">Belum ada taman dengan data kelurahan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($perKelurahanGrouped !== [])
                <details class="border-t border-gray-100">
                    <summary class="cursor-pointer px-6 py-4 text-sm font-semibold text-emerald-800 hover:bg-emerald-50/50">
                        Detail per Kelurahan
                    </summary>
                    <div class="space-y-4 px-6 pb-6">
                        @foreach ($perKelurahanGrouped as $kecamatan)
                            <div class="rounded-xl border border-gray-200">
                                <div class="border-b border-gray-100 bg-gray-50 px-4 py-2 text-sm font-semibold text-gray-800">
                                    {{ $kecamatan['kecamatan'] }}
                                    <span class="ml-2 font-normal text-gray-500">
                                        ({{ number_format($kecamatan['jumlah_taman']) }} taman · {{ number_format($kecamatan['total_luasan'], 0, ',', '.') }} M²)
                                    </span>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-xs">
                                        <tbody class="divide-y divide-gray-100">
                                            @foreach ($kecamatan['kelurahan'] as $kel)
                                                <tr>
                                                    <td class="px-4 py-2 text-gray-800">{{ $kel['kelurahan'] }}</td>
                                                    <td class="px-4 py-2 text-right text-gray-600">{{ number_format($kel['jumlah_taman']) }} taman</td>
                                                    <td class="px-4 py-2 text-right text-gray-600">{{ number_format($kel['total_luasan'], 0, ',', '.') }} M²</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </details>
            @endif
        </div>
    @endif
@endsection
