@props([
    'kategori',
])

<span {{ $attributes->merge(['class' => 'inline-flex whitespace-nowrap rounded-full bg-emerald-100 px-2.5 py-0.5 text-sm font-medium text-emerald-800']) }}>
    {{ $kategori }}
</span>
