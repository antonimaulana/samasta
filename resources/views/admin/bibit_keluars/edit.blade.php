@extends('layouts.admin')

@section('title', 'Edit Stok Keluar')
@section('header', 'Edit Stok Keluar')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.bibits.index') }}" class="text-sm text-green-700 hover:underline">← Kembali ke Kelola Bibit</a>
    </div>

    <div class="max-w-3xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="mb-6 rounded-lg border border-gray-100 bg-gray-50 px-4 py-3">
            <p class="text-xs font-semibold uppercase text-gray-500">Bibit</p>
            <p class="mt-1 font-medium text-gray-900">
                <x-admin.bibit-nama :bibit="$keluar->bibit" /> ({{ $keluar->bibit->jenis }})
            </p>
            <p class="mt-1 text-xs text-gray-500">Stok tersedia saat ini: {{ $keluar->bibit->stok_tersedia }} bibit</p>
        </div>

        <form action="{{ route('admin.bibit-keluars.update', $keluar) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <label for="jumlah" class="mb-1 block text-sm font-medium text-gray-700">Jumlah Keluar *</label>
                    <input type="number" name="jumlah" id="jumlah" min="1"
                           value="{{ old('jumlah', $keluar->jumlah) }}"
                           required
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                </div>

                <div>
                    <label for="tanggal_keluar" class="mb-1 block text-sm font-medium text-gray-700">Tanggal Keluar *</label>
                    <input type="date" name="tanggal_keluar" id="tanggal_keluar"
                           value="{{ old('tanggal_keluar', $keluar->tanggal_keluar->format('Y-m-d')) }}"
                           required
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                </div>
            </div>

            <div class="space-y-6">
                @include('admin.bibit_keluars._peruntukan_lokasi')
            </div>

            <div>
                <label for="foto" class="mb-1 block text-sm font-medium text-gray-700">Foto Bukti</label>
                @if ($keluar->foto_url)
                    <a href="{{ $keluar->foto_url }}" target="_blank" rel="noopener" class="mb-2 block">
                        <img src="{{ $keluar->foto_url }}" alt="Foto saat ini" class="max-h-40 rounded-lg border object-cover">
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
                <a href="{{ route('admin.bibits.index') }}"
                   class="rounded-lg border border-gray-300 px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const jumlahInput = document.getElementById('jumlah');
            const stokTersedia = {{ $keluar->bibit->stok_tersedia }};
            const jumlahAsal = {{ $keluar->jumlah }};

            jumlahInput.max = stokTersedia + jumlahAsal;
        });
    </script>
@endpush
