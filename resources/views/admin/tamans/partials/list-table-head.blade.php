@props([
    'sortState',
    'showKategori' => true,
    'showActions' => true,
])

@php
    $thBase = 'whitespace-nowrap px-4 py-3 max-md:px-2 max-md:py-2.5 max-md:text-xs';
@endphp

<thead class="bg-gray-50">
    <tr>
        <x-admin.table-sort-header column="nama" label="Nama" :sort="$sortState['sort']" :direction="$sortState['direction']" :class="$thBase" />
        @if ($showKategori)
            <x-admin.table-sort-header column="kategori" label="Kategori" :sort="$sortState['sort']" :direction="$sortState['direction']" :class="$thBase" />
        @endif
        <x-admin.table-sort-header column="wilayah" label="Wilayah" :sort="$sortState['sort']" :direction="$sortState['direction']" :class="$thBase" />
        <x-admin.table-sort-header column="luasan" label="Luasan (M²)" align="right" :sort="$sortState['sort']" :direction="$sortState['direction']" :class="$thBase.' hidden md:table-cell'" />
        <x-admin.table-sort-header column="tahun" label="Tahun" :sort="$sortState['sort']" :direction="$sortState['direction']" class="hidden whitespace-nowrap px-4 py-3 md:table-cell" />
        <x-admin.table-sort-header column="status_data" label="Status Data" :sort="$sortState['sort']" :direction="$sortState['direction']" :class="$thBase" />
        @if ($showActions)
            <th class="whitespace-nowrap px-4 py-3 text-right text-sm font-medium text-gray-700 max-md:px-2 max-md:py-2.5 max-md:text-xs">Aksi</th>
        @endif
    </tr>
</thead>
