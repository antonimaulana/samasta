@extends('layouts.admin')

@section('title', $item->nama)
@section('header', 'Detail Alat/Sarana')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.alat-sarana-operasionals.index') }}"
           class="text-sm font-medium text-green-700 hover:text-green-800">
            ← Kembali ke daftar
        </a>
        <div class="flex flex-wrap gap-2">
            <x-admin.can-write>
                <a href="{{ route('admin.alat-sarana-operasionals.edit', $item) }}"
                   class="rounded-lg border border-blue-300 px-4 py-2 text-sm font-medium text-blue-700 hover:bg-blue-50">
                    Edit
                </a>
            </x-admin.can-write>
        </div>
    </div>

    <div class="mb-6 grid gap-4 lg:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">{{ $item->nama }}</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between gap-4 border-b border-gray-100 pb-3">
                    <dt class="text-gray-500">Jenis</dt>
                    <dd class="font-medium text-gray-900">{{ $item->jenis }}</dd>
                </div>
                @if ($item->no_plat)
                    <div class="flex justify-between gap-4 border-b border-gray-100 pb-3">
                        <dt class="text-gray-500">Nomor Plat</dt>
                        <dd class="font-medium text-gray-900">{{ $item->no_plat }}</dd>
                    </div>
                @endif
                @if ($item->sopir)
                    <div class="flex justify-between gap-4 border-b border-gray-100 pb-3">
                        <dt class="text-gray-500">Sopir Default</dt>
                        <dd class="font-medium text-gray-900">{{ $item->sopir }}</dd>
                    </div>
                @endif
                <div class="flex justify-between gap-4 border-b border-gray-100 pb-3">
                    <dt class="text-gray-500">Jumlah</dt>
                    <dd class="font-medium text-gray-900">{{ number_format($item->jumlah) }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-gray-100 pb-3">
                    <dt class="text-gray-500">Peruntukan</dt>
                    <dd>
                        <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-800">{{ $item->peruntukan }}</span>
                    </dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-gray-100 pb-3">
                    <dt class="text-gray-500">Kondisi</dt>
                    <dd class="font-medium text-gray-900">{{ $item->kondisi }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Keterangan</dt>
                    <dd class="mt-1 text-gray-900">{{ $item->keterangan ?: '—' }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-green-50 p-6 shadow-sm">
            <p class="text-sm font-medium text-emerald-800">Total Penggunaan Tercatat</p>
            <p class="mt-2 text-4xl font-bold text-emerald-900">{{ number_format($usageHistory->count()) }}</p>
            <p class="mt-2 text-sm text-emerald-700/80">
                Riwayat dari pemeliharaan rutin dan permohonan operasional.
            </p>
        </div>
    </div>

    <x-admin.collapsible-card
        title="Riwayat Penggunaan Armada"
        description="Pekerjaan pemeliharaan rutin dan permohonan yang menggunakan armada ini"
        :open="true">
        @if ($item->isArmada())
            <x-admin.data-table class="mx-5 mb-5 mt-5">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Tanggal</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Sumber</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Pekerjaan</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Lokasi</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Sopir</th>
                        <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($usageHistory as $entry)
                        <tr class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900">
                                {{ $entry['tanggal']?->format('d/m/Y') ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'rounded-full px-2 py-0.5 text-xs font-semibold',
                                    'bg-blue-100 text-blue-800' => $entry['sumber'] === 'Permohonan',
                                    'bg-emerald-100 text-emerald-800' => $entry['sumber'] === 'Pemeliharaan Rutin',
                                ])>
                                    {{ $entry['sumber'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $entry['pekerjaan'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $entry['lokasi'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $entry['sopir'] ?: '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                @if ($entry['url'])
                                    <a href="{{ $entry['url'] }}"
                                       class="text-sm font-medium text-green-700 hover:text-green-800">
                                        Lihat
                                    </a>
                                @else
                                    <span class="text-sm text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">
                                Belum ada riwayat penggunaan armada ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-admin.data-table>
        @else
            <div class="p-5 text-sm text-gray-600">
                Riwayat penggunaan hanya tersedia untuk jenis armada operasional (Dump Truck, Truck, Crane, Kendaraan Operasional).
            </div>
        @endif
    </x-admin.collapsible-card>
@endsection
