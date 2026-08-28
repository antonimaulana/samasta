@props([
    'placeholder' => 'Cari data...',
    'preserve' => null,
])

@php
    $preserveParams = $preserve ?? collect(request()->query())->except(['search', 'page'])->all();
@endphp

<form method="GET" {{ $attributes->merge(['class' => 'mb-4 flex flex-wrap items-end gap-3']) }}>
    @foreach ($preserveParams as $key => $value)
        @if (is_scalar($value) && (string) $value !== '')
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach

    <div class="min-w-[220px] flex-1 sm:max-w-md">
        <label for="table-search-input" class="mb-1 block text-sm font-medium text-gray-700">Cari</label>
        <input type="search" name="search" id="table-search-input"
               value="{{ request('search') }}"
               placeholder="{{ $placeholder }}"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
    </div>

    <button type="submit"
            class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
        Cari
    </button>

    @if (request()->filled('search'))
        <a href="{{ url()->current() }}?{{ http_build_query(collect($preserveParams)->except(['search'])->all()) }}"
           class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            Reset
        </a>
    @endif
</form>
