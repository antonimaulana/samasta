@extends('layouts.public')

@section('body_class', 'bg-[#f0fdf4]')

@section('title', 'Beranda')

@section('meta_description', config('app.name').' — Portal profil pertamanan, peta taman interaktif, ensiklopedia hijau, dan layanan aduan pertamanan Kota Batam.')

@push('styles')
    @vite(['resources/css/home.css'])
@endpush

@section('hero')
    @include('home.partials.hero', ['heroFeaturedTamans' => $heroFeaturedTamans ?? collect()])
@endsection

@section('main_class', 'home-botanical-bg relative mx-auto max-w-7xl px-4 pt-8 sm:px-6 sm:pt-10 lg:px-8 lg:pt-12')

@section('content')
    <div class="flex flex-col gap-14 pb-4 sm:gap-16 sm:pb-6 lg:gap-20 lg:pb-8">
        @include('home.partials.info-terkini-slider')

        @include('home.partials.pimpinan-grid', [
            'pimpinan' => $pimpinanKota,
            'visiMisi' => $visiMisiKota,
        ])

        <div class="section-leaf-divider" aria-hidden="true"></div>

        @include('home.partials.penjaga-hijau')

        <div class="section-leaf-divider" aria-hidden="true"></div>

        @include('home.partials.ensiklopedia-teaser')
    </div>
@endsection

@include('tamans.partials.nearby-script')

@push('scripts')
    @vite(['resources/js/home-page.js'])
@endpush
