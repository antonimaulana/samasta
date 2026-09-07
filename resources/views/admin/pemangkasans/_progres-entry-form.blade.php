@php
    $isMiniGarden = $pemangkasan->jenis_layanan === 'Pemasangan Mini Garden';
    $jadwalAkhir = $pemangkasan->tanggal_akhir_jadwal ?? $pemangkasan->tanggal_eksekusi;
@endphp

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <x-operasional-pelaksanaan-datetime
            name="tanggal"
            :value="$progres->tanggal"
            :min="$pemangkasan->tanggal_eksekusi->format('Y-m-d').'T00:00'"
            :max="$jadwalAkhir->format('Y-m-d').'T23:59'"
        />
    </div>

    <div>
        <label for="jumlah_personil" class="mb-1 block text-sm font-medium text-gray-700">Jumlah Personil *</label>
        <input type="number" name="jumlah_personil" id="jumlah_personil" min="1" max="9999" required
               value="{{ old('jumlah_personil', $progres->jumlah_personil) }}"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
        @error('jumlah_personil')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <p class="text-sm text-gray-600">
            Hari ke-<strong>{{ $progres->hari_ke }}</strong>
            dari {{ (int) ($pemangkasan->total_hari ?? 1) }} hari jadwal
        </p>
    </div>

    @unless ($isMiniGarden)
        <div class="md:col-span-2">
            <x-operasional-foto-fields
                :record="$progres"
                variant="admin"
                :required="false"
            />
        </div>
    @endunless

    <div class="md:col-span-2">
        <label for="catatan" class="mb-1 block text-sm font-medium text-gray-700">Catatan / Uraian Pekerjaan</label>
        <textarea name="catatan" id="catatan" rows="4"
                  class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">{{ old('catatan', $progres->catatan) }}</textarea>
        @error('catatan')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

@include('admin.pemeliharaan_tamans._armada-form', [
    'kinerja' => $progres,
    'armadaInventory' => $armadaInventory,
    'isTimArmada' => $pemangkasan->usesTimArmada(),
    'operatorTim' => $pemangkasan->usesTimArmada() ? \App\Models\PemeliharaanTaman::TIM_ARMADA : null,
    'armadaVisibility' => 'always',
    'armadaRequired' => false,
])
