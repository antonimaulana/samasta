@php
    $kinerja = $kinerja ?? null;
    $isLapanganForm = request()->routeIs('lapangan.*');
    $isLokasiLuar = filter_var(old('lokasi_luar', ($kinerja && ! $kinerja->taman_id) ? '1' : '0'), FILTER_VALIDATE_BOOLEAN);
    $selectedTim = old('tim', $kinerja?->tim ?? ($operatorTim ?? ''));
    $isTimArmada = $selectedTim === \App\Models\PemeliharaanTaman::TIM_ARMADA;
    $labelClass = $isLapanganForm ? 'lapangan-field-label' : 'mb-1 block text-sm font-medium text-gray-700';
    $inputClass = $isLapanganForm
        ? 'lapangan-field-input focus:border-green-500 focus:outline-none focus:ring-4 focus:ring-green-100'
        : 'w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500';
    $tamanInputClass = $isLapanganForm
        ? 'lapangan-field-input focus:border-green-500 focus:outline-none focus:ring-4 focus:ring-green-100'
        : 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500';
@endphp

@if ($errors->any() && ! $isLapanganForm)
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        <p class="font-semibold">Form belum bisa disimpan:</p>
        <ul class="mt-1 list-inside list-disc space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div @class([
    'space-y-6' => $isLapanganForm,
    'grid gap-6 md:grid-cols-2' => ! $isLapanganForm,
])>
    @if ($isLapanganForm)
        <section class="lapangan-form-section">
            <p class="lapangan-section-title text-sm font-black text-gray-900">Waktu &amp; tim</p>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
    @endif
    <div>
        <x-operasional-pelaksanaan-datetime
            name="tanggal"
            :value="$kinerja?->tanggal"
            :variant="$isLapanganForm ? 'lapangan' : 'admin'"
        />
    </div>

    <div>
        @if ($isLapanganForm)
            <x-lapangan.field-label for="tim" :required="true">Tim pelaksana</x-lapangan.field-label>
        @else
            <label for="tim" class="{{ $labelClass }}">Tim Pelaksana *</label>
        @endif
        @if (filled($operatorTim ?? null))
            <input type="hidden" name="tim" id="tim" value="{{ $operatorTim }}">
            <p @class([
                'text-sm font-semibold text-gray-800',
                'lapangan-field-input lapangan-field-input--readonly' => $isLapanganForm,
                'w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-gray-700' => ! $isLapanganForm,
            ])>{{ $operatorTim }}</p>
            @unless($isLapanganForm)
                <p class="mt-1 text-xs text-gray-500">Tim ditetapkan sesuai wilayah kerja akun operator.</p>
            @endunless
        @elseif (filled($operatorTimOptions ?? null))
            <select name="tim" id="tim" required class="{{ $inputClass }}">
                <option value="">Pilih tim pelaksana</option>
                @foreach ($operatorTimOptions as $tim)
                    <option value="{{ $tim }}" @selected($selectedTim === $tim)>{{ $tim }}</option>
                @endforeach
            </select>
            @unless($isLapanganForm)
                <p class="mt-1 text-xs text-gray-500">Pilih tim dari wilayah yang ditetapkan untuk akun Anda.</p>
            @endunless
            <div id="tim-suggest-notice" class="mt-2 hidden rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs text-blue-900"></div>
        @else
            <select name="tim" id="tim" required class="{{ $inputClass }}">
                <option value="">Pilih tim pelaksana</option>
                @foreach (\App\Models\PemeliharaanTaman::timNames() as $tim)
                    <option value="{{ $tim }}" @selected($selectedTim === $tim)>{{ $tim }}</option>
                @endforeach
            </select>
            <div id="tim-suggest-notice" class="mt-2 hidden rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs text-blue-900"></div>
        @endif
    </div>
    @if ($isLapanganForm)
            </div>
        </section>
    @endif

    <div @class(['lapangan-form-section' => $isLapanganForm, 'md:col-span-2' => ! $isLapanganForm])>
        @if ($isLapanganForm)
            <x-lapangan.field-label required>Lokasi pelaksanaan</x-lapangan.field-label>
        @else
            <x-lapangan.field-label required>Lokasi pelaksanaan</x-lapangan.field-label>
        @endif
        <label @class([
            'mb-3 flex cursor-pointer items-center gap-2' => ! $isLapanganForm,
            'lapangan-check-row' => $isLapanganForm,
        ])>
            <input type="checkbox" name="lokasi_luar" id="lokasi_luar" value="1"
                   @checked($isLokasiLuar)
                   class="rounded border-gray-300 text-green-600 focus:ring-green-500">
            <span class="text-sm font-medium text-gray-800">Lokasi di luar wilayah pemeliharaan</span>
        </label>

        <div id="lokasi-taman-section" class="{{ $isLokasiLuar ? 'hidden' : '' }}">
            <x-admin.taman-select
                :tamans="$tamans ?? collect()"
                :selected="old('taman_id', $kinerja?->taman_id ?? ($prefillTamanId ?? ''))"
                :required="true"
                :show-hint="false"
                :input-class="$tamanInputClass"
                :placeholder="$isLapanganForm ? 'Ketik nama taman, lalu pilih dari daftar' : 'Ketik nama taman, kategori, atau alamat...'"
            />
            @error('taman_id')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div id="lokasi-manual-section" class="{{ $isLokasiLuar ? '' : 'hidden' }}">
            @if ($isLapanganForm)
                <x-lapangan.field-label for="lokasi_pelaksanaan_manual" :required="true">Alamat lokasi</x-lapangan.field-label>
            @endif
            <input type="text" name="lokasi_pelaksanaan" id="lokasi_pelaksanaan_manual"
                   value="{{ old('lokasi_pelaksanaan', $isLokasiLuar ? ($kinerja?->lokasi_pelaksanaan ?? '') : '') }}"
                   placeholder="Contoh: Jl. Raja Haji Fisabilillah, Batam Center"
                   class="{{ $inputClass }}">
            @error('lokasi_pelaksanaan')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div @class(['lapangan-form-section' => $isLapanganForm, 'md:col-span-2' => ! $isLapanganForm])>
        @if ($isLapanganForm)
            <x-lapangan.field-label required>Petugas</x-lapangan.field-label>
        @else
            <x-lapangan.field-label required>Petugas</x-lapangan.field-label>
        @endif
        <x-petugas-picker
            :team-name="$selectedTim"
            :selected-ids="old('petugas_ids', $kinerja?->petugas?->pluck('id')?->all() ?? [])"
            :rosters-by-team="$rostersByTeam ?? []"
            :roster-url="request()->routeIs('lapangan.*') ? route('lapangan.tim-pelaksanas.roster') : route('admin.tim-pelaksanas.roster')"
            :tim-select-id="filled($operatorTim ?? null) ? null : 'tim'"
            roster-scope="operasional-wilayah"
            :variant="request()->routeIs('lapangan.*') ? 'lapangan' : 'admin'"
        />
    </div>

    <div @class(['lapangan-form-section' => $isLapanganForm])>
        @if ($isLapanganForm)
            <p class="lapangan-section-title text-sm font-black text-gray-900">Progres pekerjaan</p>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
        @endif
        <div @class(['sm:col-span-2' => $isLapanganForm])>
        <label class="{{ $labelClass }}">Hari Ke / Total Hari</label>
        <div class="flex items-center gap-2">
            <input type="number" name="hari_ke" id="hari_ke" min="1" max="9999"
                   value="{{ old('hari_ke', $kinerja?->hari_ke) }}"
                   placeholder="Hari ke"
                   class="{{ $inputClass }}">
            <span class="text-sm font-medium text-gray-500">/</span>
            <input type="number" name="total_hari" id="total_hari" min="1" max="9999"
                   value="{{ old('total_hari', $kinerja?->total_hari) }}"
                   placeholder="Total hari"
                   class="{{ $inputClass }}">
        </div>
        @error('hari_ke')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
        @error('total_hari')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="persentase_progres" class="{{ $labelClass }}">Persentase Progres (%)</label>
        <input type="number" name="persentase_progres" id="persentase_progres" min="0" max="100"
               value="{{ old('persentase_progres', $kinerja?->persentase_progres) }}"
               placeholder="0–100"
               class="{{ $inputClass }}">
        @error('persentase_progres')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
        @if ($isLapanganForm)
            </div>
        @endif
    </div>
</div>

@include('admin.pemeliharaan_tamans._armada-form', compact('isTimArmada', 'kinerja', 'armadaInventory', 'operatorTim'))

<div @class([
    'mt-8 border-t border-gray-100 pt-6' => ! $isLapanganForm,
    'lapangan-form-section' => $isLapanganForm,
])>
    @if ($isLapanganForm)
        <x-lapangan.field-label for="uraian_pekerjaan">Uraian pekerjaan</x-lapangan.field-label>
    @else
        <label for="uraian_pekerjaan" class="{{ $labelClass }}">Uraian Pekerjaan</label>
    @endif
    <textarea name="uraian_pekerjaan" id="uraian_pekerjaan" rows="4"
              placeholder="Deskripsikan pekerjaan pemeliharaan yang dilakukan"
              class="{{ $inputClass }} min-h-[6rem]">{{ old('uraian_pekerjaan', $kinerja?->uraian_pekerjaan ?? '') }}</textarea>
    @error('uraian_pekerjaan')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<x-operasional-foto-fields
    :record="$kinerja"
    :required="! $kinerja"
    :variant="$isLapanganForm ? 'lapangan' : 'admin'"
/>

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
                        tamanHiddenInput.removeAttribute('required');
                    } else {
                        tamanHiddenInput.setAttribute('data-searchable-required', 'true');
                        tamanHiddenInput.setAttribute('required', 'required');
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

            const pemeliharaanForm = document.getElementById('pemeliharaan-taman-form');
            pemeliharaanForm?.addEventListener('submit', function (event) {
                pemeliharaanForm.querySelectorAll('.petugas-picker').forEach(function (root) {
                    root.dispatchEvent(new CustomEvent('petugas-picker:commit', { bubbles: true }));
                });

                if (pemeliharaanForm.hasAttribute('novalidate')) {
                    pemeliharaanForm.dataset.pemeliharaanSubmitOk = '1';
                    return;
                }

                if (pemeliharaanForm.checkValidity()) {
                    return;
                }

                event.preventDefault();
                pemeliharaanForm.reportValidity();
                const firstInvalid = pemeliharaanForm.querySelector(':invalid');
                firstInvalid?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, true);
        });
    </script>
@endpush
