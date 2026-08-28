@extends('layouts.admin')

@section('title', 'Artikel Ensiklopedia')
@section('header', 'Artikel Ensiklopedia')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-600">Kelola artikel konten ensiklopedia di situs publik.</p>
        <div class="flex gap-2">
            <a href="{{ route('admin.ensiklopedia-kategoris.index') }}"
               class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Kelola Kategori
            </a>
            <x-admin.can-write>
            <a href="{{ route('admin.ensiklopedia-artikels.create') }}"
               class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                + Tambah Artikel
            </a>
            </x-admin.can-write>
        </div>
    </div>

    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3">
        <div class="min-w-[220px] flex-1 sm:max-w-md">
            <label for="search" class="mb-1 block text-xs font-medium text-gray-600">Cari</label>
            <input type="search" name="search" id="search" value="{{ request('search') }}"
                   placeholder="Cari judul, ringkasan, slug..."
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
        </div>
        <div>
            <label for="kategori" class="mb-1 block text-xs font-medium text-gray-600">Filter Kategori</label>
            <select name="kategori" id="kategori"
                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                <option value="">Semua kategori</option>
                @foreach ($kategoris as $kat)
                    <option value="{{ $kat->id }}" @selected(request('kategori') == $kat->id)>
                        {{ $kat->nama }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit"
                class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
            Terapkan
        </button>
        @if (request()->anyFilled(['search', 'kategori']))
            <a href="{{ route('admin.ensiklopedia-artikels.index') }}"
               class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Reset
            </a>
        @endif
    </form>

    <x-admin.data-table>
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Icon</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Judul</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Kategori</th>
                <th class="px-4 py-3 text-center text-sm font-medium text-gray-700">Urutan</th>
                <th class="px-4 py-3 text-center text-sm font-medium text-gray-700">Status</th>
                <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Aksi</th>
            </tr>
        </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($artikels as $artikel)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-xl">{{ $artikel->icon }}</td>
                            <td class="px-4 py-3">
                                <p class="font-medium">{{ $artikel->judul }}</p>
                                <p class="mt-0.5 max-w-md truncate text-xs text-gray-500">{{ $artikel->ringkas }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">
                                    {{ $artikel->kategori->nama }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-gray-600">{{ $artikel->urutan }}</td>
                            <td class="px-4 py-3 text-center">
                                @if ($artikel->is_published)
                                    <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">Publik</span>
                                @else
                                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">Draft</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    @if ($artikel->is_published)
                                        <a href="{{ route('ensiklopedia.show', $artikel) }}"
                                           class="rounded border border-gray-300 px-3 py-1 text-gray-700 hover:bg-gray-50">
                                            Lihat
                                        </a>
                                    @endif
                                    <x-admin.can-write>
                                    <a href="{{ route('admin.ensiklopedia-artikels.edit', $artikel) }}"
                                       class="rounded border border-blue-300 px-3 py-1 text-blue-700 hover:bg-blue-50">
                                        Edit
                                    </a>
                                    </x-admin.can-write>
                                    <x-admin.can-delete>
                                    <form action="{{ route('admin.ensiklopedia-artikels.destroy', $artikel) }}" method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus artikel ini?')">
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
                                Belum ada artikel.@if ($canWrite ?? auth()->user()?->canWrite()) <a href="{{ route('admin.ensiklopedia-artikels.create') }}" class="text-green-700 underline">Tambah sekarang</a>@endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
        @if ($artikels->hasPages())
            <x-slot:footer>
                {{ $artikels->links() }}
            </x-slot:footer>
        @endif
    </x-admin.data-table>
@endsection
