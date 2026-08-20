@extends('layouts.admin')

@section('title', 'Edit Kategori Ensiklopedia')
@section('header', 'Edit Kategori Ensiklopedia')

@section('content')
    <div class="max-w-3xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('admin.ensiklopedia-kategoris.update', $kategori) }}" method="POST">
            @csrf
            @method('PUT')
            @include('admin.ensiklopedia_kategoris._form')
        </form>
    </div>
@endsection
