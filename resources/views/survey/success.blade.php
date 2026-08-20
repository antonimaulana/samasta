@extends('layouts.public')

@section('title', 'Survey Terkirim')

@section('main_class', 'mx-auto max-w-2xl px-4 py-16 sm:px-6 lg:px-8')

@section('content')
    <div class="rounded-2xl border border-violet-200 bg-white p-8 text-center shadow-lg">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-violet-100 text-3xl">⭐</div>
        <h1 class="mt-6 text-2xl font-black text-gray-900">Terima Kasih!</h1>
        <p class="mt-2 text-gray-600">Penilaian Anda telah kami terima dan akan menjadi bahan evaluasi operasional pertamanan Kota Batam.</p>

        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ route('home') }}"
               class="rounded-xl bg-violet-600 px-6 py-2.5 text-sm font-bold text-white hover:bg-violet-700">
                Kembali ke Beranda
            </a>
            <a href="{{ route('survey.create') }}"
               class="rounded-xl border border-violet-300 px-6 py-2.5 text-sm font-bold text-violet-700 hover:bg-violet-50">
                Isi Survey Lain
            </a>
        </div>
    </div>
@endsection
