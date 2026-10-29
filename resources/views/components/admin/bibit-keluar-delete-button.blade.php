@props(['batch', 'size' => 'default'])

@php
    $buttonClass = $size === 'xs'
        ? 'rounded border border-red-300 px-2 py-0.5 text-xs text-red-700 hover:bg-red-50'
        : 'rounded border border-red-300 px-3 py-1 text-sm text-red-700 hover:bg-red-50';
@endphp

<x-admin.can-write>
    <form action="{{ route('admin.bibit-keluars.destroy', $batch) }}" method="POST"
          onsubmit="return confirm('Yakin ingin menghapus transaksi stok keluar ini? Semua jenis bibit dalam transaksi akan dikembalikan ke stok.')">
        @csrf
        @method('DELETE')
        <button type="submit" class="{{ $buttonClass }}">
            Hapus
        </button>
    </form>
</x-admin.can-write>
