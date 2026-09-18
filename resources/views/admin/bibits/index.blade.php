@extends('layouts.admin')

@section('title', 'Kelola Bibit')
@section('header', 'Kelola Bibit')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-sm text-gray-600">Master data bibit, stok masuk, dan stok keluar.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <x-admin.can-manage-users>
            <a href="{{ route('admin.bibits.import') }}"
               class="rounded-lg border border-teal-200 bg-teal-50 px-4 py-2 text-sm font-medium text-teal-800 hover:bg-teal-100">
                Import CSV
            </a>
            </x-admin.can-manage-users>
            <x-admin.can-write>
            <a href="{{ route('admin.bibit-masuks.create') }}"
               class="rounded-lg border border-teal-300 bg-teal-50 px-4 py-2 text-sm font-medium text-teal-800 hover:bg-teal-100">
                + Stok Masuk
            </a>
            <a href="{{ route('admin.bibit-keluars.create') }}"
               class="rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-800 hover:bg-emerald-100">
                + Stok Keluar
            </a>
            <a href="{{ route('admin.bibits.create') }}"
               class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                + Tambah Bibit
            </a>
            </x-admin.can-write>
        </div>
    </div>

    @include('admin.partials.table-search', ['placeholder' => 'Cari nama tanaman, nama ilmiah, jenis...'])

    <x-admin.data-table>
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Nama Tanaman</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Jenis</th>
                        <th class="px-4 py-3 text-center text-sm font-medium text-gray-700">Total Stok</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Stok per Sumber</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Kesiapan Tanam</th>
                        <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($bibits as $bibit)
                        @php
                            $stokSumber = $bibit->stokPerSumber();
                            $siap = $bibit->stokSiapTanam();
                            $belum = $bibit->stokBelumSiap();
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <x-admin.bibit-nama :bibit="$bibit" />
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                    {{ $bibit->jenis }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-lg font-bold text-gray-900">{{ number_format($bibit->stok_tersedia) }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ($stokSumber as $sumber => $jumlah)
                                        @if ($jumlah > 0)
                                            <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-xs
                                                {{ $sumber === 'Produksi' ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : '' }}
                                                {{ $sumber === 'Pengadaan' ? 'border-blue-200 bg-blue-50 text-blue-800' : '' }}
                                                {{ $sumber === 'Hibah' ? 'border-purple-200 bg-purple-50 text-purple-800' : '' }}">
                                                {{ $sumber }}: <strong>{{ number_format($jumlah) }}</strong>
                                            </span>
                                        @endif
                                    @endforeach
                                    @if ($bibit->stok_tersedia === 0)
                                        <span class="text-xs text-gray-400">Belum ada stok</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">
                                            Siap: {{ number_format($siap) }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">
                                            Belum: {{ number_format($belum) }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.bibits.show', $bibit) }}"
                                       class="rounded border border-gray-300 px-3 py-1 text-gray-700 hover:bg-gray-50">
                                        Lihat
                                    </a>
                                    <x-admin.can-write>
                                    <a href="{{ route('admin.bibits.edit', $bibit) }}"
                                       class="rounded border border-blue-300 px-3 py-1 text-blue-700 hover:bg-blue-50">
                                        Edit
                                    </a>
                                    </x-admin.can-write>
                                    <x-admin.can-delete>
                                    <form action="{{ route('admin.bibits.destroy', $bibit) }}" method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus data bibit ini?')">
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
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                Belum ada data bibit.@if ($canWrite ?? auth()->user()?->canWrite()) <a href="{{ route('admin.bibits.create') }}" class="text-green-700 underline">Tambah sekarang</a>@endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
        @if ($bibits->hasPages())
            <x-slot:footer>
                {{ $bibits->links() }}
            </x-slot:footer>
        @endif
    </x-admin.data-table>

    <div class="mt-8 grid gap-6 xl:grid-cols-2">
        <x-admin.data-table>
            <x-slot:header>
                <div class="flex items-center justify-between border-b border-teal-50 bg-teal-50 px-4 py-3">
                    <h3 class="text-sm font-semibold text-teal-800">Transaksi Masuk Terbaru</h3>
                    <a href="{{ route('admin.bibit-masuks.index') }}" class="text-xs font-medium text-teal-700 hover:underline">
                        Lihat semua →
                    </a>
                </div>
            </x-slot:header>
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Tanggal</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Bibit</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Jumlah</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Sumber</th>
                        <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Aksi</th>
                    </tr>
                </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($recentMasuks as $masuk)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 text-gray-600">{{ $masuk->tanggal_masuk->format('d M Y') }}</td>
                                <td class="px-4 py-2">
                                    <x-admin.bibit-nama :bibit="$masuk->bibit" />
                                </td>
                                <td class="px-4 py-2 font-semibold text-teal-700">+{{ number_format($masuk->jumlah) }}</td>
                                <td class="px-4 py-2 text-gray-600">{{ $masuk->sumber }}</td>
                                <td class="px-4 py-2 text-right">
                                    <div class="flex justify-end gap-1">
                                        <a href="{{ route('admin.bibit-masuks.show', $masuk) }}"
                                           class="rounded border border-gray-300 px-2 py-0.5 text-xs text-gray-700 hover:bg-gray-50">
                                            Lihat
                                        </a>
                                        <x-admin.bibit-masuk-delete-button :masuk="$masuk" size="xs" />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                                    Belum ada transaksi masuk.
                                    @if ($canWrite ?? auth()->user()?->canWrite())
                                        <a href="{{ route('admin.bibit-masuks.create') }}" class="text-green-700 underline">Catat stok masuk</a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
        </x-admin.data-table>

        <x-admin.data-table>
            <x-slot:header>
                <div class="flex items-center justify-between border-b border-red-50 bg-red-50 px-4 py-3">
                    <h3 class="text-sm font-semibold text-red-800">Transaksi Keluar Terbaru</h3>
                    <a href="{{ route('admin.bibit-keluars.index') }}" class="text-xs font-medium text-red-700 hover:underline">
                        Lihat semua →
                    </a>
                </div>
            </x-slot:header>
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Tanggal</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Bibit</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Jumlah</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Lokasi</th>
                        <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Aksi</th>
                    </tr>
                </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($recentKeluars as $keluar)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 text-gray-600">{{ $keluar->tanggal_keluar->format('d M Y') }}</td>
                                <td class="px-4 py-2">
                                    <x-admin.bibit-nama :bibit="$keluar->bibit" />
                                </td>
                                <td class="px-4 py-2 font-semibold text-red-700">-{{ number_format($keluar->jumlah) }}</td>
                                <td class="max-w-[8rem] truncate px-4 py-2 text-gray-600">{{ $keluar->taman?->nama_taman ?? '—' }}</td>
                                <td class="px-4 py-2 text-right">
                                    <div class="flex justify-end gap-1">
                                        <a href="{{ route('admin.bibit-keluars.show', $keluar) }}"
                                           class="rounded border border-gray-300 px-2 py-0.5 text-xs text-gray-700 hover:bg-gray-50">
                                            Lihat
                                        </a>
                                        <x-admin.bibit-keluar-delete-button :keluar="$keluar" size="xs" />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                                    Belum ada transaksi keluar.
                                    @if ($canWrite ?? auth()->user()?->canWrite())
                                        <a href="{{ route('admin.bibit-keluars.create') }}" class="text-green-700 underline">Catat stok keluar</a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
        </x-admin.data-table>
    </div>
@endsection
