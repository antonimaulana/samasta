@php
    $jadwalAkhir = $pemangkasan->tanggal_akhir_jadwal ?? $pemangkasan->tanggal_eksekusi;
    $collectFotoDocumentation = \App\Support\PermohonanPelaksanaProgress::shouldCollectFotoDocumentation(
        $pemangkasan,
        filled($progres->tim) ? $progres->tim : null,
    );
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

    @if (filled($progres->tim))
        <div class="md:col-span-2">
            <p class="text-sm font-medium text-gray-700">Tim pelaksana</p>
            <p class="mt-1 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm font-semibold text-gray-900">{{ $progres->tim }}</p>
        </div>
    @endif

    <div class="md:col-span-2">
        <x-lapangan.field-label required>Petugas</x-lapangan.field-label>
        <x-petugas-picker
            :team-name="filled($progres->tim) ? $progres->tim : null"
            :team-names="filled($progres->tim) ? [] : ($pemangkasan->pelaksana ?? [])"
            :selected-ids="old('petugas_ids', $progres->petugas?->pluck('id')?->all() ?? [])"
            :rosters-by-team="$rostersByTeam ?? []"
            :roster-url="route('admin.tim-pelaksanas.roster')"
            variant="admin"
        />
    </div>

    <div class="md:col-span-2">
        <p class="text-sm text-gray-600">
            Hari ke-<strong>{{ $progres->hari_ke }}</strong>
            dari {{ (int) ($pemangkasan->total_hari ?? 1) }} hari jadwal
        </p>
    </div>

    @if ($collectFotoDocumentation)
        <div class="md:col-span-2">
            <x-operasional-foto-fields
                :record="$progres"
                variant="admin"
                :required="false"
            />
        </div>
    @endif

    <div class="md:col-span-2">
        <label for="catatan" class="mb-1 block text-sm font-medium text-gray-700">Catatan / Uraian Pekerjaan</label>
        <textarea name="catatan" id="catatan" rows="4"
                  class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">{{ old('catatan', $progres->catatan) }}</textarea>
        @error('catatan')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

@php
    $showAdminArmada = filled($progres->tim)
        ? $progres->tim === \App\Models\PemeliharaanTaman::TIM_ARMADA
        : $pemangkasan->usesTimArmada();
@endphp
@if ($showAdminArmada)
    @include('admin.pemeliharaan_tamans._armada-form', [
        'kinerja' => $progres,
        'armadaInventory' => $armadaInventory,
        'isTimArmada' => true,
        'operatorTim' => \App\Models\PemeliharaanTaman::TIM_ARMADA,
        'armadaVisibility' => 'always',
        'armadaRequired' => false,
    ])
@endif
