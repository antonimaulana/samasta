@extends('layouts.admin')

@section('title', 'Catat Stok Keluar')
@section('header', 'Catat Stok Keluar')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.bibits.index') }}" class="text-sm text-green-700 hover:underline">← Kembali ke Kelola Bibit</a>
    </div>

    <div class="max-w-3xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('admin.bibit-keluars.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <x-operasional-pelaksanaan-datetime
                name="tanggal_keluar"
                label="Tanggal & Waktu Keluar"
            />

            <div class="space-y-6">
                @include('admin.bibit_keluars._peruntukan_lokasi', ['batch' => null])
            </div>

            @include('admin.bibit_keluars._baris_bibit', ['batch' => null])

            @include('admin.partials.bibit-stok-foto-input')

            <div class="flex gap-3">
                <button type="submit"
                        class="rounded-lg bg-green-600 px-5 py-2 text-sm font-medium text-white hover:bg-green-700">
                    Simpan Stok Keluar
                </button>
                <a href="{{ route('admin.bibits.index') }}"
                   class="rounded-lg border border-gray-300 px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
