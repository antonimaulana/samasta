@extends('layouts.admin')

@section('title', 'Kelola DPA')
@section('header', 'Input & Import DPA/Paket Pekerjaan')

@section('content')
    @include('admin.dpa.partials.wizard-steps', [
        'step' => 3,
        'tahunAnggaran' => $tahunAnggaran,
        'subKegiatanLabel' => $subKegiatanLabel,
    ])

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.dpa.tahun-anggarans.show', $tahunAnggaran) }}"
           class="text-sm text-green-700 hover:text-green-900">&larr; Ganti sub kegiatan</a>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.dpa.dpas.create', ['tahun_anggaran_id' => $tahunAnggaran->id, 'sub_kegiatan' => $subKegiatan]) }}"
               class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                + Tambah DPA
            </a>
        </div>
    </div>

    @if ($statsPerTahap)
        <div class="mb-6 grid gap-3 sm:grid-cols-3">
            @foreach ($tahapLabels as $tahap => $label)
                <div class="rounded-lg border border-gray-200 bg-white px-4 py-3">
                    <p class="text-xs text-gray-500">{{ $label }}</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($statsPerTahap[$tahap] ?? 0) }}</p>
                </div>
            @endforeach
        </div>
    @endif

    @if ($dpas->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center">
            <p class="text-lg font-semibold text-gray-900">Belum ada DPA pada sub kegiatan ini</p>
            <p class="mt-2 text-sm text-gray-600">Buat DPA terlebih dahulu, lalu input atau import paket pekerjaan.</p>
            <a href="{{ route('admin.dpa.dpas.create', ['tahun_anggaran_id' => $tahunAnggaran->id, 'sub_kegiatan' => $subKegiatan]) }}"
               class="mt-5 inline-flex rounded-lg bg-green-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-green-700">
                + Buat DPA Pertama
            </a>
        </div>
    @else
        <div class="space-y-6">
            @foreach ($dpas as $dpa)
                <x-admin.data-table>
                    <x-slot:header>
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 bg-gray-50 px-5 py-4">
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $dpa->nama_dpa }}</h3>
                                <p class="text-xs text-gray-500">
                                    @if ($dpa->nomor_dpa) No. {{ $dpa->nomor_dpa }} · @endif
                                    {{ number_format($dpa->paket_pekerjaans_count) }} paket pekerjaan
                                </p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.dpa.paket-pekerjaans.import', $dpa) }}"
                                   class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-100">
                                    Import CSV
                                </a>
                                <a href="{{ route('admin.dpa.paket-pekerjaans.create', $dpa) }}"
                                   class="rounded-lg bg-green-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-green-700">
                                    + Paket Pekerjaan
                                </a>
                                <a href="{{ route('admin.dpa.dpas.show', $dpa) }}"
                                   class="rounded-lg border border-green-300 px-3 py-1.5 text-sm font-medium text-green-800 hover:bg-green-50">
                                    Kelola DPA
                                </a>
                            </div>
                        </div>
                    </x-slot:header>

                    @if ($dpa->paketPekerjaans->isEmpty())
                        <tbody>
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-sm text-gray-500">
                                    Belum ada paket pekerjaan. Tambah manual atau import CSV.
                                </td>
                            </tr>
                        </tbody>
                    @else
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Paket Pekerjaan</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Pagu</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Tahap</th>
                                <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($dpa->paketPekerjaans as $paket)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm">
                                        <p class="font-medium text-gray-900">{{ $paket->nama_paket }}</p>
                                        @if ($paket->kode_rup)
                                            <p class="text-xs text-gray-500">RUP {{ $paket->kode_rup }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">Rp {{ number_format($paket->pagu_anggaran, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium">{{ $paket->tahapLabel() }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm">
                                        <a href="{{ route('admin.dpa.paket-pekerjaans.show', $paket) }}"
                                           class="font-medium text-green-700 hover:text-green-900">Monitoring</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    @endif
                </x-admin.data-table>
            @endforeach
        </div>
    @endif
@endsection
