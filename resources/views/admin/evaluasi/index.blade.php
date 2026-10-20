@extends('layouts.admin')

@section('title', 'Evaluasi')
@section('header', 'Evaluasi & Insight Operasional')

@section('content')
    <div class="mb-6 overflow-hidden rounded-2xl border border-green-100 bg-white shadow-sm">
        <div class="border-b border-green-50 bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-5">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="max-w-2xl">
                    <h2 class="text-lg font-semibold text-green-900">Ringkasan evaluasi</h2>
                    <p class="mt-1 text-sm text-green-800/90">
                        Satu layar untuk memantau pemeliharaan, permohonan, armada, data RTH, dan masukan warga.
                        Pilih jangka waktu lalu telusuri aspek di bawah.
                    </p>
                </div>
                @if ($indeksKinerja !== null)
                    <div class="rounded-2xl border border-green-200 bg-white px-5 py-3 text-center shadow-sm">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-500">Indeks gabungan</p>
                        <p class="text-3xl font-black text-green-700">{{ $indeksKinerja }}%</p>
                        <p class="mt-0.5 text-xs text-gray-500">{{ $labelPeriode }}</p>
                    </div>
                @endif
            </div>
            <ul class="mt-4 space-y-1 text-sm text-green-900/90">
                @foreach ($highlights as $line)
                    <li class="flex gap-2">
                        <span class="text-green-600" aria-hidden="true">•</span>
                        <span>{{ $line }}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        <form action="{{ route('admin.evaluasi.index') }}" method="GET" class="space-y-4 p-6">
            <fieldset>
                <legend class="mb-3 text-sm font-semibold text-gray-800">Jangka waktu</legend>
                <div class="flex flex-wrap gap-2">
                    @foreach ($rentangOptions as $key => $label)
                        <label class="cursor-pointer">
                            <input type="radio" name="rentang" value="{{ $key }}" class="peer sr-only" @checked($rentang === $key)>
                            <span class="inline-block rounded-full border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition peer-checked:border-green-600 peer-checked:bg-green-600 peer-checked:text-white hover:border-green-400">
                                {{ $label }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <div id="evaluasi-bulan-fields" class="grid max-w-md gap-4 sm:grid-cols-2 {{ $rentang === 'bulan' ? '' : 'hidden' }}">
                <div>
                    <label for="bulan" class="mb-1 block text-sm font-medium text-gray-700">Bulan</label>
                    <select name="bulan" id="bulan"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                        @foreach ($daftarBulan as $num => $nama)
                            <option value="{{ $num }}" @selected($bulan == $num)>{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="tahun" class="mb-1 block text-sm font-medium text-gray-700">Tahun</label>
                    <select name="tahun" id="tahun"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                        @foreach ($daftarTahun as $thn)
                            <option value="{{ $thn }}" @selected($tahun == $thn)>{{ $thn }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <p class="text-sm text-gray-600">
                Periode aktif: <strong class="text-gray-900">{{ $labelPeriode }}</strong>
                @if ($rentang !== 'bulan' && $rentang !== 'semua')
                    <span class="text-gray-500">({{ $dari }} s/d {{ $sampai }})</span>
                @endif
            </p>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="rounded-lg bg-green-600 px-5 py-2 text-sm font-medium text-white hover:bg-green-700">
                    Perbarui evaluasi
                </button>
                @if ($showRapKonsolidasi)
                    <a href="{{ $rapRoute }}"
                       class="rounded-lg border border-violet-300 bg-white px-5 py-2 text-sm font-semibold text-violet-800 hover:bg-violet-50">
                        RAP Konsolidasi (PDF)
                    </a>
                @endif
            </div>
        </form>
    </div>

    <nav class="sticky top-16 z-10 mb-6 -mx-1 overflow-x-auto rounded-xl border border-gray-200 bg-white/95 px-2 py-2 shadow-sm backdrop-blur">
        <ul class="flex min-w-max gap-1 text-xs font-semibold text-gray-600">
            @foreach ($aspects as $aspect)
                <li>
                    <a href="#{{ $aspect['id'] }}"
                       class="block whitespace-nowrap rounded-lg px-3 py-2 hover:bg-green-50 hover:text-green-800">
                        {{ $aspect['icon'] }} {{ $aspect['title'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    <div class="space-y-6">
        @foreach ($aspects as $aspect)
            @include('admin.evaluasi._aspect-card', ['aspect' => $aspect])
        @endforeach
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('input[name="rentang"]').forEach(function (input) {
            input.addEventListener('change', function () {
                document.getElementById('evaluasi-bulan-fields')?.classList.toggle('hidden', input.value !== 'bulan');
            });
        });
    </script>
@endpush
