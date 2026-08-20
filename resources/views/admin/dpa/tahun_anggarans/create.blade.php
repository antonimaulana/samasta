@extends('layouts.admin')

@section('title', 'Tambah Tahun Anggaran')
@section('header', 'Tambah Tahun Anggaran')

@section('content')
    <div class="max-w-xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('admin.dpa.tahun-anggarans.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="tahun" class="mb-1 block text-sm font-medium text-gray-700">Tahun *</label>
                <input type="number" name="tahun" id="tahun" value="{{ old('tahun', date('Y')) }}" required min="2000" max="2100"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                @error('tahun')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="keterangan" class="mb-1 block text-sm font-medium text-gray-700">Keterangan</label>
                <textarea name="keterangan" id="keterangan" rows="3"
                          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">{{ old('keterangan') }}</textarea>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">Simpan</button>
                <a href="{{ route('admin.dpa.dashboard') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</a>
            </div>
        </form>
    </div>
@endsection
