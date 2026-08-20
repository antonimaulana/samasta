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

            <div>
                <label for="bibit_id" class="mb-1 block text-sm font-medium text-gray-700">Pilih Bibit *</label>
                <select name="bibit_id" id="bibit_id" required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                    <option value="">-- Pilih bibit --</option>
                    @foreach ($bibits as $bibit)
                        <option value="{{ $bibit->id }}" @selected(old('bibit_id', request('bibit_id')) == $bibit->id)>
                            {{ $bibit->namaDenganIlmiah() }} ({{ $bibit->jenis }}) — Stok: {{ $bibit->stok_tersedia }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <label for="jumlah" class="mb-1 block text-sm font-medium text-gray-700">Jumlah Masuk *</label>
                    <input type="number" name="jumlah" id="jumlah" min="1"
                           value="{{ old('jumlah') }}"
                           required
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                </div>

                <div>
                    <label for="tanggal_masuk" class="mb-1 block text-sm font-medium text-gray-700">Tanggal Masuk *</label>
                    <input type="date" name="tanggal_masuk" id="tanggal_masuk"
                           value="{{ old('tanggal_masuk', now()->format('Y-m-d')) }}"
                           required
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                </div>
            </div>

            <div>
                <label for="sumber" class="mb-1 block text-sm font-medium text-gray-700">Sumber *</label>
                <select name="sumber" id="sumber" required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                    <option value="">-- Pilih sumber --</option>
                    @foreach (\App\Models\Bibit::SUMBER as $sumber)
                        <option value="{{ $sumber }}" @selected(old('sumber') === $sumber)>{{ $sumber }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="flex items-center gap-3 rounded-lg border border-green-100 bg-green-50 px-4 py-3">
                    <input type="checkbox" name="status_siap_tanam" value="1"
                           @checked(old('status_siap_tanam'))
                           class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                    <span>
                        <span class="block text-sm font-medium text-gray-800">Batch Siap Tanam</span>
                        <span class="text-xs text-gray-500">Centang jika batch stok masuk ini sudah layak ditanam.</span>
                    </span>
                </label>
            </div>

            <div>
                <label for="foto" class="mb-1 block text-sm font-medium text-gray-700">Foto Bukti *</label>
                <input type="file" name="foto" id="foto" accept="image/*" required
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-green-50 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-green-700 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                <p class="mt-1 text-xs text-gray-500">Format JPG/PNG, maks. 4 MB.</p>
            </div>

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
