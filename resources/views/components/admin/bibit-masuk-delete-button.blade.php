@props(['masuk', 'size' => 'default'])

@php
    $buttonClass = $size === 'xs'
        ? 'rounded border border-red-300 px-2 py-0.5 text-xs text-red-700 hover:bg-red-50'
        : 'rounded border border-red-300 px-3 py-1 text-sm text-red-700 hover:bg-red-50';
@endphp

@if ($masuk->sisa_stok === $masuk->jumlah)
    <x-admin.can-write>
        <form action="{{ route('admin.bibit-masuks.destroy', $masuk) }}" method="POST"
              onsubmit="return confirm('Yakin ingin menghapus data stok masuk ini? Stok batch akan dikurangi.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="{{ $buttonClass }}">
                Hapus
            </button>
        </form>
    </x-admin.can-write>
@endif
