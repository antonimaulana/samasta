@props([
    'taman',
    'showKategori' => true,
    'showActions' => true,
])

@php
    $actionClass = 'max-md:px-1.5 max-md:py-0.5 max-md:text-[11px]';
@endphp

<tr class="hover:bg-gray-50">
    <td class="max-w-0 px-2 py-2 align-top md:px-4 md:py-3">
        <p class="text-sm font-medium leading-snug text-gray-900 break-words md:truncate md:whitespace-nowrap" title="{{ $taman->nama_taman }}">{{ $taman->nama_taman }}</p>
        <p class="mt-0.5 hidden truncate text-sm text-gray-500 md:block" title="{{ $taman->alamat }}">{{ $taman->alamat ?: '—' }}</p>
    </td>
    @if ($showKategori)
        <td class="max-w-[5.5rem] px-2 py-2 md:max-w-none md:whitespace-nowrap md:px-4 md:py-3">
            <x-admin.taman-kategori-badge :kategori="$taman->kategori" class="max-md:text-[11px] max-md:px-2" />
        </td>
    @endif
    <td class="max-w-[4rem] px-1 py-2 md:max-w-none md:px-4 md:py-3">
        @if ($taman->kelurahan)
            <span class="block truncate text-[11px] text-gray-900 md:text-sm" title="{{ $taman->kelurahan->nama }}">{{ $taman->kelurahan->nama }}</span>
            <span class="hidden truncate text-sm text-gray-500 md:block" title="{{ $taman->kelurahan->kecamatan->nama }}">{{ $taman->kelurahan->kecamatan->nama }}</span>
        @else
            <span class="text-[11px] text-amber-600 md:text-sm">—</span>
        @endif
    </td>
    <td class="whitespace-nowrap px-1 py-2 text-right text-[11px] tabular-nums text-gray-900 md:px-4 md:py-3 md:text-sm">{{ number_format($taman->luasan, 0, ',', '.') }}</td>
    <td class="hidden whitespace-nowrap px-4 py-3 text-sm text-gray-900 md:table-cell">{{ $taman->tahun_pembangunan ?? '—' }}</td>
    <td class="px-2 py-2 md:px-4 md:py-3">
        <div class="md:hidden">
            <x-admin.taman-status-data-badge :status="$taman->status_data" size="table" compact />
        </div>
        <div class="hidden md:block">
            <x-admin.taman-status-data-badge :status="$taman->status_data" size="table" />
        </div>
    </td>
    @if ($showActions)
        <td class="whitespace-normal px-2 py-2 text-right md:whitespace-nowrap md:px-3 md:py-3">
            <div class="inline-flex max-md:flex-col max-md:items-stretch max-md:gap-0.5 flex-wrap justify-end gap-1.5">
                <x-admin.table-action-link :href="route('admin.tamans.show', $taman)" :class="$actionClass">Lihat</x-admin.table-action-link>
                <x-admin.can-write>
                    <x-admin.table-action-link variant="edit" :href="route('admin.tamans.edit', $taman)" :class="$actionClass">Edit</x-admin.table-action-link>
                </x-admin.can-write>
                <x-admin.can-delete>
                    <form action="{{ route('admin.tamans.destroy', $taman) }}" method="POST"
                          onsubmit="return confirm('Yakin ingin menghapus taman ini?')">
                        @csrf
                        @method('DELETE')
                        <x-admin.table-action-link tag="button" variant="delete" :class="$actionClass">Hapus</x-admin.table-action-link>
                    </form>
                </x-admin.can-delete>
            </div>
        </td>
    @endif
</tr>
