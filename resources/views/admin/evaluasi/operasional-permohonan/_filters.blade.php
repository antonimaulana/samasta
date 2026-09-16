<div class="grid gap-4 sm:grid-cols-3">
    <div>
        <label for="jenis_layanan" class="mb-1 block text-sm font-medium text-gray-700">Jenis Layanan</label>
        <select name="jenis_layanan" id="jenis_layanan"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-orange-500 focus:outline-none focus:ring-1 focus:ring-orange-500">
            <option value="">Semua Jenis</option>
            @foreach ($daftarJenisLayanan as $jenis)
                <option value="{{ $jenis }}" @selected($jenisLayanan === $jenis)>{{ $jenis }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="status" class="mb-1 block text-sm font-medium text-gray-700">Status</label>
        <select name="status" id="status"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-orange-500 focus:outline-none focus:ring-1 focus:ring-orange-500">
            <option value="">Semua Status</option>
            @foreach ($daftarStatus as $item)
                <option value="{{ $item }}" @selected($status === $item)>{{ $item }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="pelaksana" class="mb-1 block text-sm font-medium text-gray-700">Tim Pelaksana</label>
        <select name="pelaksana" id="pelaksana"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-orange-500 focus:outline-none focus:ring-1 focus:ring-orange-500">
            <option value="">Semua Tim</option>
            @foreach ($daftarPelaksana as $tim)
                <option value="{{ $tim }}" @selected($pelaksana === $tim)>{{ $tim }}</option>
            @endforeach
        </select>
    </div>
</div>
