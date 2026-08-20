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

            <div>
                <label for="bibit_id" class="mb-1 block text-sm font-medium text-gray-700">Pilih Bibit *</label>
                <select name="bibit_id" id="bibit_id" required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                    <option value="">-- Pilih bibit --</option>
                    @foreach ($bibits as $bibit)
                        <option value="{{ $bibit->id }}"
                                data-stok="{{ $bibit->stok_tersedia }}"
                                @selected(old('bibit_id', request('bibit_id')) == $bibit->id)>
                            {{ $bibit->namaDenganIlmiah() }} ({{ $bibit->jenis }}) — Stok: {{ $bibit->stok_tersedia }}
                        </option>
                    @endforeach
                </select>
                <p id="stok-info" class="mt-1 text-xs text-gray-500">Pilih bibit untuk melihat stok tersedia.</p>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <label for="jumlah" class="mb-1 block text-sm font-medium text-gray-700">Jumlah Keluar *</label>
                    <input type="number" name="jumlah" id="jumlah" min="1"
                           value="{{ old('jumlah') }}"
                           required
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                </div>

                <div>
                    <label for="tanggal_keluar" class="mb-1 block text-sm font-medium text-gray-700">Tanggal Keluar *</label>
                    <input type="date" name="tanggal_keluar" id="tanggal_keluar"
                           value="{{ old('tanggal_keluar', now()->format('Y-m-d')) }}"
                           required
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                </div>
            </div>

            <div class="space-y-6">
                @include('admin.bibit_keluars._peruntukan_lokasi', ['keluar' => null])
            </div>

            <div>
                <label for="foto" class="mb-1 block text-sm font-medium text-gray-700">Foto Bukti *</label>
                <input type="file" name="foto" id="foto" accept="image/*" required
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-green-50 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-green-700 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                <p class="mt-1 text-xs text-gray-500">Format JPG/PNG, maks. 4 MB.</p>
            </div>

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

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const bibitSelect = document.getElementById('bibit_id');
            const jumlahInput = document.getElementById('jumlah');
            const stokInfo = document.getElementById('stok-info');

            function updateStokInfo() {
                const selected = bibitSelect.options[bibitSelect.selectedIndex];
                const stok = selected?.dataset?.stok;

                if (!stok) {
                    stokInfo.textContent = 'Pilih bibit untuk melihat stok tersedia.';
                    jumlahInput.removeAttribute('max');
                    return;
                }

                stokInfo.textContent = 'Stok tersedia saat ini: ' + stok + ' bibit.';
                jumlahInput.max = stok;
            }

            bibitSelect.addEventListener('change', updateStokInfo);
            updateStokInfo();
        });
    </script>
@endpush
