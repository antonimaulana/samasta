@extends('layouts.admin')

@section('title', 'Edit Kategori RTH')
@section('header', 'Edit Kategori RTH')

@section('content')
    <div class="max-w-3xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('admin.rth-kategoris.update', $kategori) }}" method="POST">
            @csrf
            @method('PUT')
            @include('admin.rth_kategoris._form')
        </form>
    </div>
@endsection
