@extends('layouts.admin')

@section('title', 'Tambah Operasional Pertamanan')
@section('header', 'Tambah Operasional Pertamanan')

@section('content')
    <div class="max-w-3xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form id="pemangkasan-form" action="{{ route('admin.pemangkasans.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('admin.pemangkasans._form')
        </form>
    </div>
@endsection
