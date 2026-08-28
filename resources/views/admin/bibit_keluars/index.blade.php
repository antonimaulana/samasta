@extends('layouts.admin')

@section('title', 'Stok Keluar Bibit')
@section('header', 'Stok Keluar Bibit')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.bibits.index') }}" class="text-sm text-green-700 hover:underline">← Kembali ke Kelola Bibit</a>
    </div>

    <div class="mb-6 flex items-center justify-between">
        <p class="text-sm text-gray-600">Riwayat pengeluaran bibit dari pembibitan {{ config('app.name') }}.</p>
        <x-admin.can-write>
        <a href="{{ route('admin.bibit-keluars.create') }}"
           class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
            + Catat Stok Keluar
        </a>
        </x-admin.can-write>
    </div>

    @include('admin.partials.table-search', ['placeholder' => 'Cari tanaman, peruntukan, lokasi...'])

    <x-admin.data-table>
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Tanggal</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Nama Tanaman</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Jenis</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Jumlah</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Peruntukan</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Lokasi</th>
                <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($bibitKeluars as $keluar)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $keluar->tanggal_keluar->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-sm">
                        <x-admin.bibit-nama :bibit="$keluar->bibit" />
                    </td>
                    <td class="px-4 py-3 text-sm">
                        <span class="rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                            {{ $keluar->bibit->jenis }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm">
                        <span class="font-semibold text-red-700">-{{ $keluar->jumlah }}</span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $keluar->peruntukan }}</td>
                    <td class="max-w-xs truncate px-4 py-3 text-sm text-gray-600">{{ $keluar->taman?->nama_taman ?? '—' }}</td>
                    <td class="px-4 py-3 text-right text-sm">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.bibit-keluars.show', $keluar) }}"
                               class="rounded border border-gray-300 px-3 py-1 text-gray-700 hover:bg-gray-50">
                                Lihat
                            </a>
                            <x-admin.can-write>
                            <a href="{{ route('admin.bibit-keluars.edit', $keluar) }}"
                               class="rounded border border-blue-300 px-3 py-1 text-blue-700 hover:bg-blue-50">
                                Edit
                            </a>
                            <x-admin.bibit-keluar-delete-button :keluar="$keluar" />
                            </x-admin.can-write>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                        Belum ada riwayat stok keluar.@if ($canWrite ?? auth()->user()?->canWrite()) <a href="{{ route('admin.bibit-keluars.create') }}" class="text-green-700 underline">Catat sekarang</a>@endif
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if ($bibitKeluars->hasPages())
            <x-slot:footer>
                {{ $bibitKeluars->links() }}
            </x-slot:footer>
        @endif
    </x-admin.data-table>
@endsection
