@php
    $sumber = old('sumber', $batch?->sumber ?? $batch?->items->first()?->sumber ?? '');
    $siapTanam = filter_var(old('status_siap_tanam', $batch?->status_siap_tanam ?? $batch?->items->first()?->status_siap_tanam ?? false), FILTER_VALIDATE_BOOLEAN);
@endphp

<div>
    <label for="sumber" class="mb-1 block text-sm font-medium text-gray-700">Sumber *</label>
    <select name="sumber" id="sumber" required
            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
        <option value="">-- Pilih sumber --</option>
        @foreach (\App\Models\Bibit::SUMBER as $opt)
            <option value="{{ $opt }}" @selected($sumber === $opt)>{{ $opt }}</option>
        @endforeach
    </select>
    @error('sumber')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label class="flex items-center gap-3 rounded-lg border border-green-100 bg-green-50 px-4 py-3">
        <input type="checkbox" name="status_siap_tanam" value="1"
               @checked($siapTanam)
               class="rounded border-gray-300 text-green-600 focus:ring-green-500">
        <span>
            <span class="block text-sm font-medium text-gray-800">Batch siap tanam</span>
            <span class="text-xs text-gray-500">Berlaku untuk seluruh bibit dalam transaksi ini.</span>
        </span>
    </label>
</div>
