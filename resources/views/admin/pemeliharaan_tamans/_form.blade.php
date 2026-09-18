@php
    $kinerja = $kinerja ?? null;
    $isLokasiLuar = filter_var(old('lokasi_luar', ($kinerja && ! $kinerja->taman_id) ? '1' : '0'), FILTER_VALIDATE_BOOLEAN);
    $selectedTim = old('tim', $kinerja?->tim ?? ($operatorTim ?? ''));
    $isTimArmada = $selectedTim === \App\Models\PemeliharaanTaman::TIM_ARMADA;
@endphp

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <x-operasional-pelaksanaan-datetime
            name="tanggal"
            :value="$kinerja?->tanggal"
        />
    </div>

    <div>
        <label for="tim" class="mb-1 block text-sm font-medium text-gray-700">Tim Pelaksana *</label>
        @if (filled($operatorTim ?? null))
            <input type="hidden" name="tim" id="tim" value="{{ $operatorTim }}">
            <p class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">{{ $operatorTim }}</p>
            <p class="mt-1 text-xs text-gray-500">Tim ditetapkan sesuai wilayah kerja akun operator.</p>
        @elseif (filled($operatorTimOptions ?? null))
            <select name="tim" id="tim" required
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                <option value="">Pilih tim pelaksana</option>
                @foreach ($operatorTimOptions as $tim)
                    <option value="{{ $tim }}" @selected($selectedTim === $tim)>{{ $tim }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-500">Pilih tim dari wilayah yang ditetapkan untuk akun Anda.</p>
            <div id="tim-suggest-notice" class="mt-2 hidden rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs text-blue-900"></div>
        @else
            <select name="tim" id="tim" required
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                <option value="">Pilih tim pelaksana</option>
                @foreach (\App\Models\PemeliharaanTaman::timNames() as $tim)
                    <option value="{{ $tim }}" @selected($selectedTim === $tim)>{{ $tim }}</option>
                @endforeach
            </select>
            <div id="tim-suggest-notice" class="mt-2 hidden rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs text-blue-900"></div>
        @endif
    </div>

    <div class="md:col-span-2">
        <label class="mb-1 block text-sm font-medium text-gray-700">Lokasi Pelaksanaan *</label>
        <label class="mb-3 flex cursor-pointer items-center gap-2">
            <input type="checkbox" name="lokasi_luar" id="lokasi_luar" value="1"
                   @checked($isLokasiLuar)
                   class="rounded border-gray-300 text-green-600 focus:ring-green-500">
            <span class="text-sm text-gray-700">Lokasi di luar wilayah pemeliharaan</span>
        </label>

        <div id="lokasi-taman-section" class="{{ $isLokasiLuar ? 'hidden' : '' }}">
            <x-admin.taman-select
                :tamans="$tamans ?? collect()"
                :selected="old('taman_id', $kinerja?->taman_id ?? ($prefillTamanId ?? ''))"
                :required="true"
                :hint="filled($timWilayahKelurahan ?? null) ? 'Daftar lokasi difilter sesuai wilayah kerja tim pelaksana yang dipilih.' : 'Ketik untuk memfilter, lalu pilih taman dari daftar.'"
            />
            @error('taman_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div id="lokasi-manual-section" class="{{ $isLokasiLuar ? '' : 'hidden' }}">
            <input type="text" name="lokasi_pelaksanaan" id="lokasi_pelaksanaan_manual"
                   value="{{ old('lokasi_pelaksanaan', $isLokasiLuar ? ($kinerja?->lokasi_pelaksanaan ?? '') : '') }}"
                   placeholder="Contoh: Jl. Raja Haji Fisabilillah, Batam Center"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
            @error('lokasi_pelaksanaan')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="md:col-span-2">
        <label class="mb-2 block text-sm font-medium text-gray-700">Petugas Pelaksana *</label>
        <x-petugas-picker
            :team-name="$selectedTim"
            :selected-ids="old('petugas_ids', $kinerja?->petugas?->pluck('id')?->all() ?? [])"
            :rosters-by-team="$rostersByTeam ?? []"
            :roster-url="route('admin.tim-pelaksanas.roster')"
            :tim-select-id="filled($operatorTim ?? null) ? null : 'tim'"
            :variant="request()->routeIs('lapangan.*') ? 'lapangan' : 'admin'"
        />
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Hari Ke / Total Hari</label>
        <div class="flex items-center gap-2">
            <input type="number" name="hari_ke" id="hari_ke" min="1" max="9999"
                   value="{{ old('hari_ke', $kinerja?->hari_ke) }}"
                   placeholder="Hari ke"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
            <span class="text-sm text-gray-500">/</span>
            <input type="number" name="total_hari" id="total_hari" min="1" max="9999"
                   value="{{ old('total_hari', $kinerja?->total_hari) }}"
                   placeholder="Total hari"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
        </div>
        @error('hari_ke')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
        @error('total_hari')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="persentase_progres" class="mb-1 block text-sm font-medium text-gray-700">Persentase Progres (%)</label>
        <input type="number" name="persentase_progres" id="persentase_progres" min="0" max="100"
               value="{{ old('persentase_progres', $kinerja?->persentase_progres) }}"
               placeholder="0–100"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
        @error('persentase_progres')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

@include('admin.pemeliharaan_tamans._armada-form', compact('isTimArmada', 'kinerja', 'armadaInventory', 'operatorTim'))

<div class="mt-8 border-t border-gray-100 pt-6">
    <label for="uraian_pekerjaan" class="mb-1 block text-sm font-medium text-gray-700">Uraian Pekerjaan</label>
    <textarea name="uraian_pekerjaan" id="uraian_pekerjaan" rows="4"
              placeholder="Deskripsikan pekerjaan pemeliharaan yang dilakukan"
              class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">{{ old('uraian_pekerjaan', $kinerja?->uraian_pekerjaan ?? '') }}</textarea>
    @error('uraian_pekerjaan')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<x-operasional-foto-fields :record="$kinerja" :required="! $kinerja" />

@unless($hideFormActions ?? false)
<div class="mt-6 flex gap-3">
    <button type="submit"
            class="rounded-lg bg-green-600 px-5 py-2 text-sm font-medium text-white hover:bg-green-700">
        Simpan
    </button>
    <a href="{{ $cancelUrl ?? route('admin.pemeliharaan-tamans.index', [
        'tanggal_mulai' => old('tanggal', \App\Support\OperasionalPelaksanaanTime::datePart($kinerja?->tanggal)),
        'tanggal_selesai' => old('tanggal', \App\Support\OperasionalPelaksanaanTime::datePart($kinerja?->tanggal)),
    ]) }}"
       class="rounded-lg border border-gray-300 px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
        Batal
    </a>
</div>
@endunless

@push('scripts')
    @if (! empty($timWilayahKelurahan) && blank($operatorTim ?? null))
        @include('admin.partials.tim-wilayah-taman-filter', [
            'timWilayahKelurahan' => $timWilayahKelurahan,
            'timSelectId' => 'tim',
            'tamanInputId' => 'taman_id',
        ])
    @endif
    @if (blank($operatorTim ?? null))
        @include('admin.partials.tim-auto-suggest', ['mode' => 'select', 'pelaksanaSelectId' => 'tim'])
    @endif
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const lokasiLuarCheckbox = document.getElementById('lokasi_luar');
            const lokasiTamanSection = document.getElementById('lokasi-taman-section');
            const lokasiManualSection = document.getElementById('lokasi-manual-section');
            const tamanHiddenInput = document.getElementById('taman_id');
            const tamanSearchInput = document.getElementById('taman_id_search');
            const lokasiManualInput = document.getElementById('lokasi_pelaksanaan_manual');

            function toggleLokasiMode() {
                const isManual = lokasiLuarCheckbox?.checked ?? false;
                lokasiTamanSection?.classList.toggle('hidden', isManual);
                lokasiManualSection?.classList.toggle('hidden', ! isManual);

                if (tamanHiddenInput) {
                    if (isManual) {
                        tamanHiddenInput.value = '';
                        tamanHiddenInput.removeAttribute('data-searchable-required');
                    } else {
                        tamanHiddenInput.setAttribute('data-searchable-required', 'true');
                    }
                }

                if (tamanSearchInput) {
                    tamanSearchInput.disabled = isManual;
                    tamanSearchInput.classList.toggle('bg-gray-100', isManual);
                    tamanSearchInput.classList.toggle('cursor-not-allowed', isManual);
                    if (isManual) {
                        tamanSearchInput.value = '';
                    }
                }

                if (lokasiManualInput) {
                    lokasiManualInput.required = isManual;
                    if (! isManual) {
                        lokasiManualInput.value = '';
                    }
                }
            }

            lokasiLuarCheckbox?.addEventListener('change', toggleLokasiMode);
            toggleLokasiMode();
        });
    </script>
@endpush
