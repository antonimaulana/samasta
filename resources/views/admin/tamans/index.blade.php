@extends('layouts.admin')

@section('title', 'Data Taman')
@section('header', 'Data Taman')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-600">Daftar semua taman yang terdaftar di sistem.</p>
        <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.tamans.export-pdf', request()->query()) }}"
           class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            Export PDF
        </a>
        <x-admin.can-manage-users>
        <a href="{{ route('admin.tamans.import') }}"
           class="rounded-lg border border-green-200 bg-green-50 px-4 py-2 text-sm font-medium text-green-800 hover:bg-green-100">
            Import CSV
        </a>
        </x-admin.can-manage-users>
        <x-admin.can-write>
        <a href="{{ route('admin.tamans.create') }}"
           class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
            + Tambah Taman
        </a>
        </x-admin.can-write>
        </div>
    </div>

    @include('admin.partials.table-search', ['placeholder' => 'Cari nama taman, alamat, kategori...'])

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Foto</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Nama Taman</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Kategori</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Wilayah</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Luasan (M²)</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Alamat</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Koordinat</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($tamans as $taman)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                @if ($taman->foto_url)
                                    <img src="{{ $taman->foto_url }}" alt="{{ $taman->nama_taman }}"
                                         class="h-12 w-16 rounded object-cover">
                                @else
                                    <div class="flex h-12 w-16 items-center justify-center rounded bg-gray-100 text-xs text-gray-400">
                                        N/A
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-medium">{{ $taman->nama_taman }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">
                                    {{ $taman->kategori }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                @if ($taman->kelurahan)
                                    <span class="block text-sm">{{ $taman->kelurahan->nama }}</span>
                                    <span class="text-xs text-gray-500">{{ $taman->kelurahan->kecamatan->nama }}</span>
                                @else
                                    <span class="text-xs text-amber-600">Belum diset</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right text-gray-700">{{ number_format($taman->luasan, 0, ',', '.') }}</td>
                            <td class="max-w-xs truncate px-4 py-3 text-gray-600">{{ $taman->alamat }}</td>
                            <td class="px-4 py-3 text-gray-600">
                                @if ($taman->latitude && $taman->longitude)
                                    {{ $taman->latitude }}, {{ $taman->longitude }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.tamans.show', $taman) }}"
                                       class="rounded border border-gray-300 px-3 py-1 text-gray-700 hover:bg-gray-50">
                                        Lihat
                                    </a>
                                    <x-admin.can-write>
                                    <a href="{{ route('admin.tamans.edit', $taman) }}"
                                       class="rounded border border-blue-300 px-3 py-1 text-blue-700 hover:bg-blue-50">
                                        Edit
                                    </a>
                                    </x-admin.can-write>
                                    <x-admin.can-delete>
                                    <form action="{{ route('admin.tamans.destroy', $taman) }}" method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus taman ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="rounded border border-red-300 px-3 py-1 text-red-700 hover:bg-red-50">
                                            Hapus
                                        </button>
                                    </form>
                                    </x-admin.can-delete>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                Belum ada data taman.@if ($canWrite ?? auth()->user()?->canWrite()) <a href="{{ route('admin.tamans.create') }}" class="text-green-700 underline">Tambah sekarang</a>@endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($tamans->hasPages())
            <div class="border-t border-gray-200 px-4 py-3">
                {{ $tamans->links() }}
            </div>
        @endif
    </div>
@endsection
