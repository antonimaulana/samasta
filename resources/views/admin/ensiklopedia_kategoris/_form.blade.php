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
        <label for="slug" class="mb-1 block text-sm font-medium text-gray-700">Slug URL</label>
        <input type="text" name="slug" id="slug"
               value="{{ old('slug', $kategori?->slug ?? '') }}"
               placeholder="Otomatis dari nama jika kosong"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 font-mono text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
        <p class="mt-1 text-xs text-gray-500">Hanya huruf kecil, angka, dan tanda hubung. Contoh: program-pemerintah</p>
    </div>

    <div>
        <label for="icon" class="mb-1 block text-sm font-medium text-gray-700">Icon (emoji) *</label>
        <input type="text" name="icon" id="icon"
               value="{{ old('icon', $kategori?->icon ?? '📚') }}"
               required maxlength="10"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-xl focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
    </div>

    <div>
        <label for="urutan" class="mb-1 block text-sm font-medium text-gray-700">Urutan Tampil</label>
        <input type="number" name="urutan" id="urutan" min="0" step="1"
               value="{{ old('urutan', $kategori?->urutan ?? 0) }}"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
        <p class="mt-1 text-xs text-gray-500">Angka lebih kecil ditampilkan lebih dulu.</p>
    </div>

    <div class="md:col-span-2">
        <label for="deskripsi" class="mb-1 block text-sm font-medium text-gray-700">Deskripsi</label>
        <textarea name="deskripsi" id="deskripsi" rows="3"
                  class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">{{ old('deskripsi', $kategori?->deskripsi ?? '') }}</textarea>
    </div>
</div>

<div class="mt-6 flex gap-3">
    <button type="submit"
            class="rounded-lg bg-green-600 px-5 py-2 text-sm font-medium text-white hover:bg-green-700">
        Simpan
    </button>
    <a href="{{ route('admin.ensiklopedia-kategoris.index') }}"
       class="rounded-lg border border-gray-300 px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
        Batal
    </a>
</div>
