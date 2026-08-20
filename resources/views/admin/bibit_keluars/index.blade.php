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

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Tanggal</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Nama Tanaman</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Jenis</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Jumlah</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Peruntukan</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Lokasi</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($bibitKeluars as $keluar)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-600">{{ $keluar->tanggal_keluar->format('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <x-admin.bibit-nama :bibit="$keluar->bibit" />
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                    {{ $keluar->bibit->jenis }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-semibold text-red-700">-{{ $keluar->jumlah }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $keluar->peruntukan }}</td>
                            <td class="max-w-xs truncate px-4 py-3 text-gray-600">{{ $keluar->taman?->nama_taman ?? '—' }}</td>
                            <td class="px-4 py-3 text-right">
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
            </table>
        </div>

        @if ($bibitKeluars->hasPages())
            <div class="border-t border-gray-200 px-4 py-3">
                {{ $bibitKeluars->links() }}
            </div>
        @endif
    </div>
@endsection
