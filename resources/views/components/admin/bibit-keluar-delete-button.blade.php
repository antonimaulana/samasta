@props(['batch', 'size' => 'default'])

<x-admin.can-write>
    <form action="{{ route('admin.bibit-keluars.destroy', $batch) }}" method="POST" class="inline"
          onsubmit="return confirm('Yakin ingin menghapus transaksi stok keluar ini? Semua jenis bibit dalam transaksi akan dikembalikan ke stok.')">
        @csrf
        @method('DELETE')
        <x-admin.table-action-link tag="button" variant="delete" :size="$size === 'xs' ? 'sm' : 'md'" />
    </form>
</x-admin.can-write>
