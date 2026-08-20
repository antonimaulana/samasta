@extends('layouts.admin')

@section('title', 'Edit Bibit')
@section('header', 'Edit Bibit')

@section('content')
    <div class="max-w-3xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('admin.bibits.update', $bibit) }}" method="POST">
            @csrf
            @method('PUT')
            @include('admin.bibits._form', ['bibit' => $bibit])
        </form>
    </div>
@endsection
