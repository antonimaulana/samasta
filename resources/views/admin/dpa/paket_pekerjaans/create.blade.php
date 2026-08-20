@extends('layouts.admin')

@section('title', 'Tambah Paket Pekerjaan')
@section('header', 'Tambah Paket Pekerjaan')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.dpa.dpas.show', $dpa) }}" class="text-sm text-green-700 hover:text-green-900">&larr; {{ $dpa->nama_dpa }}</a>
    </div>
    <div class="max-w-3xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        @include('admin.dpa.paket_pekerjaans._form', ['dpa' => $dpa, 'penyedias' => $penyedias])
    </div>
@endsection
