@extends('layouts.admin')

@section('title', 'Edit Paket Pekerjaan')
@section('header', 'Edit Paket Pekerjaan')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.dpa.paket-pekerjaans.show', $paketPekerjaan) }}" class="text-sm text-green-700 hover:text-green-900">&larr; {{ $paketPekerjaan->nama_paket }}</a>
    </div>
    <div class="max-w-3xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        @include('admin.dpa.paket_pekerjaans._form', [
            'dpa' => $paketPekerjaan->dpa,
            'paketPekerjaan' => $paketPekerjaan,
            'penyedias' => $penyedias,
        ])
    </div>
@endsection
