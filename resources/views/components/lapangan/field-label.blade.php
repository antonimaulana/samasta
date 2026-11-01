@props([
    'for' => null,
    'required' => false,
])

<label @if ($for) for="{{ $for }}" @endif {{ $attributes->merge(['class' => 'lapangan-field-label']) }}>
    {{ $slot }}@if ($required)<span class="text-red-600" aria-hidden="true"> *</span>@endif
</label>
