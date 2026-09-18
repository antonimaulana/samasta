@props(['sortState'])

@php
    $makeSortUrl = function (string $column) use ($sortState) {
        $isActive = $sortState['sort'] === $column;
        $direction = $isActive && $sortState['direction'] === 'asc' ? 'desc' : 'asc';

        return request()->fullUrlWithQuery([
            'sort' => $column,
            'direction' => $direction,
            'page' => null,
        ]);
    };
@endphp

<div class="flex flex-wrap items-center gap-2 border-b border-gray-200 bg-gray-50 px-4 py-2.5 md:hidden">
    <span class="text-xs font-medium text-gray-500">Urutkan:</span>
    @foreach (['nama' => 'Nama', 'status_data' => 'Status'] as $column => $label)
        @php $active = $sortState['sort'] === $column; @endphp
        <a href="{{ $makeSortUrl($column) }}"
           class="rounded-full px-2.5 py-1 text-xs font-medium {{ $active ? 'bg-green-100 text-green-800' : 'bg-white text-gray-700 ring-1 ring-gray-200' }}">
            {{ $label }}
            @if ($active)
                <span aria-hidden="true">{{ $sortState['direction'] === 'asc' ? '↑' : '↓' }}</span>
            @endif
        </a>
    @endforeach
</div>
