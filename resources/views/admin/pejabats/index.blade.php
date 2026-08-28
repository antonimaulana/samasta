@extends('layouts.admin')

@section('title', 'Pejabat Beranda')
@section('header', 'Pejabat Beranda')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-600">Kelola foto dan profil pejabat yang ditampilkan di beranda.</p>
        <x-admin.can-write>
        <a href="{{ route('admin.pejabats.create') }}"
           class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
            + Tambah Pejabat
        </a>
        </x-admin.can-write>
    </div>

    @include('admin.partials.table-search', ['placeholder' => 'Cari nama, jabatan...'])

    <x-admin.data-table>
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Foto</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Nama</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Jabatan</th>
                <th class="px-4 py-3 text-center text-sm font-medium text-gray-700">Urutan</th>
                <th class="px-4 py-3 text-center text-sm font-medium text-gray-700">Status</th>
                <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Aksi</th>
            </tr>
        </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($pejabats as $pejabat)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                @if ($pejabat->image_path && file_exists(public_path($pejabat->image_path)))
                                    <img src="{{ asset($pejabat->image_path) }}" alt="{{ $pejabat->nama }}"
                                         class="h-12 w-12 rounded-lg object-cover ring-1 ring-gray-200">
                                @else
                                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-lg bg-gray-100 text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-medium">{{ $pejabat->nama }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $pejabat->jabatan }}</td>
                            <td class="px-4 py-3 text-center text-gray-600">{{ $pejabat->urutan }}</td>
                            <td class="px-4 py-3 text-center">
                                @if ($pejabat->is_published)
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">Publik</span>
                                @else
                                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">Draft</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <x-admin.can-write>
                                    <a href="{{ route('admin.pejabats.edit', $pejabat) }}"
                                       class="rounded border border-blue-300 px-3 py-1 text-blue-700 hover:bg-blue-50">
                                        Edit
                                    </a>
                                    </x-admin.can-write>
                                    <x-admin.can-delete>
                                    <form action="{{ route('admin.pejabats.destroy', $pejabat) }}" method="POST"
                                          onsubmit="return confirm('Hapus pejabat ini?')">
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
                                Belum ada pejabat.@if ($canWrite ?? auth()->user()?->canWrite()) <a href="{{ route('admin.pejabats.create') }}" class="text-green-700 underline">Tambah sekarang</a>@endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
        @if ($pejabats->hasPages())
            <x-slot:footer>
                {{ $pejabats->links() }}
            </x-slot:footer>
        @endif
    </x-admin.data-table>
@endsection
