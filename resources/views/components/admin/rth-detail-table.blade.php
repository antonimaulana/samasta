@props([
    'columns' => 2,
])

<x-admin.data-table fixed {{ $attributes }}>
    @if ($columns === 2)
        <colgroup>
            <col style="width: 33%">
            <col>
        </colgroup>
    @endif
    {{ $slot }}
</x-admin.data-table>
