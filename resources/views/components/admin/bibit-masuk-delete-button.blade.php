@props(['batch', 'size' => 'default'])

@php
    $canDelete = $batch->items->every(fn ($line) => $line->sisa_stok === $line->jumlah);
@endphp

@if ($canDelete)
    <x-admin.can-write>
        <form action="{{ route('admin.bibit-masuks.destroy', $batch) }}" method="POST" class="inline"
              onsubmit="return confirm('Yakin ingin menghapus transaksi stok masuk ini? Stok akan dikurangi.')">
            @csrf
            @method('DELETE')
            <x-admin.table-action-link tag="button" variant="delete" :size="$size === 'xs' ? 'sm' : 'md'" />
        </form>
    </x-admin.can-write>
@endif
