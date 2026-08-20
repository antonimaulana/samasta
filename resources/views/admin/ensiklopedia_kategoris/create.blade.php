@extends('layouts.admin')

@section('title', 'Tambah Kategori Ensiklopedia')
@section('header', 'Tambah Kategori Ensiklopedia')

@section('content')
    <div class="max-w-3xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('admin.ensiklopedia-kategoris.store') }}" method="POST">
            @csrf
            @include('admin.ensiklopedia_kategoris._form')
        </form>
    </div>
@endsection
