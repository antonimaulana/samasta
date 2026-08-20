@extends('layouts.admin')

@section('title', 'Edit Pejabat')
@section('header', 'Edit Pejabat')

@section('content')
    <div class="max-w-3xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('admin.pejabats.update', $pejabat) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('admin.pejabats._form')
        </form>
    </div>
@endsection
