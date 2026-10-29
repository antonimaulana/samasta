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

            <div>
                <label for="tanggal_keluar" class="mb-1 block text-sm font-medium text-gray-700">Tanggal Keluar *</label>
                <input type="date" name="tanggal_keluar" id="tanggal_keluar"
                       value="{{ old('tanggal_keluar', $batch->tanggal_keluar->format('Y-m-d')) }}"
                       required
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
            </div>

            <div class="space-y-6">
                @include('admin.bibit_keluars._peruntukan_lokasi')
            </div>

            @include('admin.bibit_keluars._baris_bibit')

            <div>
                <label for="foto" class="mb-1 block text-sm font-medium text-gray-700">Foto Bukti</label>
                @if ($batch->foto_url)
                    <a href="{{ $batch->foto_url }}" target="_blank" rel="noopener" class="mb-2 block">
                        <img src="{{ $batch->foto_url }}" alt="Foto saat ini" class="max-h-40 rounded-lg border object-cover">
                    </a>
                @endif
                <input type="file" name="foto" id="foto" accept="image/*"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-green-50 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-green-700 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                <p class="mt-1 text-xs text-gray-500">Kosongkan jika tidak ingin mengganti foto. Format JPG/PNG, maks. 4 MB.</p>
            </div>

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
