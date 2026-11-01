@extends('layouts.admin')

@section('title', 'Kelola Pengguna')
@section('header', 'Kelola Pengguna')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-600">Atur akun Administrator, Admin, Pengawas, dan Pimpinan.</p>
        <a href="{{ route('admin.users.create') }}"
           class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
            + Tambah Pengguna
        </a>
    </div>

    <x-admin.data-table>
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Nama</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Email</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Peran</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Tim / Wilayah</th>
                        <th class="admin-table-col-actions">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-bold text-green-800">
                                    {{ $user->roleLabel() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $user->wilayahLabel() ?? '—' }}
                            </td>
                            <td class="admin-table-col-actions">
                                <x-admin.table-actions>
                                    <x-admin.table-action-link variant="edit" :href="route('admin.users.edit', $user)" />
                                    @if ($user->id !== auth()->id())
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Hapus pengguna ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <x-admin.table-action-link tag="button" variant="delete" />
                                        </form>
                                    @endif
                                </x-admin.table-actions>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-gray-500">Belum ada pengguna.</td>
                        </tr>
                    @endforelse
                </tbody>
        @if ($users->hasPages())
            <x-slot:footer>
                {{ $users->links() }}
            </x-slot:footer>
        @endif
    </x-admin.data-table>
@endsection
