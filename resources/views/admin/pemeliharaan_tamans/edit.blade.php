@extends('layouts.admin')

@section('title', 'Edit Operasional Pemeliharaan')
@section('header', 'Edit Operasional Pemeliharaan')

@section('content')
    <div class="max-w-4xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('admin.pemeliharaan-tamans.update', $kinerja) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('admin.pemeliharaan_tamans._form')
        </form>
    </div>
@endsection
