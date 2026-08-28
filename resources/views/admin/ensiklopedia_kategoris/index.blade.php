@extends('layouts.admin')

@section('title', 'Kategori Ensiklopedia')
@section('header', 'Kategori Ensiklopedia')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-600">Kelola kategori konten ensiklopedia di situs publik.</p>
        <div class="flex gap-2">
            <a href="{{ route('admin.ensiklopedia-artikels.index') }}"
               class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Kelola Artikel
            </a>
            <x-admin.can-write>
            <a href="{{ route('admin.ensiklopedia-kategoris.create') }}"
               class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                + Tambah Kategori
            </a>
            </x-admin.can-write>
        </div>
    </div>

    @include('admin.partials.table-search', ['placeholder' => 'Cari nama kategori, slug...'])

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
                                    <x-admin.can-write>
                                    <a href="{{ route('admin.ensiklopedia-kategoris.edit', $kategori) }}"
                                       class="rounded border border-blue-300 px-3 py-1 text-blue-700 hover:bg-blue-50">
                                        Edit
                                    </a>
                                    </x-admin.can-write>
                                    <x-admin.can-delete>
                                    <form action="{{ route('admin.ensiklopedia-kategoris.destroy', $kategori) }}" method="POST"
                                          onsubmit="return confirm('Hapus kategori ini? Semua artikel di dalamnya ikut terhapus.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="rounded border border-red-300 px-3 py-1 text-red-700 hover:bg-red-50">
                                            Hapus
                                        </button>
                                    </form>
                                    </x-admin.can-delete>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                Belum ada kategori.@if ($canWrite ?? auth()->user()?->canWrite()) <a href="{{ route('admin.ensiklopedia-kategoris.create') }}" class="text-green-700 underline">Tambah sekarang</a>@endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
        @if ($kategoris->hasPages())
            <x-slot:footer>
                {{ $kategoris->links() }}
            </x-slot:footer>
        @endif
    </x-admin.data-table>
@endsection
