@extends('layouts.admin')

@section('title', 'Detail Stok Keluar')
@section('header', 'Detail Stok Keluar')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.bibits.index') }}" class="text-sm text-green-700 hover:underline">← Kembali ke Kelola Bibit</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase text-red-600">Stok Keluar</p>
                        <h2 class="mt-1 text-xl font-bold text-gray-900">
                            <x-admin.bibit-nama :bibit="$keluar->bibit" />
                        </h2>
                        <p class="mt-1 text-sm text-gray-500">{{ $keluar->tanggal_keluar->translatedFormat('d F Y') }}</p>
                    </div>
                    <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-bold text-red-800">
                        -{{ $keluar->jumlah }} bibit
                    </span>
                </div>

                <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-500">Jenis</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $keluar->bibit->jenis }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-500">Peruntukan</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $keluar->peruntukan }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-500">Lokasi</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $keluar->taman?->nama_taman ?? '—' }}</dd>
                        @if ($keluar->taman?->alamat)
                            <dd class="text-sm text-gray-600">{{ $keluar->taman->alamat }}</dd>
                        @endif
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-500">Dicatat</dt>
                        <dd class="mt-1 text-sm text-gray-700">{{ $keluar->created_at->translatedFormat('d F Y, H:i') }} WIB</dd>
                    </div>
                </dl>

                @if ($keluar->foto_url)
                    <div class="mt-6">
                        <h3 class="text-sm font-semibold text-gray-700">Foto bukti</h3>
                        <a href="{{ $keluar->foto_url }}" target="_blank" rel="noopener">
                            <img src="{{ $keluar->foto_url }}" alt="Foto stok keluar" class="mt-2 max-h-96 rounded-lg border object-cover">
                        </a>
                    </div>
                @endif

                <div class="mt-6 flex flex-wrap gap-2 border-t border-gray-100 pt-6">
                    <x-admin.can-write>
                    <a href="{{ route('admin.bibit-keluars.edit', $keluar) }}"
                       class="rounded-lg border border-blue-300 px-4 py-2 text-sm font-medium text-blue-700 hover:bg-blue-50">
                        Edit
                    </a>
                    <x-admin.bibit-keluar-delete-button :keluar="$keluar" />
                    </x-admin.can-write>
                </div>
            </div>
        </div>
    </div>
@endsection
