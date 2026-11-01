@props(['masuk', 'size' => 'default'])

@if ($masuk->sisa_stok === $masuk->jumlah)
    <x-admin.can-write>
        <form action="{{ route('admin.bibit-masuks.destroy', $masuk) }}" method="POST" class="inline"
              onsubmit="return confirm('Yakin ingin menghapus data stok masuk ini? Stok batch akan dikurangi.')">
            @csrf
            @method('DELETE')
            <x-admin.table-action-link tag="button" variant="delete" :size="$size === 'xs' ? 'sm' : 'md'" />
        </form>
    </x-admin.can-write>
@endif
