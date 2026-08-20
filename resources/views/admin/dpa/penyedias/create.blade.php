@extends('layouts.admin')

@section('title', 'Tambah Penyedia')
@section('header', 'Tambah Penyedia')

@section('content')
    <div class="max-w-2xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        @include('admin.dpa.penyedias._form')
    </div>
@endsection
