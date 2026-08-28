@extends('layouts.lapangan')

@section('title', 'Buka Input Lapangan')
@section('header', 'Masuk Input Lapangan')

@section('content')
    <div class="lapangan-card overflow-hidden p-0">
        <div class="bg-gradient-to-br from-green-600 to-emerald-700 px-6 py-8 text-center text-white">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-white/20 text-3xl backdrop-blur" aria-hidden="true">
                🔐
            </div>
            <h2 class="mt-4 text-xl font-black">Selamat Datang, Petugas!</h2>
            <p class="mx-auto mt-2 max-w-sm text-sm leading-relaxed text-green-50">
                Masukkan PIN operasional untuk mulai mencatat pekerjaan tanpa login akun.
            </p>
        </div>

        <div class="p-6">
            <x-lapangan.k3-banner />

            <form action="{{ route('lapangan.unlock.store') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label for="pin" class="mb-2 block text-sm font-bold text-gray-800">PIN Lapangan</label>
                    <p class="mb-2 text-xs text-gray-500">PIN cukup dimasukkan sekali per sesi perangkat.</p>
                    <input type="password"
                           id="pin"
                           name="pin"
                           inputmode="numeric"
                           autocomplete="one-time-code"
                           required
                           autofocus
                           class="lapangan-pin-input w-full rounded-2xl border-2 border-gray-200 bg-gray-50 px-4 py-4 text-center text-2xl font-bold focus:border-green-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-green-100"
                           placeholder="••••">
                    @error('pin')
                        <p class="mt-2 flex items-center gap-1 text-sm font-medium text-red-600">
                            <span aria-hidden="true">⚠️</span> {{ $message }}
                        </p>
                    @enderror
                </div>

                <button type="submit"
                        class="lapangan-btn-primary w-full rounded-2xl px-4 py-4 text-base font-black text-white">
                    🚀 Buka Menu Input
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-gray-500">
                Punya akun petugas?
                <a href="{{ route('login') }}" class="font-bold text-green-700 underline decoration-green-300 underline-offset-2 hover:text-green-800">Login di sini</a>
            </p>
        </div>
    </div>

    <x-lapangan.motivation-banner />
@endsection
