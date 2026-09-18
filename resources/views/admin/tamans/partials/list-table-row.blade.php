@props([
    'taman',
    'showKategori' => true,
    'showActions' => true,
])

<tr class="hover:bg-gray-50">
    <td class="max-w-0 px-4 py-3 align-top">
        <p class="truncate text-sm font-medium text-gray-900" title="{{ $taman->nama_taman }}">{{ $taman->nama_taman }}</p>
        <p class="mt-1 truncate text-sm text-gray-500" title="{{ $taman->alamat }}">{{ $taman->alamat ?: '—' }}</p>
    </td>
    @if ($showKategori)
        <td class="px-4 py-3 align-top">
            <x-admin.taman-kategori-badge :kategori="$taman->kategori" class="max-w-full" />
        </td>
    @endif
    <td class="px-4 py-3 align-top">
        @if ($taman->kelurahan)
            <span class="block truncate text-sm text-gray-900" title="{{ $taman->kelurahan->nama }}">{{ $taman->kelurahan->nama }}</span>
            <span class="mt-0.5 block truncate text-sm text-gray-500" title="{{ $taman->kelurahan->kecamatan->nama }}">{{ $taman->kelurahan->kecamatan->nama }}</span>
        @else
            <span class="text-sm text-amber-600">Belum diset</span>
        @endif
    </td>
    <td class="whitespace-nowrap px-4 py-3 text-right align-top text-sm tabular-nums text-gray-900">
        {{ number_format($taman->luasan, 0, ',', '.') }}
    </td>
    <td class="whitespace-nowrap px-4 py-3 align-top text-sm text-gray-900">
        {{ $taman->tahun_pembangunan ?? '—' }}
    </td>
    <td class="min-w-[7.5rem] px-4 py-3 align-top">
        <x-admin.taman-status-data-badge :status="$taman->status_data" size="table" />
    </td>
    @if ($showActions)
        <td class="whitespace-nowrap px-3 py-3 text-right align-top">
            <div class="inline-flex flex-wrap justify-end gap-1.5">
                <x-admin.table-action-link :href="route('admin.tamans.show', $taman)">Lihat</x-admin.table-action-link>
                <x-admin.can-write>
                    <x-admin.table-action-link variant="edit" :href="route('admin.tamans.edit', $taman)">Edit</x-admin.table-action-link>
                </x-admin.can-write>
                <x-admin.can-delete>
                    <form action="{{ route('admin.tamans.destroy', $taman) }}" method="POST" class="inline"
                          onsubmit="return confirm('Yakin ingin menghapus taman ini?')">
                        @csrf
                        @method('DELETE')
                        <x-admin.table-action-link tag="button" variant="delete">Hapus</x-admin.table-action-link>
                    </form>
                </x-admin.can-delete>
            </div>
        </td>
    @endif
</tr>
