@extends('layouts.admin')

@section('title', 'Detail Stok Masuk')
@section('header', 'Detail Stok Masuk')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.bibit-masuks.index') }}" class="text-sm text-green-700 hover:underline">← Kembali ke Stok Masuk</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase text-teal-600">Stok Masuk</p>
                        <h2 class="mt-1 text-xl font-bold text-gray-900">Transaksi masuk</h2>
                        <p class="mt-1 text-sm text-gray-500">{{ \App\Support\OperasionalPelaksanaanTime::display($batch->tanggal_masuk) }}</p>
                    </div>
                    <span class="rounded-full bg-teal-100 px-3 py-1 text-xs font-bold text-teal-800">
                        +{{ $batch->totalJumlah() }} bibit · {{ $batch->items->count() }} jenis
                    </span>
                </div>

                <div class="mt-6">
                    <h3 class="text-sm font-semibold text-gray-700">Rincian bibit</h3>
                    <div class="mt-3 overflow-hidden rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium text-gray-700">Nama tanaman</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-700">Sumber</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-700">Siap tanam</th>
                                    <th class="px-4 py-2 text-right font-medium text-gray-700">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach ($batch->items as $item)
                                    <tr>
                                        <td class="px-4 py-2">
                                            <x-admin.bibit-nama :bibit="$item->bibit" />
                                        </td>
                                        <td class="px-4 py-2 text-gray-600">{{ $item->sumber }}</td>
                                        <td class="px-4 py-2 text-gray-600">{{ $item->status_siap_tanam ? 'Siap' : 'Belum' }}</td>
                                        <td class="px-4 py-2 text-right font-semibold text-teal-700">+{{ $item->jumlah }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @include('admin.partials.bibit-stok-foto-gallery', ['batch' => $batch])

                <div class="mt-6 flex flex-wrap gap-2 border-t border-gray-100 pt-6">
                    <x-admin.can-write>
                    <a href="{{ route('admin.bibit-masuks.edit', $batch) }}"
                       class="rounded-lg border border-blue-300 px-4 py-2 text-sm font-medium text-blue-700 hover:bg-blue-50">
                        Edit
                    </a>
                    <x-admin.bibit-masuk-delete-button :batch="$batch" />
                    </x-admin.can-write>
                </div>
            </div>
        </div>
    </div>
@endsection
