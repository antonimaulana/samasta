@extends('layouts.public')

@section('title', 'Aduan Terkirim')

@section('main_class', 'mx-auto max-w-2xl px-4 py-16 sm:px-6 lg:px-8')

@section('content')
    <div class="rounded-2xl border border-green-200 bg-white p-8 text-center shadow-lg">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100 text-3xl">✅</div>
        <h1 class="mt-6 text-2xl font-black text-gray-900">Aduan Berhasil Dikirim</h1>
        <p class="mt-2 text-gray-600">Terima kasih atas partisipasi Anda menjaga ruang hijau Batam.</p>

        <div class="mt-8 rounded-xl bg-lime-50 px-6 py-5 ring-1 ring-lime-200">
            <p class="text-xs font-bold uppercase tracking-wider text-green-600">Nomor Aduan</p>
            <p class="mt-1 font-mono text-2xl font-black text-gray-900">{{ $aduan->nomor_aduan }}</p>
            <p class="mt-3 text-sm text-gray-600">Simpan nomor ini untuk referensi. Tim kami akan meninjau aduan Anda.</p>
        </div>

        <dl class="mt-8 space-y-3 text-left text-sm">
            <div class="flex justify-between gap-4 border-b border-gray-100 pb-3">
                <dt class="text-gray-500">Jenis</dt>
                <dd class="font-semibold text-gray-900">{{ $aduan->jenis_aduan }}</dd>
            </div>
            <div class="flex justify-between gap-4 border-b border-gray-100 pb-3">
                <dt class="text-gray-500">Lokasi</dt>
                <dd class="text-right font-semibold text-gray-900">{{ $aduan->lokasi }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-gray-500">Status</dt>
                <dd>
                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ \App\Models\AduanMasyarakat::statusBadgeClass($aduan->status) }}">
                        {{ $aduan->status }}
                    </span>
                </dd>
            </div>
        </dl>

        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ route('aduan.check') }}"
               class="rounded-xl border border-green-300 px-6 py-2.5 text-sm font-bold text-green-700 hover:bg-green-50">
                Cek Status Aduan
            </a>
            <a href="{{ route('home') }}"
               class="rounded-xl bg-green-600 px-6 py-2.5 text-sm font-bold text-white hover:bg-green-700">
                Kembali ke Beranda
            </a>
            <a href="{{ route('aduan.create') }}"
               class="rounded-xl border border-green-300 px-6 py-2.5 text-sm font-bold text-green-700 hover:bg-green-50">
                Kirim Aduan Lain
            </a>
        </div>
    </div>
@endsection
