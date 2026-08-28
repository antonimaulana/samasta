@extends('layouts.admin')

@section('title', 'Stok Masuk Bibit')
@section('header', 'Stok Masuk Bibit')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.bibits.index') }}" class="text-sm text-green-700 hover:underline">← Kembali ke Kelola Bibit</a>
    </div>

    <div class="mb-6 flex items-center justify-between">
        <p class="text-sm text-gray-600">Riwayat penambahan stok bibit ke pembibitan.</p>
        <x-admin.can-write>
        <a href="{{ route('admin.bibit-masuks.create') }}"
           class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
            + Catat Stok Masuk
        </a>
        </x-admin.can-write>
    </div>

    @include('admin.partials.table-search', ['placeholder' => 'Cari tanaman, sumber...'])

    <x-admin.data-table>
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Tanggal</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Nama Tanaman</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Jenis</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Jumlah</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Sumber</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Siap Tanam</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Sisa Stok</th>
                <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($bibitMasuks as $masuk)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $masuk->tanggal_masuk->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-sm">
                        <x-admin.bibit-nama :bibit="$masuk->bibit" />
                    </td>
                    <td class="px-4 py-3 text-sm">
                        <span class="rounded-full bg-teal-100 px-2.5 py-0.5 text-xs font-medium text-teal-800">
                            {{ $masuk->bibit->jenis }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm">
                        <span class="font-semibold text-teal-700">+{{ $masuk->jumlah }}</span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $masuk->sumber }}</td>
                    <td class="px-4 py-3 text-sm">
                        @if ($masuk->status_siap_tanam)
                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs text-emerald-800">Siap</span>
                        @else
                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600">Belum</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm font-medium text-gray-700">{{ $masuk->sisa_stok }} / {{ $masuk->jumlah }}</td>
                    <td class="px-4 py-3 text-right text-sm">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.bibit-masuks.show', $masuk) }}"
                               class="rounded border border-gray-300 px-3 py-1 text-gray-700 hover:bg-gray-50">
                                Lihat
                            </a>
                            <x-admin.can-write>
                            <a href="{{ route('admin.bibit-masuks.edit', $masuk) }}"
                               class="rounded border border-blue-300 px-3 py-1 text-blue-700 hover:bg-blue-50">
                                Edit
                            </a>
                            <x-admin.bibit-masuk-delete-button :masuk="$masuk" />
                            </x-admin.can-write>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                        Belum ada riwayat stok masuk.@if ($canWrite ?? auth()->user()?->canWrite()) <a href="{{ route('admin.bibit-masuks.create') }}" class="text-green-700 underline">Catat sekarang</a>@endif
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if ($bibitMasuks->hasPages())
            <x-slot:footer>
                {{ $bibitMasuks->links() }}
            </x-slot:footer>
        @endif
    </x-admin.data-table>
@endsection
