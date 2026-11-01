@props([
    'required' => true,
    'existingCount' => 0,
])

<div>
    <label for="fotos" class="mb-1 block text-sm font-medium text-gray-700">
        Foto Bukti @if ($required && $existingCount === 0)<span class="text-red-600">*</span>@endif
    </label>
    <input type="file" name="fotos[]" id="fotos" accept="image/*" multiple
           @if ($required && $existingCount === 0) required @endif
           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-green-50 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-green-700 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
    <p class="mt-1 text-xs text-gray-500">Format JPG/PNG, maks. 4 MB per file. Dapat memilih lebih dari satu foto.</p>
    @error('fotos')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
    @error('fotos.*')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
