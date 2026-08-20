@php
    $item = $item ?? null;
@endphp

<div class="grid gap-6 md:grid-cols-2">
    <div class="md:col-span-2">
        <label for="nama" class="mb-1 block text-sm font-medium text-gray-700">Nama Alat/Armada *</label>
        <input type="text" name="nama" id="nama" required
               value="{{ old('nama', $item?->nama ?? '') }}"
               placeholder="Contoh: Mesin pemotong rumput, Dump Truck BP 1234 AB"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
        @error('nama')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="jenis" class="mb-1 block text-sm font-medium text-gray-700">Jenis *</label>
        <select name="jenis" id="jenis" required
                class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
            <option value="">Pilih jenis</option>
            @foreach (\App\Models\AlatSaranaOperasional::JENIS as $jenis)
                <option value="{{ $jenis }}" @selected(old('jenis', $item?->jenis ?? '') === $jenis)>{{ $jenis }}</option>
            @endforeach
        </select>
        @error('jenis')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="jumlah" class="mb-1 block text-sm font-medium text-gray-700">Jumlah *</label>
        <input type="number" name="jumlah" id="jumlah" min="1" max="99999" required
               value="{{ old('jumlah', $item?->jumlah ?? 1) }}"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
        @error('jumlah')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="peruntukan" class="mb-1 block text-sm font-medium text-gray-700">Peruntukan (Tim) *</label>
        <select name="peruntukan" id="peruntukan" required
                class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
            <option value="">Pilih tim</option>
            @foreach (\App\Models\AlatSaranaOperasional::timOptions() as $tim)
                <option value="{{ $tim }}" @selected(old('peruntukan', $item?->peruntukan ?? '') === $tim)>{{ $tim }}</option>
            @endforeach
        </select>
        @error('peruntukan')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="kondisi" class="mb-1 block text-sm font-medium text-gray-700">Kondisi *</label>
        <select name="kondisi" id="kondisi" required
                class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
            <option value="">Pilih kondisi</option>
            @foreach (\App\Models\AlatSaranaOperasional::KONDISI as $kondisi)
                <option value="{{ $kondisi }}" @selected(old('kondisi', $item?->kondisi ?? 'Baik') === $kondisi)>{{ $kondisi }}</option>
            @endforeach
        </select>
        @error('kondisi')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div id="armada-detail-fields" class="contents">
        <div>
            <label for="no_plat" class="mb-1 block text-sm font-medium text-gray-700">No. Plat</label>
            <input type="text" name="no_plat" id="no_plat"
                   value="{{ old('no_plat', $item?->no_plat ?? '') }}"
                   placeholder="BP 1234 AB"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
            @error('no_plat')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="sopir" class="mb-1 block text-sm font-medium text-gray-700">Sopir</label>
            <input type="text" name="sopir" id="sopir"
                   value="{{ old('sopir', $item?->sopir ?? '') }}"
                   placeholder="Nama sopir"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
            @error('sopir')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="md:col-span-2">
        <label for="keterangan" class="mb-1 block text-sm font-medium text-gray-700">Keterangan</label>
        <textarea name="keterangan" id="keterangan" rows="3"
                  placeholder="Catatan tambahan, lokasi penyimpanan, tahun perolehan, dll."
                  class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">{{ old('keterangan', $item?->keterangan ?? '') }}</textarea>
        @error('keterangan')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-6 flex gap-3">
    <button type="submit"
            class="rounded-lg bg-green-600 px-5 py-2 text-sm font-medium text-white hover:bg-green-700">
        Simpan
    </button>
    <a href="{{ route('admin.alat-sarana-operasionals.index') }}"
       class="rounded-lg border border-gray-300 px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
        Batal
    </a>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const jenisSelect = document.getElementById('jenis');
        const armadaFields = document.getElementById('armada-detail-fields');
        const armadaJenis = @json(\App\Models\AlatSaranaOperasional::ARMADA_JENIS);

        function toggleArmadaFields() {
            const show = armadaJenis.includes(jenisSelect?.value ?? '');
            armadaFields?.querySelectorAll('#no_plat, #sopir').forEach(function (input) {
                input.required = show;
                input.closest('div')?.classList.toggle('hidden', ! show);
            });
        }

        jenisSelect?.addEventListener('change', toggleArmadaFields);
        toggleArmadaFields();
    });
</script>
@endpush
