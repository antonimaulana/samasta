@extends('layouts.admin')

@section('title', 'Tambah Artikel Ensiklopedia')
@section('header', 'Tambah Artikel Ensiklopedia')

@section('content')
    <div class="max-w-3xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('admin.ensiklopedia-artikels.store') }}" method="POST">
            @csrf
            @include('admin.ensiklopedia_artikels._form')
        </form>
    </div>
@endsection
