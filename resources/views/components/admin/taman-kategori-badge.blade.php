@props([
    'kategori',
])

<span {{ $attributes->merge(['class' => 'inline-flex max-w-full truncate rounded-full bg-emerald-100 px-2.5 py-0.5 text-sm font-medium text-emerald-800']) }} title="{{ $kategori }}">
    {{ $kategori }}
</span>
