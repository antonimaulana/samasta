@extends('layouts.admin')

@section('title', 'Tambah Armada')
@section('header', 'Tambah Armada')

@section('content')
    <div class="max-w-3xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('admin.armadas.store') }}" method="POST">
            @csrf
            @include('admin.armadas._form')
        </form>
    </div>
@endsection
