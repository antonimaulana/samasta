@extends('layouts.admin')

@section('title', 'Edit Pesan Tama')
@section('header', 'Edit Pesan Tama')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.beranda-mascot-tips.index') }}" class="text-sm font-semibold text-green-700 hover:underline">← Daftar pesan Tama</a>
    </div>

    <p class="mb-4 text-sm text-gray-600">Bagian: <strong>{{ $tip->section_label }}</strong> (kunci: <code class="text-xs">{{ $tip->section_key }}</code>)</p>

    <div class="mb-6 flex items-end gap-4 rounded-2xl border border-gray-200 bg-white p-4">
        <img src="{{ asset($tip->image_asset) }}" alt="Preview Tama" class="h-32 w-auto object-contain">
        <p class="text-xs text-gray-500">Preview gambar pose saat ini.</p>
    </div>

    <form action="{{ route('admin.beranda-mascot-tips.update', $tip) }}"
          method="POST" class="mx-auto max-w-2xl space-y-5 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Judul bubble</label>
            <input type="text" name="title" value="{{ old('title', $tip->title) }}" required maxlength="160"
                   class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-green-500 focus:ring-1 focus:ring-green-500">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Isi pesan (persona Tama)</label>
            <textarea name="body" rows="7" required maxlength="2000"
                      class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-green-500 focus:ring-1 focus:ring-green-500">{{ old('body', $tip->body) }}</textarea>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Path gambar (relatif ke folder public)</label>
            <input type="text" name="image_asset" value="{{ old('image_asset', $tip->image_asset) }}" required
                   class="w-full rounded-xl border border-gray-200 px-4 py-2.5 font-mono text-xs focus:border-green-500 focus:ring-1 focus:ring-green-500"
                   placeholder="images/mascot/tama/hero.png">
            <p class="mt-1 text-xs text-gray-500">Ganti file di server atau upload manual ke <code>public/images/mascot/tama/</code>.</p>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Tombol tambahan (opsional)</label>
                <input type="text" name="cta_label" value="{{ old('cta_label', $tip->cta_label) }}" maxlength="80"
                       class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-green-500 focus:ring-1 focus:ring-green-500"
                       placeholder="Lihat statistik RTH">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">URL tombol</label>
                <input type="text" name="cta_url" value="{{ old('cta_url', $tip->cta_url) }}" maxlength="500"
                       class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-green-500 focus:ring-1 focus:ring-green-500"
                       placeholder="/rth-kota-batam">
            </div>
        </div>
        <label class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $tip->is_active))
                   class="rounded border-gray-300 text-green-600 focus:ring-green-500">
            <span class="text-sm font-medium text-gray-800">Tampilkan pop-up di beranda</span>
        </label>
        <button type="submit" class="w-full rounded-xl bg-green-600 py-3 text-sm font-bold text-white hover:bg-green-700">
            Simpan
        </button>
    </form>
@endsection
