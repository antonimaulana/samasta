@php
    $bibit = $bibit ?? null;
@endphp

<div class="mb-4 rounded-lg border border-teal-100 bg-teal-50 px-4 py-3 text-sm text-teal-800">
    Daftarkan jenis tanaman di sini. Penambahan stok dilakukan melalui tombol <strong>Stok Masuk</strong> di halaman Kelola Bibit.
</div>

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <label for="nama_tanaman" class="mb-1 block text-sm font-medium text-gray-700">Nama Tanaman *</label>
        <input type="text" name="nama_tanaman" id="nama_tanaman"
               value="{{ old('nama_tanaman', $bibit?->nama_tanaman ?? '') }}"
               required
               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
    </div>

    <div>
        <label for="nama_ilmiah" class="mb-1 block text-sm font-medium text-gray-700">Nama Ilmiah</label>
        <input type="text" name="nama_ilmiah" id="nama_ilmiah"
               value="{{ old('nama_ilmiah', $bibit?->nama_ilmiah ?? '') }}"
               placeholder="Contoh: Swietenia macrophylla"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 italic focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
    </div>

    <div>
        <label for="jenis" class="mb-1 block text-sm font-medium text-gray-700">Jenis *</label>
        <select name="jenis" id="jenis" required
                class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
            <option value="">Pilih jenis</option>
            @foreach (\App\Models\Bibit::JENIS as $jenis)
                <option value="{{ $jenis }}" @selected(old('jenis', $bibit?->jenis ?? '') === $jenis)>{{ $jenis }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="mt-6 flex gap-3">
    <button type="submit"
            class="rounded-lg bg-green-600 px-5 py-2 text-sm font-medium text-white hover:bg-green-700">
        Simpan
    </button>
    <a href="{{ route('admin.bibits.index') }}"
       class="rounded-lg border border-gray-300 px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
        Batal
    </a>
</div>
