@extends('layouts.admin')

@section('title', 'Edit Taman')
@section('header', 'Edit Taman')

@section('content')
    <div class="mx-auto max-w-5xl">
        <x-admin.rth-page-toolbar
            :back-url="route('admin.tamans.show', $taman)"
            back-label="← Kembali ke profil taman" />

        <form action="{{ route('admin.tamans.update', $taman) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('admin.tamans._form', ['taman' => $taman])
        </form>
    </div>

    @foreach ($taman->images as $image)
        <form id="delete-image-{{ $image->id }}"
              action="{{ route('admin.tamans.images.destroy', [$taman, $image]) }}"
              method="POST"
              class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endforeach

    @include('admin.tamans.partials.location-map-assets')
@endsection
