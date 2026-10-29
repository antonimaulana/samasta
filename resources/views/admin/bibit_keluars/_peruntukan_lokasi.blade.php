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

<div>
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
            });
        </script>
    @endpush
@endonce
