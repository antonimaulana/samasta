@php
    $bibitId = old("items.{$index}.bibit_id", $row['bibit_id'] ?? '');
    $jumlah = old("items.{$index}.jumlah", $row['jumlah'] ?? '');
    $sumber = old("items.{$index}.sumber", $row['sumber'] ?? '');
    $siapTanam = filter_var(old("items.{$index}.status_siap_tanam", $row['status_siap_tanam'] ?? false), FILTER_VALIDATE_BOOLEAN);
@endphp

<div data-bibit-masuk-row class="space-y-3 rounded-lg border border-gray-200 bg-gray-50 p-3">
    <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_7rem_auto] md:items-end">
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-600">Bibit *</label>
            <select name="items[{{ $index }}][bibit_id]" data-bibit-select required
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                <option value="">-- Pilih bibit --</option>
                @foreach ($bibits as $bibit)
                    <option value="{{ $bibit->id }}" @selected((string) $bibitId === (string) $bibit->id)>
                        {{ $bibit->namaDenganIlmiah() }} ({{ $bibit->jenis }}) — Stok: {{ $bibit->stok_tersedia }}
                    </option>
                @endforeach
            </select>
            @error("items.{$index}.bibit_id")
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-600">Jumlah *</label>
            <input type="number" name="items[{{ $index }}][jumlah]" data-jumlah-input min="1"
                   value="{{ $jumlah }}"
                   required
                   class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
            @error("items.{$index}.jumlah")
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="flex md:justify-end">
            <button type="button" data-remove-row
                    @class(['rounded-lg border border-red-200 px-3 py-2 text-xs font-medium text-red-700 hover:bg-red-50', 'hidden' => ! ($canRemove ?? true)])>
                Hapus baris
            </button>
        </div>
    </div>
    <div class="grid gap-3 sm:grid-cols-2">
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-600">Sumber *</label>
            <select name="items[{{ $index }}][sumber]" required
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                <option value="">-- Pilih sumber --</option>
                @foreach (\App\Models\Bibit::SUMBER as $opt)
                    <option value="{{ $opt }}" @selected($sumber === $opt)>{{ $opt }}</option>
                @endforeach
            </select>
            @error("items.{$index}.sumber")
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="flex items-end">
            <label class="flex w-full items-center gap-3 rounded-lg border border-green-100 bg-white px-3 py-2.5">
                <input type="checkbox" name="items[{{ $index }}][status_siap_tanam]" value="1"
                       @checked($siapTanam)
                       class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                <span class="text-sm text-gray-800">Batch siap tanam</span>
            </label>
        </div>
    </div>
</div>
