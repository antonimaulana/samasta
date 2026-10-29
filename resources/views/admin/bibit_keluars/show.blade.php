@extends('layouts.admin')

@section('title', 'Detail Stok Keluar')
@section('header', 'Detail Stok Keluar')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.bibit-keluars.index') }}" class="text-sm text-green-700 hover:underline">← Kembali ke Stok Keluar</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase text-red-600">Stok Keluar</p>
                        <h2 class="mt-1 text-xl font-bold text-gray-900">{{ $batch->peruntukan }}</h2>
                        <p class="mt-1 text-sm text-gray-500">{{ $batch->tanggal_keluar->translatedFormat('d F Y') }}</p>
                    </div>
                    <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-bold text-red-800">
                        -{{ $batch->totalJumlah() }} bibit · {{ $batch->items->count() }} jenis
                    </span>
                </div>

                <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-500">Lokasi</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $batch->lokasiLabel() }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-500">Dicatat</dt>
                        <dd class="mt-1 text-sm text-gray-700">{{ $batch->created_at->translatedFormat('d F Y, H:i') }} WIB</dd>
                    </div>
                </dl>

                <div class="mt-6">
                    <h3 class="text-sm font-semibold text-gray-700">Rincian bibit</h3>
                    <div class="mt-3 overflow-hidden rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium text-gray-700">Nama tanaman</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-700">Jenis</th>
                                    <th class="px-4 py-2 text-right font-medium text-gray-700">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach ($batch->items as $item)
                                    <tr>
                                        <td class="px-4 py-2">
                                            <x-admin.bibit-nama :bibit="$item->bibit" />
                                        </td>
                                        <td class="px-4 py-2 text-gray-600">{{ $item->bibit->jenis }}</td>
                                        <td class="px-4 py-2 text-right font-semibold text-red-700">-{{ $item->jumlah }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($batch->hasFotoPath())
                    <div class="mt-6">
                        <h3 class="text-sm font-semibold text-gray-700">Foto bukti</h3>
                        @if ($batch->foto_url)
                            <a href="{{ $batch->foto_url }}" target="_blank" rel="noopener" class="mt-2 inline-block">
                                <img src="{{ $batch->foto_url }}" alt="Foto stok keluar" class="max-h-96 rounded-lg border border-gray-200 object-cover shadow-sm">
                            </a>
                        @else
                            <div class="mt-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900">
                                File foto tercatat di database tetapi tidak ditemukan di server.
                            </div>
                        @endif
                    </div>
                @endif

                <div class="mt-6 flex flex-wrap gap-2 border-t border-gray-100 pt-6">
                    <x-admin.can-write>
                    <a href="{{ route('admin.bibit-keluars.edit', $batch) }}"
                       class="rounded-lg border border-blue-300 px-4 py-2 text-sm font-medium text-blue-700 hover:bg-blue-50">
                        Edit
                    </a>
                    <x-admin.bibit-keluar-delete-button :batch="$batch" />
                    </x-admin.can-write>
                </div>
            </div>
        </div>
    </div>
@endsection
