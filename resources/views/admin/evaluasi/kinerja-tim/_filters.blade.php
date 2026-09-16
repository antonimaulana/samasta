<div>
    <label for="tim" class="mb-1 block text-sm font-medium text-gray-700">Tim Pelaksana</label>
    <select name="tim" id="tim"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
        <option value="">Semua Tim</option>
        @foreach ($daftarTim as $namaTim)
            <option value="{{ $namaTim }}" @selected($tim === $namaTim)>{{ $namaTim }}</option>
        @endforeach
    </select>
</div>
