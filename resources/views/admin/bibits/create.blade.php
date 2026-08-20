@extends('layouts.admin')

@section('title', 'Tambah Bibit')
@section('header', 'Tambah Bibit')

@section('content')
    <div class="max-w-3xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('admin.bibits.store') }}" method="POST">
            @csrf
            @include('admin.bibits._form')
        </form>
    </div>
@endsection
