@extends('layouts.admin')

@section('title', 'Edit Alat/Sarana Operasional')
@section('header', 'Edit Alat/Sarana Operasional')

@section('content')
    <div class="max-w-3xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('admin.alat-sarana-operasionals.update', $item) }}" method="POST">
            @csrf
            @method('PUT')
            @include('admin.alat_sarana_operasionals._form')
        </form>
    </div>
@endsection
