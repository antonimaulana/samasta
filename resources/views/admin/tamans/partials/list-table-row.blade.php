@props([
    'taman',
    'showKategori' => true,
    'showActions' => true,
])

@php
    $cell = 'px-4 py-3 align-top text-sm max-md:px-2 max-md:py-2.5';
@endphp

<tr class="hover:bg-gray-50">
    <td class="{{ $cell }} max-w-0">
        <p class="font-medium leading-snug text-gray-900 break-words md:truncate" title="{{ $taman->nama_taman }}">{{ $taman->nama_taman }}</p>
        <p class="mt-1 hidden truncate text-sm leading-snug text-gray-500 md:block" title="{{ $taman->alamat }}">{{ $taman->alamat ?: '—' }}</p>
    </td>
    @if ($showKategori)
        <td class="{{ $cell }}">
            <x-admin.taman-kategori-badge :kategori="$taman->kategori" class="max-w-full text-xs md:text-sm" />
        </td>
    @endif
    <td class="{{ $cell }} max-w-[5.5rem] md:max-w-none">
        @if ($taman->kelurahan)
            <span class="block truncate text-xs leading-snug text-gray-900 md:text-sm" title="{{ $taman->kelurahan->nama }}">{{ $taman->kelurahan->nama }}</span>
            <span class="mt-0.5 hidden truncate text-sm leading-snug text-gray-500 md:block" title="{{ $taman->kelurahan->kecamatan->nama }}">{{ $taman->kelurahan->kecamatan->nama }}</span>
        @else
            <span class="text-xs text-amber-600 md:text-sm">Belum diset</span>
        @endif
    </td>
    <td class="{{ $cell }} whitespace-nowrap text-right tabular-nums text-gray-900 max-md:text-xs">
        {{ number_format($taman->luasan, 0, ',', '.') }}
    </td>
    <td class="hidden whitespace-nowrap px-4 py-3 text-sm text-gray-900 md:table-cell">
        {{ $taman->tahun_pembangunan ?? '—' }}
    </td>
    <td class="{{ $cell }} min-w-[7.5rem]">
        <x-admin.taman-status-data-badge :status="$taman->status_data" size="table" />
    </td>
    @if ($showActions)
        <td class="{{ $cell }} text-right">
            <div class="inline-flex flex-wrap justify-end gap-1.5 max-md:gap-1">
                <x-admin.table-action-link :href="route('admin.tamans.show', $taman)" class="max-md:px-2 max-md:py-0.5 max-md:text-xs">Lihat</x-admin.table-action-link>
                <x-admin.can-write>
                    <x-admin.table-action-link variant="edit" :href="route('admin.tamans.edit', $taman)" class="max-md:px-2 max-md:py-0.5 max-md:text-xs">Edit</x-admin.table-action-link>
                </x-admin.can-write>
                <x-admin.can-delete>
                    <form action="{{ route('admin.tamans.destroy', $taman) }}" method="POST" class="inline"
                          onsubmit="return confirm('Yakin ingin menghapus taman ini?')">
                        @csrf
                        @method('DELETE')
                        <x-admin.table-action-link tag="button" variant="delete" class="max-md:px-2 max-md:py-0.5 max-md:text-xs">Hapus</x-admin.table-action-link>
                    </form>
                </x-admin.can-delete>
            </div>
        </td>
    @endif
</tr>
