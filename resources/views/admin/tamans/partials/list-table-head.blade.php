@props([
    'sortState',
    'showKategori' => true,
    'showActions' => true,
])

<thead class="bg-gray-50">
    <tr>
        <x-admin.table-sort-header column="nama" label="Nama" :sort="$sortState['sort']" :direction="$sortState['direction']" class="whitespace-nowrap" />
        @if ($showKategori)
            <x-admin.table-sort-header column="kategori" label="Kategori" :sort="$sortState['sort']" :direction="$sortState['direction']" class="hidden whitespace-nowrap md:table-cell" />
        @endif
        <x-admin.table-sort-header column="wilayah" label="Wilayah" :sort="$sortState['sort']" :direction="$sortState['direction']" class="whitespace-nowrap" />
        <x-admin.table-sort-header column="luasan" label="Luasan (M²)" align="right" :sort="$sortState['sort']" :direction="$sortState['direction']" class="whitespace-nowrap" />
        <x-admin.table-sort-header column="tahun" label="Tahun" :sort="$sortState['sort']" :direction="$sortState['direction']" class="whitespace-nowrap" />
        <x-admin.table-sort-header column="status_data" label="Status Data" :sort="$sortState['sort']" :direction="$sortState['direction']" class="whitespace-nowrap" />
        @if ($showActions)
            <th class="whitespace-nowrap px-4 py-3 text-right text-sm font-medium text-gray-700">Aksi</th>
        @endif
    </tr>
</thead>
