@extends('layouts.admin')

@section('title', 'Edit Armada')
@section('header', 'Edit Armada')

@section('content')
    <div class="max-w-3xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('admin.armadas.update', $item) }}" method="POST">
            @csrf
            @method('PUT')
            @include('admin.armadas._form')
        </form>
    </div>
@endsection
