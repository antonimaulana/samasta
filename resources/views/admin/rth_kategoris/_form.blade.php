@php
    $kategori = $kategori ?? null;
@endphp

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <label for="nama" class="mb-1 block text-sm font-medium text-gray-700">Nama Kategori *</label>
        <input type="text" name="nama" id="nama"
               value="{{ old('nama', $kategori?->nama ?? '') }}"
               required
               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
    </div>

    <div>
        <label for="icon" class="mb-1 block text-sm font-medium text-gray-700">Icon (emoji) *</label>
        <input type="text" name="icon" id="icon"
               value="{{ old('icon', $kategori?->icon ?? '🌳') }}"
               required maxlength="10"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-xl focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
    </div>

    <div>
        <label for="luas" class="mb-1 block text-sm font-medium text-gray-700">Luas (m²) *</label>
        <input type="number" name="luas" id="luas" min="0" step="1"
               value="{{ old('luas', $kategori?->luas ?? 0) }}"
               required
               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
    </div>

    <div>
        <label for="lokasi" class="mb-1 block text-sm font-medium text-gray-700">Jumlah Lokasi *</label>
        <input type="number" name="lokasi" id="lokasi" min="0" step="1"
               value="{{ old('lokasi', $kategori?->lokasi ?? 0) }}"
               required
               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
    </div>

    <div>
        <label for="urutan" class="mb-1 block text-sm font-medium text-gray-700">Urutan Tampil</label>
        <input type="number" name="urutan" id="urutan" min="0" step="1"
               value="{{ old('urutan', $kategori?->urutan ?? 0) }}"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
    </div>

    <div class="flex items-end">
        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="is_published" value="1"
                   @checked(old('is_published', $kategori?->is_published ?? true))
                   class="rounded border-gray-300 text-green-600 focus:ring-green-500">
            Tampilkan di beranda
        </label>
    </div>

    <div class="md:col-span-2">
        <label for="ringkas" class="mb-1 block text-sm font-medium text-gray-700">Deskripsi Ringkas</label>
        <textarea name="ringkas" id="ringkas" rows="3"
                  class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">{{ old('ringkas', $kategori?->ringkas ?? '') }}</textarea>
    </div>
</div>

<div class="mt-6 flex gap-3">
    <button type="submit"
            class="rounded-lg bg-green-600 px-5 py-2 text-sm font-medium text-white hover:bg-green-700">
        Simpan
    </button>
    <a href="{{ route('admin.rth-kategoris.index') }}"
       class="rounded-lg border border-gray-300 px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
        Batal
    </a>
</div>
