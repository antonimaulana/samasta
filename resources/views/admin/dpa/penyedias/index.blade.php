@extends('layouts.admin')

@section('title', 'Daftar Penyedia')
@section('header', 'Daftar Penyedia')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" class="flex flex-1 flex-wrap items-end gap-3">
            <div class="min-w-[200px] flex-1 sm:max-w-xs">
                <label for="search" class="mb-1 block text-xs font-medium text-gray-600">Cari</label>
                <input type="search" name="search" id="search" value="{{ request('search') }}" placeholder="Nama, PIC, NPWP..."
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
            </div>
            <button type="submit" class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-900">Cari</button>
        </form>
        <a href="{{ route('admin.dpa.penyedias.create') }}"
           class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">+ Penyedia</a>
    </div>

    @if (session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Nama</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">PIC</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">NPWP</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Paket</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-700">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($penyedias as $penyedia)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $penyedia->nama }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $penyedia->pic ?: '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $penyedia->npwp ?: '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ number_format($penyedia->paket_pekerjaans_count) }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.dpa.penyedias.edit', $penyedia) }}" class="text-green-700 hover:text-green-900">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">Belum ada penyedia.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $penyedias->links() }}</div>
@endsection
