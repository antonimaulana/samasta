@props([
    'placeholder' => null,
])

<select {{ $attributes->merge([
    'class' => 'w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500',
]) }}>
    @if ($placeholder !== null)
        <option value="">{{ $placeholder }}</option>
    @endif
    {{ $slot }}
</select>
