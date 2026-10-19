@extends('layouts.admin')

@section('title', 'Ubah Tim Kerja')
@section('header', 'Ubah Tim Kerja — '.$timPelaksana->nama)

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.tim-pelaksanas.index') }}" class="text-sm text-green-700 hover:underline">← Daftar tim kerja</a>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            Periksa kembali isian form.
        </div>
    @endif

    <form method="POST" action="{{ route('admin.tim-pelaksanas.update', $timPelaksana) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.tim_pelaksanas._form', ['timPelaksana' => $timPelaksana])

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" class="rounded-lg bg-green-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-green-700">
                Simpan Perubahan
            </button>
            <a href="{{ route('admin.tim-pelaksanas.index') }}"
               class="rounded-lg border border-gray-300 px-6 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Batal
            </a>
        </div>
    </form>

    <form method="POST" action="{{ route('admin.tim-pelaksanas.destroy', $timPelaksana) }}"
          class="mt-10 border-t border-gray-200 pt-6"
          onsubmit="return confirm('Hapus tim kerja ini beserta anggotanya? Tindakan tidak dapat dibatalkan.');">
        @csrf
        @method('DELETE')
        <button type="submit" class="rounded-lg border border-red-300 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">
            Hapus Tim Kerja
        </button>
    </form>
@endsection
