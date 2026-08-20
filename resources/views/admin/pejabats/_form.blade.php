@php
    $pejabat = $pejabat ?? null;
@endphp

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <label for="nama" class="mb-1 block text-sm font-medium text-gray-700">Nama *</label>
        <input type="text" name="nama" id="nama"
               value="{{ old('nama', $pejabat?->nama ?? '') }}"
               required
               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
    </div>

    <div>
        <label for="jabatan" class="mb-1 block text-sm font-medium text-gray-700">Jabatan *</label>
        <input type="text" name="jabatan" id="jabatan"
               value="{{ old('jabatan', $pejabat?->jabatan ?? '') }}"
               required
               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
    </div>

    <div class="md:col-span-2">
        <label for="photo" class="mb-1 block text-sm font-medium text-gray-700">Foto Pejabat</label>
        @if ($pejabat?->image_path && file_exists(public_path($pejabat->image_path)))
            <div class="mb-3">
                <img src="{{ asset($pejabat->image_path) }}" alt="{{ $pejabat->nama }}"
                     class="h-32 rounded-lg object-contain ring-1 ring-gray-200">
            </div>
        @endif
        <input type="file" name="photo" id="photo" accept="image/*"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
        <p class="mt-1 text-xs text-gray-500">Format JPG/PNG. Maks. 2 MB. Kosongkan jika tidak ingin mengubah foto.</p>
    </div>

    <div>
        <label for="accent_bg" class="mb-1 block text-sm font-medium text-gray-700">Warna Latar (Tailwind) *</label>
        <input type="text" name="accent_bg" id="accent_bg"
               value="{{ old('accent_bg', $pejabat?->accent_bg ?? 'bg-emerald-100') }}"
               required
               placeholder="bg-emerald-100"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 font-mono text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
    </div>

    <div>
        <label for="accent_ring" class="mb-1 block text-sm font-medium text-gray-700">Warna Ring (Tailwind) *</label>
        <input type="text" name="accent_ring" id="accent_ring"
               value="{{ old('accent_ring', $pejabat?->accent_ring ?? 'ring-emerald-200/60') }}"
               required
               placeholder="ring-emerald-200/60"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 font-mono text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
    </div>

    <div>
        <label for="photo_class" class="mb-1 block text-sm font-medium text-gray-700">Kelas CSS Foto</label>
        <input type="text" name="photo_class" id="photo_class"
               value="{{ old('photo_class', $pejabat?->photo_class ?? '') }}"
               placeholder="h-full w-auto max-w-[92%] object-contain object-bottom"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 font-mono text-xs focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
    </div>

    <div>
        <label for="photo_frame_class" class="mb-1 block text-sm font-medium text-gray-700">Kelas CSS Frame Foto</label>
        <input type="text" name="photo_frame_class" id="photo_frame_class"
               value="{{ old('photo_frame_class', $pejabat?->photo_frame_class ?? '') }}"
               placeholder="items-end justify-center"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 font-mono text-xs focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
    </div>

    <div>
        <label for="urutan" class="mb-1 block text-sm font-medium text-gray-700">Urutan Tampil</label>
        <input type="number" name="urutan" id="urutan" min="0" step="1"
               value="{{ old('urutan', $pejabat?->urutan ?? 0) }}"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
    </div>

    <div class="flex items-end">
        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="is_published" value="1"
                   @checked(old('is_published', $pejabat?->is_published ?? true))
                   class="rounded border-gray-300 text-green-600 focus:ring-green-500">
            Tampilkan di beranda
        </label>
    </div>
</div>

<div class="mt-6 flex gap-3">
    <button type="submit"
            class="rounded-lg bg-green-600 px-5 py-2 text-sm font-medium text-white hover:bg-green-700">
        Simpan
    </button>
    <a href="{{ route('admin.pejabats.index') }}"
       class="rounded-lg border border-gray-300 px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
        Batal
    </a>
</div>
