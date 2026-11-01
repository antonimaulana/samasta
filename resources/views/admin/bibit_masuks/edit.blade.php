@extends('layouts.admin')

@section('title', 'Edit Stok Masuk')
@section('header', 'Edit Stok Masuk')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.bibit-masuks.show', $batch) }}" class="text-sm text-green-700 hover:underline">← Kembali ke detail</a>
    </div>

    <div class="max-w-3xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('admin.bibit-masuks.update', $batch) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <x-operasional-pelaksanaan-datetime
                name="tanggal_masuk"
                :value="$batch->tanggal_masuk"
                label="Tanggal & Waktu Masuk"
            />

            @include('admin.bibit_masuks._baris_bibit')

            @include('admin.partials.bibit-stok-foto-input', [
                'required' => false,
                'existingCount' => $batch->buktiFotos->count(),
            ])

            @if ($batch->hasFotoBukti())
                <p class="text-xs text-gray-500">Unggah foto baru untuk menambah bukti; foto lama tetap disimpan.</p>
            @endif

            <div class="flex gap-3">
                <button type="submit"
                        class="rounded-lg bg-teal-600 px-5 py-2 text-sm font-medium text-white hover:bg-teal-700">
                    Simpan Perubahan
                </button>
                <a href="{{ route('admin.bibit-masuks.show', $batch) }}"
                   class="rounded-lg border border-gray-300 px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
