@extends('layouts.admin')

@section('title', 'Tambah DPA')
@section('header', 'Tambah DPA')

@section('content')
    <div class="max-w-2xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <p class="mb-4 text-sm text-gray-600">
            Tahun {{ $tahunAnggaran->tahun }} ·
            @if ($subKegiatan)
                {{ \App\Support\DpaMonitoring::subKegiatanLabel($subKegiatan) }}
            @else
                Pilih sub kegiatan
            @endif
        </p>

        <form action="{{ route('admin.dpa.dpas.store') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="dpa_tahun_anggaran_id" value="{{ $tahunAnggaran->id }}">

            <div>
                <label for="sub_kegiatan" class="mb-1 block text-sm font-medium text-gray-700">Sub Kegiatan *</label>
                <select name="sub_kegiatan" id="sub_kegiatan" required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                    <option value="">— Pilih —</option>
                    @foreach ($subKegiatanOptions as $option)
                        <option value="{{ $option['slug'] }}" @selected(old('sub_kegiatan', $subKegiatan) === $option['slug'])>
                            {{ $option['label'] }}
                        </option>
                    @endforeach
                </select>
                @error('sub_kegiatan')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="nama_dpa" class="mb-1 block text-sm font-medium text-gray-700">Nama DPA *</label>
                <input type="text" name="nama_dpa" id="nama_dpa" value="{{ old('nama_dpa') }}" required
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                @error('nama_dpa')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="nomor_dpa" class="mb-1 block text-sm font-medium text-gray-700">Nomor DPA</label>
                <input type="text" name="nomor_dpa" id="nomor_dpa" value="{{ old('nomor_dpa') }}"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
            </div>

            <div>
                <label for="keterangan" class="mb-1 block text-sm font-medium text-gray-700">Keterangan</label>
                <textarea name="keterangan" id="keterangan" rows="3"
                          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">{{ old('keterangan') }}</textarea>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">Simpan</button>
                <a href="{{ route('admin.dpa.tahun-anggarans.show', $tahunAnggaran) }}"
                   class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</a>
            </div>
        </form>
    </div>
@endsection
