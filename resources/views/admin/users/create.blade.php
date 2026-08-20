@extends('layouts.admin')

@section('title', 'Tambah Pengguna')
@section('header', 'Tambah Pengguna')

@section('content')
    <div class="max-w-3xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            @include('admin.users._form')
        </form>
    </div>
@endsection
