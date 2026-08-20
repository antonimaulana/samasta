@extends('layouts.admin')

@section('title', 'Tambah Taman')
@section('header', 'Tambah Taman')

@section('content')
    <div class="max-w-3xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('admin.tamans.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('admin.tamans._form')
        </form>
    </div>
@endsection
