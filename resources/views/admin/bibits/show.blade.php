@extends('layouts.admin')

@section('title', 'Detail Bibit')
@section('header', 'Detail Bibit')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.bibits.index') }}" class="text-sm text-green-700 hover:underline">← Kembali ke daftar bibit</a>
    </div>

    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase text-green-600">Data Bibit</p>
                <h2 class="mt-1 text-2xl font-bold text-gray-900">
                    <x-admin.bibit-nama :bibit="$bibit" />
                </h2>
                <p class="mt-1 text-sm text-gray-500">Terdaftar {{ $bibit->created_at->translatedFormat('d F Y') }}</p>
            </div>
            <span class="rounded-full bg-green-100 px-3 py-1 text-sm font-bold text-green-800">
                {{ $bibit->jenis }}
            </span>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg border border-gray-100 bg-gray-50 p-4">
                <p class="text-xs font-semibold uppercase text-gray-500">Stok Tersedia</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ number_format($bibit->stok_tersedia) }}</p>
            </div>
            <div class="rounded-lg border border-teal-100 bg-teal-50 p-4">
                <p class="text-xs font-semibold uppercase text-teal-700">Total Masuk</p>
                <p class="mt-1 text-2xl font-bold text-teal-800">+{{ number_format($totalMasuk) }}</p>
            </div>
            <div class="rounded-lg border border-red-100 bg-red-50 p-4">
                <p class="text-xs font-semibold uppercase text-red-700">Total Keluar</p>
                <p class="mt-1 text-2xl font-bold text-red-800">-{{ number_format($totalKeluar) }}</p>
            </div>
            <div class="rounded-lg border border-emerald-100 bg-emerald-50 p-4">
                <p class="text-xs font-semibold uppercase text-emerald-700">Siap Tanam</p>
                <p class="mt-1 text-2xl font-bold text-emerald-800">{{ number_format($siap) }}</p>
                <p class="mt-0.5 text-xs text-gray-500">Belum siap: {{ number_format($belum) }}</p>
            </div>
        </div>

        @if ($bibit->stok_tersedia > 0)
            <div class="mt-6">
                <p class="text-xs font-semibold uppercase text-gray-500">Stok per Sumber</p>
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach ($stokSumber as $sumber => $jumlah)
                        @if ($jumlah > 0)
                            <span class="inline-flex items-center gap-1 rounded-full border px-3 py-1 text-xs
                                {{ $sumber === 'Produksi' ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : '' }}
                                {{ $sumber === 'Pengadaan' ? 'border-blue-200 bg-blue-50 text-blue-800' : '' }}
                                {{ $sumber === 'Hibah' ? 'border-purple-200 bg-purple-50 text-purple-800' : '' }}">
                                {{ $sumber }}: <strong>{{ number_format($jumlah) }}</strong>
                            </span>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mt-6 flex flex-wrap gap-2">
            <x-admin.can-write>
            <a href="{{ route('admin.bibits.edit', $bibit) }}"
               class="rounded-lg border border-blue-300 px-4 py-2 text-sm font-medium text-blue-700 hover:bg-blue-50">
                Edit Data Bibit
            </a>
            <a href="{{ route('admin.bibit-masuks.create', ['bibit_id' => $bibit->id]) }}"
               class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">
                + Catat Stok Masuk
            </a>
            @if ($bibit->stok_tersedia > 0)
                <a href="{{ route('admin.bibit-keluars.create', ['bibit_id' => $bibit->id]) }}"
                   class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                    + Catat Stok Keluar
                </a>
            @endif
            </x-admin.can-write>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-teal-50 bg-teal-50 px-4 py-3">
                <h3 class="text-sm font-semibold text-teal-800">Riwayat Stok Masuk ({{ $bibit->masuks->count() }})</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">Tanggal</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">Jumlah</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">Sumber</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">Sisa</th>
                            <th class="px-4 py-2 text-right font-medium text-gray-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($bibit->masuks as $masuk)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 text-gray-600">{{ $masuk->tanggal_masuk->format('d M Y') }}</td>
                                <td class="px-4 py-2 font-semibold text-teal-700">+{{ $masuk->jumlah }}</td>
                                <td class="px-4 py-2 text-gray-600">{{ $masuk->sumber }}</td>
                                <td class="px-4 py-2 text-gray-700">{{ $masuk->sisa_stok }} / {{ $masuk->jumlah }}</td>
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
                                <td colspan="5" class="px-4 py-6 text-center text-gray-500">Belum ada riwayat stok masuk.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-red-50 bg-red-50 px-4 py-3">
                <h3 class="text-sm font-semibold text-red-800">Riwayat Stok Keluar ({{ $bibit->keluars->count() }})</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">Tanggal</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">Jumlah</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">Peruntukan</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">Lokasi</th>
                            <th class="px-4 py-2 text-right font-medium text-gray-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($bibit->keluars as $keluar)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 text-gray-600">{{ $keluar->tanggal_keluar->format('d M Y') }}</td>
                                <td class="px-4 py-2 font-semibold text-red-700">-{{ $keluar->jumlah }}</td>
                                <td class="px-4 py-2 text-gray-600">{{ $keluar->peruntukan }}</td>
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
                                <td colspan="5" class="px-4 py-6 text-center text-gray-500">Belum ada riwayat stok keluar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
