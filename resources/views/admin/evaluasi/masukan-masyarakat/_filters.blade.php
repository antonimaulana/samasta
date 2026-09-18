<div class="grid gap-4 sm:grid-cols-3">
    <div>
        <label for="jenis_aduan" class="mb-1 block text-sm font-medium text-gray-700">Jenis Aduan</label>
        <select name="jenis_aduan" id="jenis_aduan"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500">
            <option value="">Semua Jenis</option>
            @foreach ($daftarJenisAduan as $jenis)
                <option value="{{ $jenis }}" @selected($jenisAduan === $jenis)>{{ $jenis }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="status" class="mb-1 block text-sm font-medium text-gray-700">Status Aduan</label>
        <select name="status" id="status"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500">
            <option value="">Semua Status</option>
            @foreach ($daftarStatus as $item)
                <option value="{{ $item }}" @selected($status === $item)>{{ $item }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="kategori_survey" class="mb-1 block text-sm font-medium text-gray-700">Kategori Survey</label>
        <select name="kategori_survey" id="kategori_survey"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500">
            <option value="">Semua Kategori</option>
            @foreach ($daftarKategoriSurvey as $kategori)
                <option value="{{ $kategori }}" @selected($kategoriSurvey === $kategori)>{{ $kategori }}</option>
            @endforeach
        </select>
    </div>
</div>
