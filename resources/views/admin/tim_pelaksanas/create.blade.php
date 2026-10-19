@extends('layouts.admin')

@section('title', 'Tambah Tim Kerja')
@section('header', 'Tambah Tim Kerja')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.tim-pelaksanas.index') }}" class="text-sm text-green-700 hover:underline">← Daftar tim kerja</a>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            Periksa kembali isian form.
        </div>
    @endif

    <form method="POST" action="{{ route('admin.tim-pelaksanas.store') }}" class="space-y-6">
        @csrf
        @include('admin.tim_pelaksanas._form')

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="rounded-lg bg-green-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-green-700">
                Simpan Tim Kerja
            </button>
            <a href="{{ route('admin.tim-pelaksanas.index') }}"
               class="rounded-lg border border-gray-300 px-6 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Batal
            </a>
        </div>
    </form>
@endsection
