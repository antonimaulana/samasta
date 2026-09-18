@props([
    'taman',
    'showKategori' => true,
    'showActions' => true,
])

@php
    $actionLinkClass = 'px-2 py-0.5 text-[11px] leading-none';
    $metaParts = [];
    if ($showKategori && filled($taman->kategori)) {
        $metaParts[] = ['type' => 'kategori', 'text' => $taman->kategori];
    }
    if ($taman->kelurahan) {
        $metaParts[] = ['type' => 'text', 'text' => $taman->kelurahan->nama.', '.$taman->kelurahan->kecamatan->nama];
    }
@endphp

<div class="border-b border-gray-100 px-3 py-2.5 last:border-b-0 hover:bg-gray-50/80">
    <div class="grid grid-cols-[minmax(0,1fr)_auto] items-start gap-2">
        <p class="text-sm font-medium leading-snug text-gray-900 break-words">
            {{ $taman->nama_taman }}
        </p>
        <div class="shrink-0 pt-0.5">
            <x-admin.taman-status-data-badge :status="$taman->status_data" size="table" />
        </div>
    </div>

    @if ($metaParts !== [])
        <p class="mt-1 text-[11px] leading-snug text-gray-600 break-words">
            @foreach ($metaParts as $part)
                @if (! $loop->first)
                    <span class="text-gray-300"> · </span>
                @endif
                @if ($part['type'] === 'kategori')
                    <span class="font-medium text-emerald-800">{{ $part['text'] }}</span>
                @else
                    <span>{{ $part['text'] }}</span>
                @endif
            @endforeach
        </p>
    @endif

    @if (filled($taman->alamat))
        <p class="mt-0.5 line-clamp-2 text-[11px] leading-snug text-gray-500 break-words" title="{{ $taman->alamat }}">
            {{ $taman->alamat }}
        </p>
    @endif

    <div class="mt-1.5 flex flex-wrap items-center justify-between gap-x-2 gap-y-1">
        <p class="text-[11px] tabular-nums text-gray-500">
            {{ number_format($taman->luasan, 0, ',', '.') }} M²
            <span class="text-gray-300">·</span>
            {{ $taman->tahun_pembangunan ?? '—' }}
        </p>
        @if ($showActions)
            <div class="flex shrink-0 flex-wrap justify-end gap-1">
                <x-admin.table-action-link :href="route('admin.tamans.show', $taman)" :class="$actionLinkClass">Lihat</x-admin.table-action-link>
                <x-admin.can-write>
                    <x-admin.table-action-link variant="edit" :href="route('admin.tamans.edit', $taman)" :class="$actionLinkClass">Edit</x-admin.table-action-link>
                </x-admin.can-write>
                <x-admin.can-delete>
                    <form action="{{ route('admin.tamans.destroy', $taman) }}" method="POST" class="inline"
                          onsubmit="return confirm('Yakin ingin menghapus taman ini?')">
                        @csrf
                        @method('DELETE')
                        <x-admin.table-action-link tag="button" variant="delete" :class="$actionLinkClass">Hapus</x-admin.table-action-link>
                    </form>
                </x-admin.can-delete>
            </div>
        @endif
    </div>
</div>
