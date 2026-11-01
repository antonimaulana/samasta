@props([
    'fixed' => false,
])

<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm']) }}>
    @isset($header)
        {{ $header }}
    @endisset

    <div class="overflow-x-auto">
        <table @class([
            'admin-data-table',
            'admin-data-table--fixed w-full md:table-fixed' => $fixed,
        ])>
            {{ $slot }}
        </table>
    </div>

    @isset($footer)
        <div class="border-t border-gray-200 px-4 py-3">
            {{ $footer }}
        </div>
    @endisset
</div>
