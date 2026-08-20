@extends('layouts.admin')

@section('title', 'Edit Penyedia')
@section('header', 'Edit Penyedia')

@section('content')
    <div class="max-w-2xl space-y-6">
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            @include('admin.dpa.penyedias._form', ['penyedia' => $penyedia])
        </div>
        <form action="{{ route('admin.dpa.penyedias.destroy', $penyedia) }}" method="POST"
              onsubmit="return confirm('Hapus penyedia ini?')">
            @csrf @method('DELETE')
            <button type="submit" class="rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50">Hapus Penyedia</button>
        </form>
    </div>
@endsection
