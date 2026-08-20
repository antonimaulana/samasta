@extends('layouts.admin')

@section('title', 'Edit DPA')
@section('header', 'Edit DPA')

@section('content')
    <div class="max-w-2xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('admin.dpa.dpas.update', $dpa) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="sub_kegiatan" class="mb-1 block text-sm font-medium text-gray-700">Sub Kegiatan *</label>
                <select name="sub_kegiatan" id="sub_kegiatan" required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                    @foreach ($subKegiatanOptions as $option)
                        <option value="{{ $option['slug'] }}" @selected(old('sub_kegiatan', $dpa->sub_kegiatan) === $option['slug'])>
                            {{ $option['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="nama_dpa" class="mb-1 block text-sm font-medium text-gray-700">Nama DPA *</label>
                <input type="text" name="nama_dpa" id="nama_dpa" value="{{ old('nama_dpa', $dpa->nama_dpa) }}" required
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
            </div>

            <div>
                <label for="nomor_dpa" class="mb-1 block text-sm font-medium text-gray-700">Nomor DPA</label>
                <input type="text" name="nomor_dpa" id="nomor_dpa" value="{{ old('nomor_dpa', $dpa->nomor_dpa) }}"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
            </div>

            <div>
                <label for="keterangan" class="mb-1 block text-sm font-medium text-gray-700">Keterangan</label>
                <textarea name="keterangan" id="keterangan" rows="3"
                          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">{{ old('keterangan', $dpa->keterangan) }}</textarea>
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">Simpan</button>
                <a href="{{ route('admin.dpa.dpas.show', $dpa) }}"
                   class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</a>
            </div>
        </form>

        <form action="{{ route('admin.dpa.dpas.destroy', $dpa) }}" method="POST" class="mt-8 border-t border-gray-200 pt-6"
              onsubmit="return confirm('Hapus DPA beserta semua paket pekerjaannya?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50">
                Hapus DPA
            </button>
        </form>
    </div>
@endsection
