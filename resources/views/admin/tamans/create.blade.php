@extends('layouts.admin')

@section('title', 'Tambah Taman')
@section('header', 'Tambah Taman')

@section('content')
    <div class="mx-auto max-w-5xl">
        <x-admin.rth-page-toolbar
            :back-url="route('admin.tamans.index')"
            back-label="← Kembali ke Data Taman" />

        <form action="{{ route('admin.tamans.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('admin.tamans._form')
        </form>
    </div>

    @include('admin.tamans.partials.location-map-assets')
@endsection
