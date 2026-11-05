@extends('layouts.admin')

@section('title', 'Pesan Tama — Beranda')
@section('header', 'Pesan Tama — Beranda')

@section('content')
    <p class="mb-6 max-w-2xl text-sm text-gray-600">
        Maskot <strong>Tama</strong> menampilkan pop-up saat pengunjung scroll ke bagian beranda yang sesuai. Setiap bagian punya satu pesan.
    </p>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-800">Bagian beranda</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-800">Judul pesan</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-800">Aktif</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-800">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($tips as $tip)
                    <tr class="hover:bg-gray-50/80">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $tip->section_label }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $tip->title }}</td>
                        <td class="px-4 py-3 text-center">
                            <form action="{{ route('admin.beranda-mascot-tips.toggle', $tip) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $tip->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $tip->is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <x-admin.table-action-link variant="edit" :href="route('admin.beranda-mascot-tips.edit', $tip)" />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
