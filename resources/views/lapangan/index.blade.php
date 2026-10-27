@extends('layouts.lapangan')

@section('title', 'Menu Input Lapangan')
@section('header', 'Pilih Pekerjaan Hari Ini')

@section('content')
    @php
        $guestMode = $guestMode ?? false;
        $greeting = \App\Support\LapanganUi::greetingName(auth()->user(), $guestMode);
    @endphp

    <p class="mb-4 text-sm text-gray-600">Halo, <span class="font-semibold text-gray-900">{{ $greeting }}</span> — pilih pekerjaan di bawah.</p>

    <div class="grid gap-3 sm:grid-cols-2">
        @foreach ($menuItems as $item)
            @php
                $href = $item['type'] === 'permohonan'
                    ? route('lapangan.permohonan.index')
                    : route('lapangan.pemeliharaan.create', $item['slug']);
                $isPermohonan = $item['type'] === 'permohonan';
            @endphp
            <a href="{{ $href }}"
               class="lapangan-card lapangan-card-hover group block p-5 {{ $isPermohonan ? 'sm:col-span-2 border-blue-200 bg-gradient-to-br from-white to-blue-50/40' : '' }}">
                <div class="flex items-start gap-4">
                    <div class="lapangan-menu-icon flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-green-100 to-emerald-200 text-xl font-black text-green-800 shadow-inner group-hover:from-green-200 group-hover:to-emerald-300">
                        {{ $item['icon'] }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-lg font-bold text-gray-900 group-hover:text-green-800">{{ $item['label'] }}</h2>
                            @if ($isPermohonan)
                                <span class="rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-bold uppercase text-blue-800">Progres harian</span>
                            @else
                                <span class="rounded-full bg-green-100 px-2 py-0.5 text-[10px] font-bold uppercase text-green-800">Rutin</span>
                            @endif
                        </div>
                        <p class="mt-1.5 text-sm leading-relaxed text-gray-600">{{ $item['description'] }}</p>
                        <p class="mt-2 text-xs font-semibold text-green-700 group-hover:text-green-800">
                            Ketuk untuk mulai →
                        </p>
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    @if ($menuItems === [])
        <div class="lapangan-card mt-4 px-6 py-12 text-center">
            <p class="text-4xl" aria-hidden="true">🔒</p>
            <p class="mt-3 text-sm font-semibold text-gray-700">Belum ada menu untuk akun ini</p>
            <p class="mt-2 text-sm text-gray-500">Hubungi administrator untuk mendapatkan akses tim.</p>
        </div>
    @endif
@endsection
