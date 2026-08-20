@extends('layouts.public')

@section('title', 'Cek Status Aduan')

@section('main_class', 'mx-auto max-w-2xl px-4 py-12 sm:px-6 lg:px-8')

@section('content')
    <div class="mb-8 text-center">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-lime-400 to-green-500 text-2xl shadow-md shadow-green-400/30">
            🔍
        </div>
        <h1 class="mt-4 text-2xl font-black text-gray-900">Cek Status Aduan</h1>
        <p class="mt-2 text-sm text-gray-600">Masukkan nomor aduan dan 4 digit terakhir nomor HP/WA yang Anda gunakan saat mengirim aduan.</p>
    </div>

    <form method="POST" action="{{ route('aduan.check.submit') }}"
          class="rounded-2xl border border-green-200 bg-white p-6 shadow-sm">
        @csrf
        <div class="hidden" aria-hidden="true">
            <label for="_website">Website</label>
            <input type="text" name="_website" id="_website" tabindex="-1" autocomplete="off">
        </div>
        <label for="nomor_aduan" class="mb-1 block text-sm font-medium text-gray-700">Nomor Aduan *</label>
        <input type="text" name="nomor_aduan" id="nomor_aduan" required
               value="{{ old('nomor_aduan', $nomorInput ?? '') }}"
               placeholder="ADU-YYYYMMDD-0001"
               class="w-full rounded-xl border border-gray-300 px-4 py-3 font-mono text-sm uppercase focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-500/20">
        @error('nomor_aduan')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
        <label for="kontak_verifikasi" class="mb-1 mt-4 block text-sm font-medium text-gray-700">4 Digit Terakhir Nomor HP/WA *</label>
        <input type="text" name="kontak_verifikasi" id="kontak_verifikasi" required
               inputmode="numeric" pattern="\d{4}" maxlength="4"
               value="{{ old('kontak_verifikasi') }}"
               placeholder="1234"
               class="w-full rounded-xl border border-gray-300 px-4 py-3 font-mono text-sm focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-500/20">
        @error('kontak_verifikasi')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
        <button type="submit"
                class="mt-4 w-full rounded-xl bg-green-600 px-4 py-3 text-sm font-bold text-white hover:bg-green-700">
            Cek Status
        </button>
    </form>

    @if (! empty($notFound))
        <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-900">
            Data aduan tidak ditemukan atau verifikasi nomor HP tidak cocok. Periksa kembali nomor aduan dan 4 digit terakhir nomor HP/WA Anda.
        </div>
    @endif

    @if (! empty($summary))
        <div class="mt-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Nomor Aduan</p>
                    <p class="font-mono text-xl font-black text-gray-900">{{ $summary['nomor_aduan'] }}</p>
                </div>
                <span class="rounded-full px-3 py-1 text-xs font-bold {{ \App\Models\AduanMasyarakat::statusBadgeClass($summary['status']) }}">
                    {{ $summary['status'] }}
                </span>
            </div>

            <dl class="mt-6 space-y-4 text-sm">
                <div class="flex justify-between gap-4 border-b border-gray-100 pb-3">
                    <dt class="text-gray-500">Jenis aduan</dt>
                    <dd class="text-right font-semibold text-gray-900">{{ $summary['jenis_aduan'] }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-gray-100 pb-3">
                    <dt class="text-gray-500">Lokasi</dt>
                    <dd class="text-right font-semibold text-gray-900">{{ $summary['lokasi'] }}</dd>
                </div>
                @if ($summary['taman'])
                    <div class="flex justify-between gap-4 border-b border-gray-100 pb-3">
                        <dt class="text-gray-500">Taman</dt>
                        <dd class="text-right font-semibold text-gray-900">{{ $summary['taman'] }}</dd>
                    </div>
                @endif
                <div class="flex justify-between gap-4 border-b border-gray-100 pb-3">
                    <dt class="text-gray-500">Diterima</dt>
                    <dd class="text-right text-gray-800">{{ $summary['diterima']->translatedFormat('d F Y, H:i') }} WIB</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">Terakhir diperbarui</dt>
                    <dd class="text-right text-gray-800">{{ $summary['diperbarui']->translatedFormat('d F Y, H:i') }} WIB</dd>
                </div>
            </dl>

            @if ($summary['catatan'])
                <div class="mt-6 rounded-xl bg-lime-50 px-4 py-3 ring-1 ring-lime-200">
                    <p class="text-xs font-bold uppercase tracking-wider text-green-700">Catatan tim</p>
                    <p class="mt-2 whitespace-pre-line text-sm text-gray-700">{{ $summary['catatan'] }}</p>
                </div>
            @endif
        </div>
    @endif

    <div class="mt-8 flex flex-wrap justify-center gap-3 text-sm">
        <a href="{{ route('aduan.create') }}" class="font-semibold text-green-700 hover:underline">Kirim aduan baru</a>
        <span class="text-gray-300">|</span>
        <a href="{{ route('home') }}" class="font-semibold text-gray-600 hover:underline">Kembali ke beranda</a>
    </div>
@endsection
