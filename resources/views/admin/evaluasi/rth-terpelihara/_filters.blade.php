<div class="grid gap-4 sm:grid-cols-3">
    <div>
        <label for="fresh_days" class="mb-1 block text-sm font-medium text-gray-700">Ambang Pemeliharaan</label>
        <select name="fresh_days" id="fresh_days"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
            @foreach ($daftarFreshDays as $days)
                <option value="{{ $days }}" @selected($freshDays == $days)>{{ $days }} hari terakhir</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="kategori" class="mb-1 block text-sm font-medium text-gray-700">Kategori RTH</label>
        <select name="kategori" id="kategori"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
            <option value="">Semua Kategori</option>
            @foreach ($daftarKategori as $namaKategori)
                <option value="{{ $namaKategori }}" @selected($kategori === $namaKategori)>{{ $namaKategori }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="kecamatan_id" class="mb-1 block text-sm font-medium text-gray-700">Kecamatan</label>
        <select name="kecamatan_id" id="kecamatan_id"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
            <option value="">Semua Kecamatan</option>
            @foreach ($daftarKecamatan as $kecamatan)
                <option value="{{ $kecamatan->id }}" @selected((string) $kecamatan_id === (string) $kecamatan->id)>{{ $kecamatan->nama }}</option>
            @endforeach
        </select>
    </div>
</div>
