@extends('layouts.lapangan')

@section('title', 'Progres Permohonan')
@section('header', 'Isi Progres Pekerjaan')

@section('content')
    <x-lapangan.back-link :href="route('lapangan.permohonan.index')" label="Kembali ke daftar" />

    <x-lapangan.k3-banner />

    <div class="lapangan-card mb-5 p-5">
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-2xl" aria-hidden="true">{{ \App\Models\Pemangkasan::layananIcon($permohonan->jenis_layanan) }}</span>
            <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ \App\Models\Pemangkasan::badgeClass($permohonan->jenis_layanan) }}">
                {{ $permohonan->jenis_layanan }}
            </span>
            <span class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-bold text-blue-800">
                {{ $permohonan->kategori }}
            </span>
        </div>

        <h2 class="mt-3 text-lg font-black text-gray-900">{{ $permohonan->lokasi_pohon }}</h2>

        @php
            $totalHari = (int) ($permohonan->total_hari ?? 1);
            $hariTercapai = (int) ($permohonan->hari_tercapai ?? 0);
            $persen = (int) ($permohonan->persentase_progres ?? 0);
        @endphp

        <div class="mt-4 rounded-xl bg-green-50 px-4 py-3">
            <div class="flex justify-between text-sm font-semibold text-green-900">
                <span>Progres keseluruhan</span>
                <span>{{ $hariTercapai }}/{{ $totalHari }} hari ({{ $persen }}%)</span>
            </div>
            <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-green-200">
                <div class="lapangan-progress-bar h-full rounded-full" style="width: {{ max(4, $persen) }}%"></div>
            </div>
        </div>

        <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
            <div class="rounded-xl bg-gray-50 px-3 py-2.5">
                <dt class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Jadwal</dt>
                <dd class="mt-1 font-semibold text-gray-900">{{ \App\Support\PemangkasanSchedule::labelRentang($permohonan) }}</dd>
            </div>
            <div class="rounded-xl bg-gray-50 px-3 py-2.5">
                <dt class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Penanggung Jawab</dt>
                <dd class="mt-1 font-semibold text-gray-900">{{ $permohonan->penanggungjawab }}</dd>
            </div>
            <div class="rounded-xl bg-gray-50 px-3 py-2.5">
                <dt class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Asal</dt>
                <dd class="mt-1 text-gray-800">{{ $permohonan->asal }}</dd>
            </div>
            <div class="rounded-xl bg-gray-50 px-3 py-2.5">
                <dt class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Kontak</dt>
                <dd class="mt-1 text-gray-800">{{ $permohonan->kontak_permohonan }}</dd>
            </div>
            <div class="sm:col-span-2 rounded-xl bg-gray-50 px-3 py-2.5">
                <dt class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Tim Pelaksana</dt>
                <dd class="mt-1 text-gray-800">{{ $permohonan->pelaksanaLabel() ?: '—' }}</dd>
            </div>
        </dl>

        @if ($permohonan->hasPendukungPelaksanaanFile())
            <div class="mt-4 border-t border-gray-100 pt-4">
                <p class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Lampiran Admin</p>
                @if ($permohonan->pendukungPelaksanaanIsPdf())
                    <a href="{{ $permohonan->pendukung_pelaksanaan_url }}" target="_blank" rel="noopener"
                       class="mt-2 inline-flex items-center gap-2 rounded-xl border border-green-200 bg-green-50 px-3 py-2 text-sm font-semibold text-green-800">
                        📄 {{ $permohonan->pendukungPelaksanaanFilename() }}
                    </a>
                @else
                    <a href="{{ $permohonan->pendukung_pelaksanaan_url }}" target="_blank" rel="noopener" class="mt-2 inline-block">
                        <img src="{{ $permohonan->pendukung_pelaksanaan_url }}" alt="Lampiran"
                             class="h-28 rounded-xl border border-gray-200 object-cover shadow-sm">
                    </a>
                @endif
            </div>
        @endif
    </div>

    <div class="lapangan-card p-5 sm:p-6">
        <form action="{{ route('lapangan.permohonan.update', $permohonan) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-5 flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-full lapangan-step-active text-sm font-black">✎</span>
                <div>
                    <h2 class="text-base font-black text-gray-900">Form Progres Hari Ini</h2>
                    <p class="text-xs text-gray-500">Isi progres harian, lalu konfirmasi status di bagian bawah form</p>
                </div>
            </div>

            @include('lapangan.permohonan._progress-form', [
                'permohonan' => $permohonan,
                'armadaInventory' => $armadaInventory,
                'rostersByTeam' => $rostersByTeam ?? [],
            ])

            <div class="mt-8 space-y-3 border-t border-gray-100 pt-6">
                <button type="submit"
                        class="lapangan-btn-primary w-full rounded-2xl px-6 py-4 text-base font-black text-white sm:w-auto">
                    ✅ Simpan Progres
                </button>
                <a href="{{ route('lapangan.permohonan.index') }}"
                   class="block w-full rounded-2xl border-2 border-gray-200 px-6 py-3 text-center text-sm font-semibold text-gray-600 hover:bg-gray-50 sm:w-auto">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
