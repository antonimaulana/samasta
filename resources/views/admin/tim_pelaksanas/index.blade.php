@extends('layouts.admin')

@section('title', 'Tim Pelaksana')
@section('header', 'Tim Pelaksana & Wilayah Kerja')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-600">
            Atur wilayah kerja tim berdasarkan kelurahan. Tim Nursery dan Tim Armada tidak terikat wilayah.
        </p>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Tim</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Nama Pengawas</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Wilayah Kerja</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">Kelurahan</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($teams as $team)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $team->nama }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $team->nama_pengawas ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @if ($team->memiliki_wilayah_kerja)
                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">Per kelurahan</span>
                            @else
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">Lintas wilayah / khusus</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center text-gray-700">
                            {{ $team->memiliki_wilayah_kerja ? number_format($team->kelurahans_count) : '—' }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if ($team->memiliki_wilayah_kerja)
                                <x-admin.can-manage-users>
                                <a href="{{ route('admin.tim-pelaksanas.wilayah.edit', $team) }}"
                                   class="rounded border border-blue-300 px-3 py-1 text-blue-700 hover:bg-blue-50">
                                    Atur Wilayah
                                </a>
                                </x-admin.can-manage-users>
                            @else
                                <span class="text-xs text-gray-400">Manual</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
