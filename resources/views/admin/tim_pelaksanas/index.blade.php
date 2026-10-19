@extends('layouts.admin')

@section('title', 'Tim Kerja')
@section('header', 'Tim Kerja')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-end gap-3">
        <a href="{{ route('admin.tim-pelaksanas.create') }}"
           class="inline-flex items-center rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-700">
            + Tambah Tim Kerja
        </a>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ $errors->first() }}
        </div>
    @endif

    <x-admin.data-table>
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Tim</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Pengawas</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Wilayah</th>
                <th class="px-4 py-3 text-center text-sm font-medium text-gray-700">Kelurahan</th>
                <th class="px-4 py-3 text-center text-sm font-medium text-gray-700">Anggota</th>
                <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($teams as $team)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $team->nama }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $team->nama_pengawas ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm">
                        @if ($team->memiliki_wilayah_kerja)
                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">Per kelurahan</span>
                        @else
                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">Lintas wilayah / khusus</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center text-sm text-gray-700">
                        {{ $team->memiliki_wilayah_kerja ? number_format($team->kelurahans_count) : '—' }}
                    </td>
                    <td class="px-4 py-3 text-center text-sm text-gray-700">{{ number_format($team->petugas_count) }}</td>
                    <td class="px-4 py-3 text-right text-sm">
                        <a href="{{ route('admin.tim-pelaksanas.edit', $team) }}"
                           class="rounded border border-green-300 px-3 py-1 font-medium text-green-800 hover:bg-green-50">
                            Ubah
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">—</td>
                </tr>
            @endforelse
        </tbody>
    </x-admin.data-table>
@endsection
