@extends('layouts.admin')

@section('title', 'Monitoring DPA')
@section('header', 'Monitoring DPA')

@section('content')
    @include('admin.dpa.partials.wizard-steps', ['step' => 1])

    @if ($tahunList->isEmpty())
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-8 text-center">
            <p class="text-lg font-semibold text-amber-900">Belum ada tahun anggaran</p>
            <p class="mt-2 text-sm text-amber-800">Mulai dengan menambahkan tahun anggaran APBD.</p>
            <a href="{{ route('admin.dpa.tahun-anggarans.create') }}"
               class="mt-5 inline-flex rounded-lg bg-green-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-green-700">
                + Tambah Tahun Anggaran
            </a>
        </div>
    @else
        <div class="mb-4 flex items-center justify-between">
            <p class="text-sm text-gray-600">Pilih tahun anggaran untuk melanjutkan ke sub kegiatan.</p>
            <a href="{{ route('admin.dpa.tahun-anggarans.create') }}"
               class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                + Tahun Baru
            </a>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($tahunList as $tahun)
                <a href="{{ route('admin.dpa.tahun-anggarans.show', $tahun) }}"
                   class="group rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition hover:border-green-400 hover:shadow-md">
                    <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Tahun Anggaran</p>
                    <p class="mt-2 text-4xl font-bold text-gray-900 group-hover:text-green-700">{{ $tahun->tahun }}</p>
                    @if ($tahun->keterangan)
                        <p class="mt-2 text-sm text-gray-600">{{ $tahun->keterangan }}</p>
                    @endif
                    <p class="mt-4 text-sm font-medium text-green-700 group-hover:underline">Pilih sub kegiatan →</p>
                </a>
            @endforeach
        </div>
    @endif
@endsection
