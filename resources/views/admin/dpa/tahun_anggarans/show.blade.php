@extends('layouts.admin')

@section('title', 'Sub Kegiatan '.$tahunAnggaran->tahun)
@section('header', 'Monitoring DPA — Tahun '.$tahunAnggaran->tahun)

@section('content')
    @include('admin.dpa.partials.wizard-steps', [
        'step' => 2,
        'tahunAnggaran' => $tahunAnggaran,
    ])

    <div class="mb-4">
        <a href="{{ route('admin.dpa.dashboard') }}" class="text-sm text-green-700 hover:text-green-900">&larr; Ganti tahun anggaran</a>
    </div>

    <p class="mb-6 text-sm text-gray-600">
        Pilih sub kegiatan untuk mengelola DPA dan paket pekerjaan tahun {{ $tahunAnggaran->tahun }}.
    </p>

    <div class="grid gap-4 lg:grid-cols-3">
        @foreach ($subKegiatanCards as $card)
            <a href="{{ route('admin.dpa.tahun-anggarans.kelola', [$tahunAnggaran, 'sub_kegiatan' => $card['slug']]) }}"
               class="group flex h-full flex-col rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition hover:border-green-400 hover:shadow-md">
                <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Sub Kegiatan</p>
                <h3 class="mt-3 flex-1 text-base font-semibold leading-snug text-gray-900 group-hover:text-green-800">{{ $card['label'] }}</h3>
                <dl class="mt-4 grid grid-cols-2 gap-3 border-t border-gray-100 pt-4 text-sm">
                    <div>
                        <dt class="text-gray-500">DPA</dt>
                        <dd class="font-semibold text-gray-900">{{ number_format($card['dpa_count']) }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Paket</dt>
                        <dd class="font-semibold text-gray-900">{{ number_format($card['paket_count']) }}</dd>
                    </div>
                </dl>
                <p class="mt-4 text-sm font-medium text-green-700 group-hover:underline">Input & import data →</p>
            </a>
        @endforeach
    </div>
@endsection
