@extends('layouts.admin')

@section('title', 'Tambah Alat/Sarana Operasional')
@section('header', 'Tambah Alat/Sarana Operasional')

@section('content')
    <div class="max-w-3xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('admin.alat-sarana-operasionals.store') }}" method="POST">
            @csrf
            @include('admin.alat_sarana_operasionals._form')
        </form>
    </div>
@endsection
