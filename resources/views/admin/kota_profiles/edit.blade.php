@extends('layouts.admin')

@section('title', 'Visi & Misi Kota')
@section('header', 'Visi & Misi Kota')

@section('content')
    <div class="max-w-3xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <p class="mb-6 text-sm text-gray-600">Kelola teks visi dan misi yang ditampilkan di bagian pimpinan beranda.</p>

        <form action="{{ route('admin.kota-profile.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div>
                    <label for="visi" class="mb-1 block text-sm font-medium text-gray-700">Visi</label>
                    <textarea name="visi" id="visi" rows="3"
                              class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">{{ old('visi', $profile->visi) }}</textarea>
                </div>

                <div>
                    <label for="misi" class="mb-1 block text-sm font-medium text-gray-700">Misi</label>
                    <textarea name="misi" id="misi" rows="5"
                              class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">{{ old('misi', $profile->misi) }}</textarea>
                </div>
            </div>

            <div class="mt-6 flex gap-3">
                <button type="submit"
                        class="rounded-lg bg-green-600 px-5 py-2 text-sm font-medium text-white hover:bg-green-700">
                    Simpan
                </button>
                <a href="{{ route('home') }}"
                   class="rounded-lg border border-gray-300 px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Lihat Beranda
                </a>
            </div>
        </form>
    </div>
@endsection
