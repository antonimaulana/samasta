@props([
    'label',
    'full' => false,
])

<div @class(['sm:col-span-2' => $full])>
    <dt class="text-sm font-medium text-gray-700">{{ $label }}</dt>
    <dd {{ $attributes->merge(['class' => 'mt-1 text-sm text-gray-900']) }}>
        {{ $slot }}
    </dd>
</div>
