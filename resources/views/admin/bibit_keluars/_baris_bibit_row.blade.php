@php
    $bibitId = old("items.{$index}.bibit_id", $row['bibit_id'] ?? '');
    $jumlah = old("items.{$index}.jumlah", $row['jumlah'] ?? '');
@endphp

<div data-bibit-keluar-row class="grid gap-3 rounded-lg border border-gray-200 bg-gray-50 p-3 md:grid-cols-[1fr_140px_auto] md:items-end">
    <div>
        <label class="mb-1 block text-xs font-medium text-gray-600">Bibit *</label>
        <select name="items[{{ $index }}][bibit_id]" data-bibit-select required
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
            <option value="">-- Pilih bibit --</option>
            @foreach ($bibits as $bibit)
                <option value="{{ $bibit->id }}"
                        data-stok="{{ $bibit->stok_tersedia }}"
                        @selected((string) $bibitId === (string) $bibit->id)>
                    {{ $bibit->namaDenganIlmiah() }} ({{ $bibit->jenis }}) — Stok: {{ $bibit->stok_tersedia }}
                </option>
            @endforeach
        </select>
        <p data-stok-hint class="mt-1 text-xs text-gray-500">Pilih bibit.</p>
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
