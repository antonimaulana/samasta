@props([
    'href',
])

<a href="{{ $href }}" target="_blank" rel="noopener noreferrer" {{ $attributes }}>
    {{ $slot }}
</a>
