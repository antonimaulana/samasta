@extends('layouts.public')

@section('title', 'Masukan Masyarakat')

@section('hero')
    <section class="relative overflow-hidden bg-gradient-to-br from-emerald-600 via-green-600 to-teal-600 text-white">
        <div class="relative mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <p class="text-xs font-bold uppercase tracking-widest text-emerald-100">Partisipasi Masyarakat</p>
            <h1 class="mt-2 text-3xl font-black sm:text-4xl">Masukan & Layanan Publik</h1>
            <p class="mt-3 max-w-2xl text-white/90">
                Sampaikan aduan kondisi taman atau berikan penilaian kepuasan operasional pertamanan Kota Batam.
            </p>
        </div>
        <div class="relative -mb-1 text-[#f0fdf4]">
            <svg viewBox="0 0 1440 40" fill="currentColor" class="block w-full"><path d="M0,20 Q360,40 720,20 T1440,20 L1440,40 L0,40 Z"/></svg>
        </div>
    </section>
@endsection

@section('content')
    <div class="relative -mt-4 pb-16">
        <div class="grid gap-6 md:grid-cols-2">
            <a href="{{ route('aduan.create') }}"
               class="group overflow-hidden rounded-2xl border border-orange-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg hover:shadow-orange-200/40">
                <div class="bg-gradient-to-br from-orange-400 to-red-500 px-6 py-8 text-white">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/20 text-3xl backdrop-blur-sm">📢</div>
                    <h2 class="mt-5 text-2xl font-bold">Aduan Masyarakat</h2>
                    <p class="mt-2 text-sm leading-relaxed text-orange-50/95">
                        Laporkan kondisi taman rusak, tanaman berbahaya, fasilitas tidak layak, atau masalah di area hijau.
                    </p>
                </div>
                <div class="px-6 py-5">
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-start gap-2"><span class="text-orange-500">•</span> Upload foto kondisi lapangan</li>
                        <li class="flex items-start gap-2"><span class="text-orange-500">•</span> Lacak status aduan dengan nomor tiket</li>
                        <li class="flex items-start gap-2"><span class="text-orange-500">•</span> Ditindaklanjuti tim Disperakimtan</li>
                    </ul>
                    <p class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-orange-700 group-hover:gap-3 transition-all">
                        Buat Aduan
                        <span aria-hidden="true">→</span>
                    </p>
                </div>
            </a>

            <a href="{{ route('survey.create') }}"
               class="group overflow-hidden rounded-2xl border border-violet-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg hover:shadow-violet-200/40">
                <div class="bg-gradient-to-br from-violet-400 to-fuchsia-500 px-6 py-8 text-white">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/20 text-3xl backdrop-blur-sm">⭐</div>
                    <h2 class="mt-5 text-2xl font-bold">Survey Kepuasan</h2>
                    <p class="mt-2 text-sm leading-relaxed text-violet-50/95">
                        Berikan penilaian operasional pertamanan, kondisi taman, dan respons penanganan aduan.
                    </p>
                </div>
                <div class="px-6 py-5">
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-start gap-2"><span class="text-violet-500">•</span> Penilaian bintang 1–5</li>
                        <li class="flex items-start gap-2"><span class="text-violet-500">•</span> Pilih kategori layanan yang dinilai</li>
                        <li class="flex items-start gap-2"><span class="text-violet-500">•</span> Membantu perbaikan kualitas layanan</li>
                    </ul>
                    <p class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-violet-700 group-hover:gap-3 transition-all">
                        Isi Survey
                        <span aria-hidden="true">→</span>
                    </p>
                </div>
            </a>
        </div>

        <p class="mt-8 text-center text-sm text-gray-600">
            Sudah mengirim aduan?
            <a href="{{ route('aduan.check') }}" class="font-semibold text-green-700 hover:underline">Cek status aduan di sini</a>
        </p>
    </div>
@endsection
