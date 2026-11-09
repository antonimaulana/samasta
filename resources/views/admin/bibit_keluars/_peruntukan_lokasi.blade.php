@php
    $isLokasiLuar = filter_var(old('lokasi_luar', ($batch && ! $batch->taman_id) ? '1' : '0'), FILTER_VALIDATE_BOOLEAN);
    $manualLokasiValue = old('lokasi', $isLokasiLuar ? ($batch?->lokasi ?? '') : '');
@endphp

<div>
    <label for="peruntukan" class="mb-1 block text-sm font-medium text-gray-700">Peruntukan *</label>
    <select name="peruntukan" id="peruntukan" required
            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
        <option value="">-- Pilih peruntukan --</option>
        @foreach (\App\Models\BibitKeluarBatch::PERUNTUKAN as $peruntukan)
            <option value="{{ $peruntukan }}" @selected(old('peruntukan', $batch?->peruntukan) === $peruntukan)>
                {{ $peruntukan }}
            </option>
        @endforeach
    </select>
</div>

<div id="bibit-keluar-dokumen-section" class="hidden">
    <label for="dokumen_pendukung" class="mb-1 block text-sm font-medium text-gray-700">Dokumen Pendukung *</label>
    @if ($batch?->dokumen_pendukung)
        <p class="mb-2 text-xs text-gray-500">
            Dokumen saat ini:
            @if ($batch->dokumenPendukungUrl())
                <a href="{{ $batch->dokumenPendukungUrl() }}" target="_blank" rel="noopener" class="font-medium text-green-700 hover:underline">Lihat file</a>
            @else
                <span class="text-amber-700">File tidak ditemukan di server</span>
            @endif
        </p>
    @endif
    <input type="file" name="dokumen_pendukung" id="dokumen_pendukung" accept=".pdf,image/*"
           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-green-50 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-green-700 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
    <p class="mt-1 text-xs text-gray-500">PDF atau gambar, maks. 8 MB. Wajib untuk peruntukan permintaan masyarakat/instansi.</p>
    @error('dokumen_pendukung')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div id="bibit-keluar-lokasi-section">
    <label class="mb-1 block text-sm font-medium text-gray-700">Lokasi *</label>
    <label class="mb-3 flex cursor-pointer items-center gap-2">
        <input type="checkbox" name="lokasi_luar" id="bibit_keluar_lokasi_luar" value="1"
               @checked($isLokasiLuar)
               class="rounded border-gray-300 text-green-600 focus:ring-green-500">
        <span class="text-sm text-gray-700">Lokasi di luar wilayah pemeliharaan</span>
    </label>

    <div id="bibit-keluar-lokasi-taman-section" class="{{ $isLokasiLuar ? 'hidden' : '' }}">
        <x-admin.taman-select
            :tamans="$tamans ?? collect()"
            :selected="old('taman_id', $batch?->taman_id)"
            :required="true"
        />
        @error('taman_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div id="bibit-keluar-lokasi-manual-section" class="{{ $isLokasiLuar ? '' : 'hidden' }}">
        <input type="text" name="lokasi" id="bibit_keluar_lokasi_manual"
               value="{{ $manualLokasiValue }}"
               placeholder="Contoh: Jl. Raja Haji Fisabilillah, Batam Center"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
        @error('lokasi')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const lokasiLuarCheckbox = document.getElementById('bibit_keluar_lokasi_luar');
                const lokasiTamanSection = document.getElementById('bibit-keluar-lokasi-taman-section');
                const lokasiManualSection = document.getElementById('bibit-keluar-lokasi-manual-section');
                const tamanHiddenInput = document.getElementById('taman_id');
                const tamanSearchInput = document.getElementById('taman_id_search');
                const lokasiManualInput = document.getElementById('bibit_keluar_lokasi_manual');
                const peruntukanSelect = document.getElementById('peruntukan');
                const dokumenSection = document.getElementById('bibit-keluar-dokumen-section');
                const dokumenInput = document.getElementById('dokumen_pendukung');
                const permintaanValue = @json(\App\Models\BibitKeluarBatch::PERUNTUKAN_PERMINTAAN);
                const afkirValue = @json(\App\Models\BibitKeluarBatch::PERUNTUKAN_AFKIR);
                const lokasiSection = document.getElementById('bibit-keluar-lokasi-section');

                function toggleDokumenPendukung() {
                    const needsDoc = peruntukanSelect?.value === permintaanValue;
                    dokumenSection?.classList.toggle('hidden', ! needsDoc);
                    if (dokumenInput) {
                        dokumenInput.required = needsDoc && ! @json((bool) ($batch?->dokumen_pendukung));
                    }
                }

                function toggleLokasiSection() {
                    const hideLokasi = peruntukanSelect?.value === afkirValue;
                    lokasiSection?.classList.toggle('hidden', hideLokasi);

                    if (hideLokasi) {
                        lokasiLuarCheckbox && (lokasiLuarCheckbox.checked = false);
                        if (tamanHiddenInput) {
                            tamanHiddenInput.value = '';
                            tamanHiddenInput.removeAttribute('required');
                            tamanHiddenInput.removeAttribute('data-searchable-required');
                        }
                        if (lokasiManualInput) {
                            lokasiManualInput.value = '';
                            lokasiManualInput.required = false;
                        }
                    } else {
                        toggleLokasiMode();
                    }
                }

                function toggleLokasiMode() {
                    if (peruntukanSelect?.value === afkirValue) {
                        return;
                    }

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
                peruntukanSelect?.addEventListener('change', function () {
                    toggleDokumenPendukung();
                    toggleLokasiSection();
                });
                toggleLokasiSection();
                toggleDokumenPendukung();
            });
        </script>
    @endpush
@endonce
