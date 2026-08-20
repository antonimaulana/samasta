@extends('layouts.admin')

@section('title', 'Detail Stok Masuk')
@section('header', 'Detail Stok Masuk')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.bibits.index') }}" class="text-sm text-green-700 hover:underline">← Kembali ke Kelola Bibit</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase text-teal-600">Stok Masuk</p>
                        <h2 class="mt-1 text-xl font-bold text-gray-900">
                            <x-admin.bibit-nama :bibit="$masuk->bibit" />
                        </h2>
                        <p class="mt-1 text-sm text-gray-500">{{ $masuk->tanggal_masuk->translatedFormat('d F Y') }}</p>
                    </div>
                    <span class="rounded-full bg-teal-100 px-3 py-1 text-xs font-bold text-teal-800">
                        +{{ $masuk->jumlah }} bibit
                    </span>
                </div>

                <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-500">Jenis</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $masuk->bibit->jenis }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-500">Sumber</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $masuk->sumber }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-500">Sisa Stok Batch</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $masuk->sisa_stok }} / {{ $masuk->jumlah }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-500">Status Siap Tanam</dt>
                        <dd class="mt-1">
                            @if ($masuk->status_siap_tanam)
                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">Siap Tanam</span>
                            @else
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">Belum Siap</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-500">Dicatat</dt>
                        <dd class="mt-1 text-sm text-gray-700">{{ $masuk->created_at->translatedFormat('d F Y, H:i') }} WIB</dd>
                    </div>
                </dl>

                @if ($masuk->foto_url)
                    <div class="mt-6">
                        <h3 class="text-sm font-semibold text-gray-700">Foto bukti</h3>
                        <a href="{{ $masuk->foto_url }}" target="_blank" rel="noopener">
                            <img src="{{ $masuk->foto_url }}" alt="Foto stok masuk" class="mt-2 max-h-96 rounded-lg border object-cover">
                        </a>
                    </div>
                @endif

                <div class="mt-6 flex flex-wrap gap-2 border-t border-gray-100 pt-6">
                    <x-admin.can-write>
                    <a href="{{ route('admin.bibit-masuks.edit', $masuk) }}"
                       class="rounded-lg border border-blue-300 px-4 py-2 text-sm font-medium text-blue-700 hover:bg-blue-50">
                        Edit
                    </a>
                    <x-admin.bibit-masuk-delete-button :masuk="$masuk" />
                    </x-admin.can-write>
                </div>
            </div>
        </div>
    </div>
@endsection
