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

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Tanggal</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Nama Tanaman</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Jenis</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Jumlah</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Sumber</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Siap Tanam</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Sisa Stok</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($bibitMasuks as $masuk)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-600">{{ $masuk->tanggal_masuk->format('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <x-admin.bibit-nama :bibit="$masuk->bibit" />
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded-full bg-teal-100 px-2.5 py-0.5 text-xs font-medium text-teal-800">
                                    {{ $masuk->bibit->jenis }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-semibold text-teal-700">+{{ $masuk->jumlah }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $masuk->sumber }}</td>
                            <td class="px-4 py-3">
                                @if ($masuk->status_siap_tanam)
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs text-emerald-800">Siap</span>
                                @else
                                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600">Belum</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-700">{{ $masuk->sisa_stok }} / {{ $masuk->jumlah }}</td>
                            <td class="px-4 py-3 text-right">
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
            </table>
        </div>

        @if ($bibitMasuks->hasPages())
            <div class="border-t border-gray-200 px-4 py-3">
                {{ $bibitMasuks->links() }}
            </div>
        @endif
    </div>
@endsection
