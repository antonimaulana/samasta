@props([
    'taman',
    'showKategori' => true,
    'showActions' => true,
])

@php
    $actionLinkClass = 'px-2.5 py-1 text-xs';
@endphp

<article class="border-b border-gray-100 bg-white px-4 py-4 last:border-b-0">
    <h3 class="text-sm font-semibold leading-snug text-gray-900 break-words">
        {{ $taman->nama_taman }}
    </h3>

    <dl class="mt-3 space-y-2 text-xs leading-relaxed text-gray-700">
        @if ($showKategori && filled($taman->kategori))
            <div>
                <dt class="font-medium text-gray-500">Kategori</dt>
                <dd class="mt-0.5 break-words text-gray-800">{{ $taman->kategori }}</dd>
            </div>
        @endif
        @if ($taman->kelurahan)
            <div>
                <dt class="font-medium text-gray-500">Wilayah</dt>
                <dd class="mt-0.5 break-words text-gray-800">
                    {{ $taman->kelurahan->nama }}, {{ $taman->kelurahan->kecamatan->nama }}
                </dd>
            </div>
        @endif
        <div>
            <dt class="font-medium text-gray-500">Alamat</dt>
            <dd class="mt-0.5 break-words text-gray-600">{{ $taman->alamat ?: '—' }}</dd>
        </div>
        <div class="flex flex-wrap gap-x-4 gap-y-1 pt-1">
            <div>
                <dt class="font-medium text-gray-500">Luasan</dt>
                <dd class="mt-0.5 font-medium text-gray-900">{{ number_format($taman->luasan, 0, ',', '.') }} M²</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500">Tahun</dt>
                <dd class="mt-0.5 font-medium text-gray-900">{{ $taman->tahun_pembangunan ?? '—' }}</dd>
            </div>
        </div>
    </dl>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
        <x-admin.taman-status-data-badge :status="$taman->status_data" size="table" />
        @if ($showActions)
            <div class="flex flex-wrap justify-end gap-1.5">
                <x-admin.table-action-link :href="route('admin.tamans.show', $taman)" :class="$actionLinkClass">Lihat</x-admin.table-action-link>
                <x-admin.can-write>
                    <x-admin.table-action-link variant="edit" :href="route('admin.tamans.edit', $taman)" :class="$actionLinkClass">Edit</x-admin.table-action-link>
                </x-admin.can-write>
                <x-admin.can-delete>
                    <form action="{{ route('admin.tamans.destroy', $taman) }}" method="POST"
                          onsubmit="return confirm('Yakin ingin menghapus taman ini?')">
                        @csrf
                        @method('DELETE')
                        <x-admin.table-action-link tag="button" variant="delete" :class="$actionLinkClass">Hapus</x-admin.table-action-link>
                    </form>
                </x-admin.can-delete>
            </div>
        @endif
    </div>
</article>
