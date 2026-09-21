@extends('layouts.lapangan')

@section('title', 'Input '.$menuItem['label'])
@section('header', $menuItem['label'])

@section('content')
    <x-lapangan.back-link :href="route('lapangan.index')" label="Kembali ke menu utama" />

    <x-lapangan.motivation-banner />
    <x-lapangan.k3-banner />

    <x-lapangan.step-guide
        title="Cara isi form (4 langkah)"
        :steps="\App\Support\LapanganUi::pemeliharaanSteps()"
    />

    <div class="lapangan-card p-5 sm:p-6">
        <div class="mb-5 flex items-center gap-3 rounded-2xl border border-green-200 bg-gradient-to-r from-green-50 to-emerald-50 px-4 py-4">
            <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-600 text-2xl text-white shadow-lg">{{ $menuItem['icon'] }}</span>
            <div>
                <p class="text-xs font-bold uppercase tracking-wide text-green-700">Pemeliharaan Rutin</p>
                <p class="text-lg font-black text-green-900">{{ $tim }}</p>
                <p class="text-xs text-green-700">Isi semua bagian bertanda * (wajib)</p>
            </div>
        </div>

        <div id="lapangan-pemeliharaan-errors"
             class="mb-4 hidden rounded-2xl border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-900"
             role="alert">
            <p class="font-bold">Belum bisa disimpan — lengkapi dulu:</p>
            <ul class="mt-2 list-inside list-disc space-y-1" data-error-list></ul>
        </div>

        <form id="lapangan-pemeliharaan-form"
              data-foto-fields="{{ json_encode(array_keys(\App\Models\PemeliharaanTaman::FOTO_FIELDS)) }}"
              action="{{ route('lapangan.pemeliharaan.store', $menuItem['slug']) }}"
              method="POST"
              enctype="multipart/form-data">
            @csrf
            @include('admin.pemeliharaan_tamans._form', [
                'operatorTim' => $tim,
                'kinerja' => null,
                'tamans' => $tamans,
                'prefillTamanId' => $prefillTamanId,
                'timWilayahKelurahan' => $timWilayahKelurahan,
                'armadaInventory' => $armadaInventory,
                'rostersByTeam' => $rostersByTeam ?? [],
                'hideFormActions' => true,
            ])

            <div class="mt-8 space-y-3 border-t border-gray-100 pt-6">
                <button type="submit"
                        class="lapangan-btn-primary w-full rounded-2xl px-6 py-4 text-base font-black text-white sm:w-auto">
                    ✅ Simpan Pekerjaan
                </button>
                <a href="{{ route('lapangan.index') }}"
                   class="block w-full rounded-2xl border-2 border-gray-200 px-6 py-3 text-center text-sm font-semibold text-gray-600 hover:bg-gray-50 sm:w-auto">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/lapangan-pemeliharaan-form.js') }}?v=2"></script>
@endpush
