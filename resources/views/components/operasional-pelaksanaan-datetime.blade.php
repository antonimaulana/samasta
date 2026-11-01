@props([
    'name' => 'tanggal',
    'value' => null,
    'label' => 'Tanggal & Waktu Pelaksanaan',
    'required' => true,
    'min' => null,
    'max' => null,
    'variant' => 'admin',
    'blankInitial' => false,
])

@php
    $raw = old($name);
    if (filled($raw)) {
        $inputValue = \App\Support\OperasionalPelaksanaanTime::inputValue($raw);
    } elseif ($blankInitial) {
        $inputValue = '';
    } elseif (filled($value)) {
        $inputValue = \App\Support\OperasionalPelaksanaanTime::inputValue($value);
    } else {
        $inputValue = \App\Support\OperasionalPelaksanaanTime::inputValue(now());
    }
    $isLapangan = $variant === 'lapangan';
    $inputClass = $isLapangan
        ? 'lapangan-field-input'
        : 'w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500';
@endphp

<div>
    @if ($isLapangan)
        <x-lapangan.field-label :for="$name" :required="$required">{{ $label }}</x-lapangan.field-label>
    @else
        <label for="{{ $name }}" class="mb-1 block text-sm font-medium text-gray-700">{{ $label }} @if ($required)<span class="text-red-600">*</span>@endif</label>
    @endif
    <input type="datetime-local"
           name="{{ $name }}"
           id="{{ $name }}"
           step="60"
           value="{{ $inputValue }}"
           @if ($min) min="{{ $min }}" @endif
           @if ($max) max="{{ $max }}" @endif
           @if ($required) required @endif
           class="{{ $inputClass }}">
    @error($name)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
