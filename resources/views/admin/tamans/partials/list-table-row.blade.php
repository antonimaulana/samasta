@props([
    'taman',
    'showKategori' => true,
    'showActions' => true,
])

@php
    $cell = 'px-4 py-3 align-top text-sm max-md:px-2 max-md:py-2.5';
@endphp

<tr class="hover:bg-gray-50">
    <td class="{{ $cell }} min-w-0 max-md:min-w-[11.5rem] max-md:w-[42%] md:max-w-0">
        <p class="text-sm font-medium leading-snug text-gray-900 break-words max-md:text-[15px] md:truncate" title="{{ $taman->nama_taman }}">{{ $taman->nama_taman }}</p>
        <p class="mt-1 hidden truncate text-sm leading-snug text-gray-500 md:block" title="{{ $taman->alamat }}">{{ $taman->alamat ?: '—' }}</p>
    </td>
    @if ($showKategori)
        <td class="{{ $cell }} max-md:max-w-[4.25rem] max-md:px-1">
            <x-admin.taman-kategori-badge :kategori="$taman->kategori" class="max-w-full text-[10px] leading-tight md:text-sm" />
        </td>
    @endif
    <td class="{{ $cell }} max-md:min-w-[5.5rem] max-md:max-w-[6.5rem] md:max-w-none">
        @if ($taman->kelurahan)
            <span class="block truncate text-[11px] leading-snug text-gray-900 md:text-sm" title="{{ $taman->kelurahan->nama }}">{{ $taman->kelurahan->nama }}</span>
            <span class="mt-0.5 block truncate text-[11px] leading-snug text-gray-500 md:text-sm" title="{{ $taman->kelurahan->kecamatan->nama }}">{{ $taman->kelurahan->kecamatan->nama }}</span>
        @else
            <span class="text-xs text-amber-600 md:text-sm">Belum diset</span>
        @endif
    </td>
    <td class="{{ $cell }} hidden whitespace-nowrap text-right tabular-nums text-gray-900 md:table-cell">
        {{ number_format($taman->luasan, 0, ',', '.') }}
    </td>
    <td class="hidden whitespace-nowrap px-4 py-3 text-sm text-gray-900 md:table-cell">
        {{ $taman->tahun_pembangunan ?? '—' }}
    </td>
    <td class="{{ $cell }} max-md:max-w-[7.25rem] max-md:px-1">
        <x-admin.taman-status-data-badge :status="$taman->status_data" size="table" class="max-md:text-[10px] max-md:leading-tight" />
    </td>
    @if ($showActions)
        <td class="{{ $cell }} max-md:w-[4.5rem] max-md:px-1 text-right">
            <div class="inline-flex flex-wrap justify-end gap-1 max-md:flex-col max-md:items-end max-md:gap-0.5 md:gap-1.5">
                <x-admin.table-action-link :href="route('admin.tamans.show', $taman)" class="max-md:px-1.5 max-md:py-0.5 max-md:text-[10px]">Lihat</x-admin.table-action-link>
                <x-admin.can-write>
                    <x-admin.table-action-link variant="edit" :href="route('admin.tamans.edit', $taman)" class="max-md:px-1.5 max-md:py-0.5 max-md:text-[10px]">Edit</x-admin.table-action-link>
                </x-admin.can-write>
                <x-admin.can-delete>
                    <form action="{{ route('admin.tamans.destroy', $taman) }}" method="POST" class="inline"
                          onsubmit="return confirm('Yakin ingin menghapus taman ini?')">
                        @csrf
                        @method('DELETE')
                        <x-admin.table-action-link tag="button" variant="delete" class="max-md:px-1.5 max-md:py-0.5 max-md:text-[10px]">Hapus</x-admin.table-action-link>
                    </form>
                </x-admin.can-delete>
            </div>
        </td>
    @endif
</tr>
