@extends('layouts.admin')

@section('title', 'Atur Wilayah Tim')
@section('header', 'Wilayah Kerja: '.$timPelaksana->nama)

@section('content')
    <div class="mb-6 max-w-3xl rounded-xl border border-blue-100 bg-blue-50 p-4 text-sm text-blue-900">
        Centang kelurahan yang menjadi tanggung jawab <strong>{{ $timPelaksana->nama }}</strong>.
        Satu kelurahan hanya boleh ditugaskan ke satu tim (pindah tim akan menimpa penugasan sebelumnya).
    </div>

    <form method="POST" action="{{ route('admin.tim-pelaksanas.wilayah.update', $timPelaksana) }}" class="space-y-6">
        @csrf
        @method('PUT')

        @foreach ($kecamatans as $kecamatanNama => $kelurahans)
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
                    <h3 class="font-semibold text-gray-900">{{ $kecamatanNama }}</h3>
                </div>
                <div class="grid gap-2 p-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($kelurahans as $kelurahan)
                        <label class="flex cursor-pointer items-center gap-2 rounded-lg border px-3 py-2 text-sm transition
                            {{ in_array($kelurahan->id, $assignedIds, true) ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-green-300' }}">
                            <input type="checkbox"
                                   name="kelurahan_ids[]"
                                   value="{{ $kelurahan->id }}"
                                   @checked(in_array($kelurahan->id, old('kelurahan_ids', $assignedIds), true))
                                   class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                            <span>{{ $kelurahan->nama }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="rounded-lg bg-green-600 px-5 py-2 text-sm font-semibold text-white hover:bg-green-700">
                Simpan Wilayah
            </button>
            <a href="{{ route('admin.tim-pelaksanas.index') }}"
               class="rounded-lg border border-gray-300 px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Batal
            </a>
        </div>
    </form>
@endsection
