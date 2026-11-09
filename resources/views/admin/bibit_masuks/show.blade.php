@extends('layouts.admin')

@section('title', 'Detail Stok Masuk')
@section('header', 'Detail Stok Masuk')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.bibit-masuks.index') }}" class="text-sm text-green-700 hover:underline">← Kembali ke Stok Masuk</a>
        <x-admin.pdf-export-link :href="route('admin.bibit-masuks.export-pdf', $batch)"
           class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-bold text-white hover:bg-red-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Cetak PDF
        </x-admin.pdf-export-link>
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

                <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-500">Sumber</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $batch->sumber ?? $batch->items->first()?->sumber ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-500">Siap tanam</dt>
                        <dd class="mt-1 text-gray-900">{{ ($batch->status_siap_tanam ?? $batch->items->first()?->status_siap_tanam) ? 'Ya' : 'Belum' }}</dd>
                    </div>
                </dl>

                <div class="mt-6">
                    <h3 class="text-sm font-semibold text-gray-700">Rincian bibit</h3>
                    <div class="mt-3 overflow-hidden rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium text-gray-700">Nama tanaman</th>
                                    <th class="px-4 py-2 text-right font-medium text-gray-700">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach ($batch->items as $item)
                                    <tr>
                                        <td class="px-4 py-2">
                                            <x-admin.bibit-nama :bibit="$item->bibit" />
                                        </td>
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
