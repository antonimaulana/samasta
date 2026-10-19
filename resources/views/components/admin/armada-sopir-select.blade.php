@props([
    'name',
    'selected' => '',
    'required' => false,
    'inputClass' => 'w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500',
    'selectClass' => null,
])

@php
    $selectedValue = (string) $selected;
    $options = \App\Support\ArmadaSopirOptions::names();
    $class = $selectClass ?? $inputClass;
@endphp

<select
    name="{{ $name }}"
    @if ($required) required @endif
    {{ $attributes->merge(['class' => $class.' armada-sopir'])->except(['selected', 'required', 'inputClass', 'selectClass']) }}
>
    <option value="">Pilih sopir</option>
    @foreach ($options as $sopir)
        <option value="{{ $sopir }}" @selected($selectedValue === $sopir)>{{ $sopir }}</option>
    @endforeach
</select>
@if ($options === [])
    <p class="mt-1 text-xs text-amber-700">
        Belum ada anggota Tim Armada. Kelola petugas di menu Tim Kerja.
    </p>
@endif
