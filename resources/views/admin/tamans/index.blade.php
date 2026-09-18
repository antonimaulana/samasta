@extends('layouts.admin')

@section('title', 'Kelola Taman')
@section('header', 'Kelola Taman')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-600">Kelola data RTH/taman yang terdaftar di sistem.</p>
        <div class="flex flex-wrap gap-2">
        <x-admin.can-manage-users>
        <a href="{{ route('admin.tamans.import') }}"
           class="rounded-lg border border-green-200 bg-green-50 px-4 py-2 text-sm font-medium text-green-800 hover:bg-green-100">
            Import CSV
        </a>
        </x-admin.can-manage-users>
        <x-admin.can-write>
        <a href="{{ route('admin.tamans.create') }}"
           class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
            + Tambah Taman
        </a>
        </x-admin.can-write>
        </div>
    </div>

    @if (request('alert') === 'incomplete')
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <span>Menampilkan taman dengan status data Belum Lengkap.</span>
            <a href="{{ route('admin.tamans.index', request()->only('search')) }}"
               class="font-medium text-amber-800 underline hover:text-amber-950">
                Tampilkan semua
            </a>
        </div>
    @endif

    @include('admin.partials.table-search', ['placeholder' => 'Cari nama taman, alamat, kategori, kontraktor...'])

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        @include('admin.tamans.partials.list-mobile-sort', ['sortState' => $sortState])

        <div class="md:hidden">
            @include('admin.tamans.partials.list-mobile-header')
            @forelse ($tamans as $taman)
                @include('admin.tamans.partials.list-mobile-card', ['taman' => $taman])
            @empty
                <p class="px-4 py-10 text-center text-sm text-gray-500">
                    Belum ada data taman.@if ($canWrite ?? auth()->user()?->canWrite()) <a href="{{ route('admin.tamans.create') }}" class="font-medium text-green-700 underline">Tambah sekarang</a>@endif
                </p>
            @endforelse
        </div>

        <div class="hidden md:block">
            <x-admin.data-table fixed class="!rounded-none !border-0 !shadow-none">
                <colgroup>
                    <col style="width: 28%">
                    <col style="width: 14%">
                    <col style="width: 13%">
                    <col style="width: 9%">
                    <col style="width: 6%">
                    <col style="width: 13%">
                    <col style="width: 17%">
                </colgroup>
                @include('admin.tamans.partials.list-table-head', ['sortState' => $sortState])
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($tamans as $taman)
                        @include('admin.tamans.partials.list-table-row', ['taman' => $taman])
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-500">
                                Belum ada data taman.@if ($canWrite ?? auth()->user()?->canWrite()) <a href="{{ route('admin.tamans.create') }}" class="font-medium text-green-700 underline">Tambah sekarang</a>@endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-admin.data-table>
        </div>

        @if ($tamans->hasPages())
            <div class="border-t border-gray-200 px-4 py-3">
                {{ $tamans->links() }}
            </div>
        @endif
    </div>
@endsection
