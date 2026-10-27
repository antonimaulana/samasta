<div class="mb-4 flex flex-wrap items-center justify-end gap-2">
    <a href="{{ route('admin.ensiklopedia-kategoris.create') }}"
       class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
        + Tambah Kategori
    </a>
</div>

<form method="GET" class="mb-4">
    <input type="hidden" name="tab" value="kategori">
    @foreach (request()->except(['tab', 'kategori_search', 'kategori_page', 'page']) as $key => $value)
        @if (is_scalar($value) && (string) $value !== '')
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach
    <div class="w-full max-w-md">
        <label for="kategori_search" class="mb-1 block text-sm font-medium text-gray-700">Cari kategori</label>
        <input type="search" name="kategori_search" id="kategori_search" value="{{ request('kategori_search') }}"
               placeholder="Nama, slug..."
               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
    </div>
</form>

<x-admin.data-table>
    <thead class="bg-gray-50">
        <tr>
            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Icon</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Nama</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Slug</th>
            <th class="px-4 py-3 text-center text-sm font-medium text-gray-700">Urutan</th>
            <th class="px-4 py-3 text-center text-sm font-medium text-gray-700">Artikel</th>
            <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Aksi</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-100">
        @forelse ($kategoris as $kategori)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-xl">{{ $kategori->icon }}</td>
                <td class="px-4 py-3">
                    <p class="font-medium">{{ $kategori->nama }}</p>
                    @if ($kategori->deskripsi)
                        <p class="mt-0.5 max-w-md truncate text-xs text-gray-500">{{ $kategori->deskripsi }}</p>
                    @endif
                </td>
                <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $kategori->slug }}</td>
                <td class="px-4 py-3 text-center text-gray-600">{{ $kategori->urutan }}</td>
                <td class="px-4 py-3 text-center">
                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">
                        {{ $kategori->artikels_count }}
                    </span>
                </td>
                <td class="px-4 py-3 text-right">
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('ensiklopedia.index') }}#{{ $kategori->slug }}"
                           class="rounded border border-gray-300 px-3 py-1 text-gray-700 hover:bg-gray-50">
                            Lihat
                        </a>
                        <a href="{{ route('admin.ensiklopedia-kategoris.edit', $kategori) }}"
                           class="rounded border border-blue-300 px-3 py-1 text-blue-700 hover:bg-blue-50">
                            Edit
                        </a>
                        <form action="{{ route('admin.ensiklopedia-kategoris.destroy', $kategori) }}" method="POST"
                              onsubmit="return confirm('Hapus kategori ini? Semua artikel di dalamnya ikut terhapus.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="rounded border border-red-300 px-3 py-1 text-red-700 hover:bg-red-50">
                                Hapus
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                    Belum ada kategori.
                    <a href="{{ route('admin.ensiklopedia-kategoris.create') }}" class="text-green-700 underline">Tambah sekarang</a>
                </td>
            </tr>
        @endforelse
    </tbody>
    @if ($kategoris->hasPages())
        <x-slot:footer>
            {{ $kategoris->appends(request()->except('kategori_page'))->links() }}
        </x-slot:footer>
    @endif
</x-admin.data-table>
