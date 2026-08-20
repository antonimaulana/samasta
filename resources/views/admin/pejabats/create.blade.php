@extends('layouts.admin')

@section('title', 'Tambah Pejabat')
@section('header', 'Tambah Pejabat')

@section('content')
    <div class="max-w-3xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('admin.pejabats.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('admin.pejabats._form')
        </form>
    </div>
@endsection
