<div>
    <label for="peruntukan" class="mb-1 block text-sm font-medium text-gray-700">Peruntukan *</label>
    <select name="peruntukan" id="peruntukan" required
            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
        <option value="">-- Pilih peruntukan --</option>
        @foreach (\App\Models\BibitKeluar::PERUNTUKAN as $peruntukan)
            <option value="{{ $peruntukan }}" @selected(old('peruntukan', $keluar?->peruntukan) === $peruntukan)>
                {{ $peruntukan }}
            </option>
        @endforeach
    </select>
</div>

<div>
    <label for="taman_id_search" class="mb-1 block text-sm font-medium text-gray-700">Lokasi (Taman) *</label>
    <x-admin.taman-select
        :tamans="$tamans ?? collect()"
        :selected="old('taman_id', $keluar?->taman_id)"
        :required="true"
    />
</div>
