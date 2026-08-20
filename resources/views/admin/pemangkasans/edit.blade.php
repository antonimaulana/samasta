@extends('layouts.admin')

@section('title', 'Edit Operasional Pertamanan')
@section('header', 'Edit Operasional Pertamanan')

@section('content')
    <div class="max-w-3xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form id="pemangkasan-form" action="{{ route('admin.pemangkasans.update', $pemangkasan) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('admin.pemangkasans._form', ['pemangkasan' => $pemangkasan])
        </form>
    </div>
@endsection
