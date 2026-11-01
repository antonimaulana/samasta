@props(['pemangkasan', 'progres', 'size' => 'default'])

<x-admin.can-delete>
    <form action="{{ route('admin.pemangkasans.progres.destroy', ['pemangkasan' => $pemangkasan, 'pemangkasanProgres' => $progres]) }}"
          method="POST"
          class="inline"
          onsubmit="return confirm('Yakin ingin menghapus progres harian ini? Foto dan data terkait akan dihapus.')">
        @csrf
        @method('DELETE')
        <x-admin.table-action-link tag="button" variant="delete" :size="$size === 'xs' ? 'sm' : 'md'" />
    </form>
</x-admin.can-delete>
