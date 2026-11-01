@extends('layouts.admin')

@section('title', 'Stok Masuk Bibit')
@section('header', 'Stok Masuk Bibit')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.bibits.index') }}" class="text-sm text-green-700 hover:underline">← Kembali ke Kelola Bibit</a>
    </div>

    <div class="mb-6 flex items-center justify-between">
        <p class="text-sm text-gray-600">Riwayat penambahan stok bibit ke pembibitan.</p>
        <x-admin.can-write>
        <a href="{{ route('admin.bibit-masuks.create') }}"
           class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
            + Catat Stok Masuk
        </a>
        </x-admin.can-write>
    </div>

    @include('admin.partials.table-search', ['placeholder' => 'Cari tanaman, sumber...'])

    <x-admin.data-table fixed>
        <colgroup>
            <col style="width: 14%">
            <col style="width: auto">
            <col style="width: 12%">
            <col style="width: 7rem">
        </colgroup>
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3">Tanggal</th>
                <th class="px-4 py-3">Rincian bibit</th>
                <th class="px-4 py-3">Total</th>
                <th class="admin-table-col-actions">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($batches as $batch)
                <tr class="hover:bg-gray-50">
                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600">
                        {{ \App\Support\OperasionalPelaksanaanTime::display($batch->tanggal_masuk) }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $batch->items_count }} jenis bibit</td>
                    <td class="whitespace-nowrap px-4 py-3 text-sm font-semibold text-teal-700">+{{ (int) ($batch->items_sum_jumlah ?? 0) }}</td>
                    <td class="admin-table-col-actions">
                        <x-admin.table-actions>
                            <x-admin.table-action-link :href="route('admin.bibit-masuks.show', $batch)" />
                            <x-admin.can-write>
                                <x-admin.table-action-link variant="edit" :href="route('admin.bibit-masuks.edit', $batch)" />
                                <x-admin.bibit-masuk-delete-button :batch="$batch" />
                            </x-admin.can-write>
                        </x-admin.table-actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                        Belum ada riwayat stok masuk.@if ($canWrite ?? auth()->user()?->canWrite()) <a href="{{ route('admin.bibit-masuks.create') }}" class="text-green-700 underline">Catat sekarang</a>@endif
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if ($batches->hasPages())
            <x-slot:footer>
                {{ $batches->links() }}
            </x-slot:footer>
        @endif
    </x-admin.data-table>
@endsection
