@extends('layouts.admin')

@section('title', 'Edit Progres Permohonan')
@section('header', 'Edit Progres Permohonan')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.pemangkasans.show', $pemangkasan) }}" class="text-sm text-green-700 hover:underline">
            ← Kembali ke detail permohonan
        </a>
    </div>

    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-center gap-2">
            <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ \App\Models\Pemangkasan::badgeClass($pemangkasan->jenis_layanan) }}">
                {{ $pemangkasan->jenis_layanan }}
            </span>
            <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">
                {{ $pemangkasan->status }}
            </span>
        </div>
        <h2 class="mt-3 text-lg font-bold text-gray-900">{{ $pemangkasan->lokasi_pohon }}</h2>
        <p class="mt-1 text-sm text-gray-600">
            Jadwal {{ \App\Support\PemangkasanSchedule::labelRentang($pemangkasan) }}
            · {{ \App\Support\PemangkasanSchedule::progressSummary($pemangkasan) }}
        </p>
    </div>

    <div class="max-w-4xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('admin.pemangkasans.progres.update', [$pemangkasan, $progres]) }}"
              method="POST"
              enctype="multipart/form-data">
            @csrf
            @method('PUT')

            @include('admin.pemangkasans._progres-entry-form', [
                'pemangkasan' => $pemangkasan,
                'progres' => $progres,
            ])

            <div class="mt-6 flex gap-3">
                <button type="submit"
                        class="rounded-lg bg-green-600 px-5 py-2 text-sm font-medium text-white hover:bg-green-700">
                    Simpan Perubahan
                </button>
                <a href="{{ route('admin.pemangkasans.show', $pemangkasan) }}"
                   class="rounded-lg border border-gray-300 px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
