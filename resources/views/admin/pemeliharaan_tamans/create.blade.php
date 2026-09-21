@extends('layouts.admin')

@section('title', 'Input Operasional Pemeliharaan')
@section('header', 'Input Operasional Pemeliharaan')

@section('content')
    <div class="max-w-4xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form id="pemeliharaan-taman-form" action="{{ route('admin.pemeliharaan-tamans.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('admin.pemeliharaan_tamans._form')
        </form>
    </div>
@endsection
