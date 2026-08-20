@extends('layouts.admin')

@section('title', 'Edit Taman')
@section('header', 'Edit Taman')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.tamans.show', $taman) }}" class="text-sm text-green-700 hover:underline">← Kembali ke profil taman</a>
    </div>

    <div class="max-w-3xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
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
@endsection
