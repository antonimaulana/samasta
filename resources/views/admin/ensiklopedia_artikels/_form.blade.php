@php
    $artikel = $artikel ?? null;
@endphp

<div class="grid gap-6 md:grid-cols-2">
    <div class="md:col-span-2">
        <label for="ensiklopedia_kategori_id" class="mb-1 block text-sm font-medium text-gray-700">Kategori *</label>
        <select name="ensiklopedia_kategori_id" id="ensiklopedia_kategori_id" required
                class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
            <option value="">Pilih kategori</option>
            @foreach ($kategoris as $kat)
                <option value="{{ $kat->id }}"
                    @selected(old('ensiklopedia_kategori_id', $artikel?->ensiklopedia_kategori_id ?? request('kategori')) == $kat->id)>
                    {{ $kat->icon }} {{ $kat->nama }}
                </option>
            @endforeach
        </select>
        @if ($kategoris->isEmpty())
            <p class="mt-1 text-xs text-amber-600">
                Belum ada kategori. <a href="{{ route('admin.ensiklopedia-kategoris.create') }}" class="underline">Buat kategori dulu</a>.
            </p>
        @endif
    </div>

    <div>
        <label for="judul" class="mb-1 block text-sm font-medium text-gray-700">Judul Artikel *</label>
        <input type="text" name="judul" id="judul"
               value="{{ old('judul', $artikel?->judul ?? '') }}"
               required
               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
    </div>

    <div>
        <label for="slug" class="mb-1 block text-sm font-medium text-gray-700">Slug URL</label>
        <input type="text" name="slug" id="slug"
               value="{{ old('slug', $artikel?->slug ?? '') }}"
               placeholder="Otomatis dari judul jika kosong"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 font-mono text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
    </div>

    <div>
        <label for="icon" class="mb-1 block text-sm font-medium text-gray-700">Icon (emoji) *</label>
        <input type="text" name="icon" id="icon"
               value="{{ old('icon', $artikel?->icon ?? '📄') }}"
               required maxlength="10"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-xl focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
    </div>

    <div>
        <label for="urutan" class="mb-1 block text-sm font-medium text-gray-700">Urutan Tampil</label>
        <input type="number" name="urutan" id="urutan" min="0" step="1"
               value="{{ old('urutan', $artikel?->urutan ?? 0) }}"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
    </div>

    <div class="md:col-span-2">
        <label for="ringkas" class="mb-1 block text-sm font-medium text-gray-700">Ringkasan *</label>
        <textarea name="ringkas" id="ringkas" rows="2" required maxlength="500"
                  class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">{{ old('ringkas', $artikel?->ringkas ?? '') }}</textarea>
        <p class="mt-1 text-xs text-gray-500">Tampil di kartu daftar artikel. Maks. 500 karakter.</p>
    </div>

    <div class="md:col-span-2">
        <label for="konten" class="mb-1 block text-sm font-medium text-gray-700">Konten Artikel *</label>
        <textarea name="konten" id="konten" rows="12" required
                  class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">{{ old('konten', $artikel?->konten ?? '') }}</textarea>
        <p class="mt-1 text-xs text-gray-500">Pisahkan paragraf dengan baris kosong (Enter dua kali).</p>
    </div>

    <div class="md:col-span-2">
        <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="is_published" value="1"
                   @checked(old('is_published', $artikel?->is_published ?? true))
                   class="rounded border-gray-300 text-green-600 focus:ring-green-500">
            <span class="text-sm font-medium text-gray-700">Tampilkan di situs publik</span>
        </label>
    </div>
</div>

<div class="mt-6 flex gap-3">
    <button type="submit"
            class="rounded-lg bg-green-600 px-5 py-2 text-sm font-medium text-white hover:bg-green-700">
        Simpan
    </button>
    <a href="{{ route('admin.ensiklopedia-artikels.index') }}"
       class="rounded-lg border border-gray-300 px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
        Batal
    </a>
</div>
