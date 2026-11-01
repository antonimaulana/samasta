@extends('layouts.admin')

@section('title', 'Edit Stok Keluar')
@section('header', 'Edit Stok Keluar')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.bibit-keluars.show', $batch) }}" class="text-sm text-green-700 hover:underline">← Kembali ke detail</a>
    </div>

    <div class="max-w-3xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('admin.bibit-keluars.update', $batch) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <x-operasional-pelaksanaan-datetime
                name="tanggal_keluar"
                :value="$batch->tanggal_keluar"
                label="Tanggal & Waktu Keluar"
            />

            <div class="space-y-6">
                @include('admin.bibit_keluars._peruntukan_lokasi')
            </div>

            @include('admin.bibit_keluars._baris_bibit')

            @include('admin.partials.bibit-stok-foto-input', [
                'required' => false,
                'existingCount' => $batch->buktiFotos->count(),
            ])

            <div class="flex gap-3">
                <button type="submit"
                        class="rounded-lg bg-green-600 px-5 py-2 text-sm font-medium text-white hover:bg-green-700">
                    Simpan Perubahan
                </button>
                <a href="{{ route('admin.bibit-keluars.show', $batch) }}"
                   class="rounded-lg border border-gray-300 px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
