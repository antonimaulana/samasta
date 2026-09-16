<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label for="tim" class="mb-1 block text-sm font-medium text-gray-700">Tim Pelaksana</label>
        <select name="tim" id="tim"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
            <option value="">Semua Tim</option>
            @foreach ($daftarTim as $namaTim)
                <option value="{{ $namaTim }}" @selected($tim === $namaTim)>{{ $namaTim }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="kategori" class="mb-1 block text-sm font-medium text-gray-700">Kategori Taman</label>
        <select name="kategori" id="kategori"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
            <option value="">Semua Kategori</option>
            @foreach ($daftarKategori as $namaKategori)
                <option value="{{ $namaKategori }}" @selected($kategori === $namaKategori)>{{ $namaKategori }}</option>
            @endforeach
        </select>
    </div>
</div>
