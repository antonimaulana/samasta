@extends('layouts.admin')

@section('title', 'Tambah Kategori RTH')
@section('header', 'Tambah Kategori RTH')

@section('content')
    <div class="max-w-3xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('admin.rth-kategoris.store') }}" method="POST">
            @csrf
            @include('admin.rth_kategoris._form')
        </form>
    </div>
@endsection
