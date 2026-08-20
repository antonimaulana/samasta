@extends('layouts.admin')

@section('title', 'Edit Artikel Ensiklopedia')
@section('header', 'Edit Artikel Ensiklopedia')

@section('content')
    <div class="max-w-3xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('admin.ensiklopedia-artikels.update', $artikel) }}" method="POST">
            @csrf
            @method('PUT')
            @include('admin.ensiklopedia_artikels._form')
        </form>
    </div>
@endsection
