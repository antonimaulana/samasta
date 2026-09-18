@props([
    'sortState',
    'showKategori' => true,
    'showActions' => true,
])

<thead class="bg-gray-50">
    <tr>
        <x-admin.table-sort-header column="nama" label="Nama" :sort="$sortState['sort']" :direction="$sortState['direction']" class="whitespace-nowrap max-md:px-2 max-md:py-2" />
        @if ($showKategori)
            <x-admin.table-sort-header column="kategori" label="Kategori" :sort="$sortState['sort']" :direction="$sortState['direction']" class="whitespace-nowrap max-md:px-2 max-md:py-2" />
        @endif
        <x-admin.table-sort-header column="wilayah" label="Wilayah" :sort="$sortState['sort']" :direction="$sortState['direction']" class="whitespace-nowrap max-md:px-1 max-md:py-2 max-md:text-[10px]" />
        <x-admin.table-sort-header column="luasan" label="Luasan" align="right" :sort="$sortState['sort']" :direction="$sortState['direction']" class="whitespace-nowrap max-md:px-1 max-md:py-2 max-md:text-[10px]" />
        <x-admin.table-sort-header column="tahun" label="Tahun" :sort="$sortState['sort']" :direction="$sortState['direction']" class="hidden whitespace-nowrap md:table-cell" />
        <x-admin.table-sort-header column="status_data" label="Status Data" :sort="$sortState['sort']" :direction="$sortState['direction']" class="whitespace-nowrap max-md:px-2 max-md:py-2 max-md:text-xs" />
        @if ($showActions)
            <th class="whitespace-nowrap px-2 py-2 text-right text-xs font-medium text-gray-700 md:px-4 md:py-3 md:text-sm">Aksi</th>
        @endif
    </tr>
</thead>
