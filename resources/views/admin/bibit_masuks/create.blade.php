@extends('layouts.admin')

@section('title', 'Catat Stok Masuk')
@section('header', 'Catat Stok Masuk')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.bibits.index') }}" class="text-sm text-green-700 hover:underline">← Kembali ke Kelola Bibit</a>
    </div>

    <div class="max-w-3xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('admin.bibit-masuks.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <x-operasional-pelaksanaan-datetime
                name="tanggal_masuk"
                label="Tanggal & Waktu Masuk"
            />

            @include('admin.bibit_masuks._baris_bibit', ['batch' => null])

            @include('admin.partials.bibit-stok-foto-input')

            <div class="flex gap-3">
                <button type="submit"
                        class="rounded-lg bg-teal-600 px-5 py-2 text-sm font-medium text-white hover:bg-teal-700">
                    Simpan Stok Masuk
                </button>
                <a href="{{ route('admin.bibits.index') }}"
                   class="rounded-lg border border-gray-300 px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
