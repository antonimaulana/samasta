@php
    use App\Models\Taman;

    $fasilitasItems = old('fasilitas_items');
    if ($fasilitasItems === null) {
        $fasilitasItems = $taman?->fasilitas_items ?? [['nama' => '', 'kondisi' => '']];
    }
    if ($fasilitasItems === []) {
        $fasilitasItems = [['nama' => '', 'kondisi' => '']];
    }

    $formControlClass = 'w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500';

    $resolveFasilitasRow = static function (array $item): array {
        $storedNama = trim((string) ($item['nama'] ?? ''));
        $customInput = trim((string) ($item['nama_custom'] ?? ''));

        if ($storedNama === Taman::FASILITAS_CUSTOM_VALUE) {
            return [
                'select' => Taman::FASILITAS_CUSTOM_VALUE,
                'custom' => $customInput,
            ];
        }

        if ($storedNama !== '' && ! in_array($storedNama, Taman::FASILITAS_DAFTAR, true)) {
            return [
                'select' => Taman::FASILITAS_CUSTOM_VALUE,
                'custom' => $storedNama,
            ];
        }

        return [
            'select' => $storedNama,
            'custom' => '',
        ];
    };
@endphp

<x-admin.rth-section
    title="Fasilitas & Kondisi"
    description="Pilih dari daftar standar atau “Input sendiri” untuk mengetik fasilitas yang belum ada di daftar.">
    <x-slot:actions>
        <button type="button" id="add-fasilitas-row"
                class="rounded-lg border border-emerald-200 bg-white px-3 py-1.5 text-sm font-medium text-emerald-800 hover:bg-emerald-50">
            + Tambah Fasilitas
        </button>
    </x-slot:actions>

    <div id="fasilitas-rows" class="space-y-3">
        @foreach ($fasilitasItems as $index => $item)
            @php
                $row = $resolveFasilitasRow(is_array($item) ? $item : []);
                $selectValue = $row['select'];
                $customValue = $row['custom'];
                $isCustomMode = $selectValue === Taman::FASILITAS_CUSTOM_VALUE;
                $fasilitasNamaFilled = $selectValue !== ''
                    && ($selectValue !== Taman::FASILITAS_CUSTOM_VALUE || $customValue !== '');
            @endphp
            <div class="fasilitas-row grid grid-cols-[minmax(0,1fr)_minmax(5.75rem,7rem)_auto] items-end gap-2 rounded-lg border border-gray-200 bg-gray-50 p-3 sm:gap-3 sm:grid-cols-[minmax(0,1fr)_180px_auto]">
                <div class="min-w-0">
                    <x-admin.form.label class="text-xs sm:text-sm">Fasilitas</x-admin.form.label>
                    <select name="fasilitas_items[{{ $index }}][nama]"
                            class="fasilitas-nama-select {{ $formControlClass }} {{ $isCustomMode ? 'hidden' : '' }}">
                        <option value="">Pilih fasilitas</option>
                        @foreach (Taman::FASILITAS_DAFTAR as $fasilitas)
                            <option value="{{ $fasilitas }}" @selected($selectValue === $fasilitas)>{{ $fasilitas }}</option>
                        @endforeach
                        <option value="{{ Taman::FASILITAS_CUSTOM_VALUE }}" @selected($isCustomMode)>Input sendiri</option>
                    </select>
                    <div class="fasilitas-custom-wrap {{ $isCustomMode ? '' : 'hidden' }}">
                        <input type="text"
                               name="fasilitas_items[{{ $index }}][nama_custom]"
                               value="{{ $customValue }}"
                               maxlength="{{ Taman::FASILITAS_NAMA_MAX_LENGTH }}"
                               placeholder="Ketik nama fasilitas…"
                               class="fasilitas-nama-custom {{ $formControlClass }}">
                        <button type="button"
                                class="fasilitas-use-standard mt-1.5 text-xs font-medium text-emerald-700 hover:text-emerald-900 hover:underline">
                            ← Pilih dari daftar standar
                        </button>
                    </div>
                </div>
                <div class="min-w-0">
                    <x-admin.form.label class="text-xs sm:text-sm">Kondisi</x-admin.form.label>
                    <select name="fasilitas_items[{{ $index }}][kondisi]"
                            class="fasilitas-kondisi-select {{ $formControlClass }}"
                            @disabled(! $fasilitasNamaFilled)>
                        <option value="">Pilih kondisi</option>
                        @foreach (Taman::FASILITAS_KONDISI as $kondisi)
                            <option value="{{ $kondisi }}" @selected(($item['kondisi'] ?? '') === $kondisi)>{{ $kondisi }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex shrink-0 items-end">
                    <button type="button"
                            class="remove-fasilitas-row rounded-lg border border-red-200 bg-white px-2.5 py-2 text-xs font-medium text-red-700 hover:bg-red-50 sm:px-3 sm:text-sm"
                            title="Hapus baris fasilitas"
                            aria-label="Hapus baris fasilitas">
                        <span class="sm:hidden" aria-hidden="true">✕</span>
                        <span class="hidden sm:inline">Hapus</span>
                    </button>
                </div>
            </div>
        @endforeach
    </div>
</x-admin.rth-section>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('fasilitas-rows');
            const addButton = document.getElementById('add-fasilitas-row');
            if (!container || !addButton) return;

            const fasilitasDaftar = @json(Taman::FASILITAS_DAFTAR);
            const fasilitasCustomValue = @json(Taman::FASILITAS_CUSTOM_VALUE);
            const fasilitasNamaMaxLength = @json(Taman::FASILITAS_NAMA_MAX_LENGTH);
            const kondisiOptions = @json(Taman::FASILITAS_KONDISI);
            const formControlClass = @json($formControlClass);

            function buildFasilitasOptions(selected) {
                let html = '<option value="">Pilih fasilitas</option>';
                fasilitasDaftar.forEach(function (fasilitas) {
                    const isSelected = selected === fasilitas ? ' selected' : '';
                    html += '<option value="' + fasilitas + '"' + isSelected + '>' + fasilitas + '</option>';
                });
                const customSelected = selected === fasilitasCustomValue ? ' selected' : '';
                html += '<option value="' + fasilitasCustomValue + '"' + customSelected + '>Input sendiri</option>';
                return html;
            }

            function buildKondisiOptions(selected) {
                let html = '<option value="">Pilih kondisi</option>';
                kondisiOptions.forEach(function (kondisi) {
                    const isSelected = selected === kondisi ? ' selected' : '';
                    html += '<option value="' + kondisi + '"' + isSelected + '>' + kondisi + '</option>';
                });
                return html;
            }

            function getEffectiveNama(row) {
                const namaSelect = row.querySelector('.fasilitas-nama-select');
                const customInput = row.querySelector('.fasilitas-nama-custom');
                if (!namaSelect) {
                    return '';
                }
                if (namaSelect.value === fasilitasCustomValue) {
                    return (customInput?.value || '').trim();
                }
                return namaSelect.value.trim();
            }

            function toggleFasilitasInputMode(row, focusCustom) {
                const namaSelect = row.querySelector('.fasilitas-nama-select');
                const customWrap = row.querySelector('.fasilitas-custom-wrap');
                const customInput = row.querySelector('.fasilitas-nama-custom');
                if (!namaSelect || !customWrap) {
                    return;
                }

                const isCustom = namaSelect.value === fasilitasCustomValue;
                namaSelect.classList.toggle('hidden', isCustom);
                customWrap.classList.toggle('hidden', !isCustom);

                if (isCustom) {
                    if (focusCustom) {
                        customInput?.focus();
                    }
                } else {
                    if (customInput) {
                        customInput.value = '';
                    }
                }
            }

            function showStandardPicker(row) {
                const namaSelect = row.querySelector('.fasilitas-nama-select');
                if (!namaSelect) {
                    return;
                }
                namaSelect.value = '';
                toggleFasilitasInputMode(row, false);
            }

            function rowHasFasilitas(row) {
                const namaSelect = row.querySelector('.fasilitas-nama-select');
                if (!namaSelect || namaSelect.value === '') {
                    return false;
                }
                if (namaSelect.value === fasilitasCustomValue) {
                    return getEffectiveNama(row) !== '';
                }
                return true;
            }

            function syncKondisiFields() {
                container.querySelectorAll('.fasilitas-row').forEach(function (row) {
                    const kondisiSelect = row.querySelector('.fasilitas-kondisi-select');
                    if (!kondisiSelect) {
                        return;
                    }
                    const hasFasilitas = rowHasFasilitas(row);
                    kondisiSelect.disabled = !hasFasilitas;
                    if (!hasFasilitas) {
                        kondisiSelect.value = '';
                    }
                });
            }

            function syncFasilitasOptions() {
                const selected = Array.from(container.querySelectorAll('.fasilitas-row'))
                    .map(getEffectiveNama)
                    .filter(Boolean)
                    .map(function (nama) { return nama.toLowerCase(); });

                container.querySelectorAll('.fasilitas-row').forEach(function (row) {
                    const namaSelect = row.querySelector('.fasilitas-nama-select');
                    if (!namaSelect) {
                        return;
                    }

                    const previousValue = namaSelect.dataset.lastValue || '';
                    const currentValue = namaSelect.value;
                    const switchedToCustom = currentValue === fasilitasCustomValue && previousValue !== fasilitasCustomValue;
                    namaSelect.dataset.lastValue = currentValue;

                    const currentEffective = getEffectiveNama(row).toLowerCase();
                    Array.from(namaSelect.options).forEach(function (option) {
                        if (option.value === '' || option.value === fasilitasCustomValue) {
                            option.disabled = false;
                            return;
                        }
                        if (option.value === namaSelect.value && namaSelect.value !== fasilitasCustomValue) {
                            option.disabled = false;
                            return;
                        }
                        option.disabled = selected.includes(option.value.toLowerCase())
                            && option.value.toLowerCase() !== currentEffective;
                    });

                    toggleFasilitasInputMode(row, switchedToCustom);
                });
                syncKondisiFields();
            }

            function bindFasilitasSelects() {
                container.querySelectorAll('.fasilitas-nama-select').forEach(function (select) {
                    select.dataset.lastValue = select.value;
                    select.onchange = syncFasilitasOptions;
                });
                container.querySelectorAll('.fasilitas-nama-custom').forEach(function (input) {
                    input.oninput = syncFasilitasOptions;
                });
            }

            function bindStandardPickerButtons() {
                container.querySelectorAll('.fasilitas-use-standard').forEach(function (button) {
                    button.onclick = function () {
                        const row = button.closest('.fasilitas-row');
                        if (row) {
                            showStandardPicker(row);
                            syncFasilitasOptions();
                        }
                    };
                });
            }

            function bindRemoveButtons() {
                container.querySelectorAll('.remove-fasilitas-row').forEach(function (button) {
                    button.onclick = function () {
                        const rows = container.querySelectorAll('.fasilitas-row');
                        if (rows.length <= 1) {
                            showStandardPicker(rows[0]);
                            syncFasilitasOptions();
                            return;
                        }
                        button.closest('.fasilitas-row')?.remove();
                        reindexRows();
                        syncFasilitasOptions();
                    };
                });
            }

            function reindexRows() {
                container.querySelectorAll('.fasilitas-row').forEach(function (row, index) {
                    row.querySelectorAll('[name]').forEach(function (input) {
                        input.name = input.name.replace(/fasilitas_items\[\d+]/, 'fasilitas_items[' + index + ']');
                    });
                });
            }

            function bindRowControls() {
                bindRemoveButtons();
                bindFasilitasSelects();
                bindStandardPickerButtons();
            }

            addButton.addEventListener('click', function () {
                const index = container.querySelectorAll('.fasilitas-row').length;
                const row = document.createElement('div');
                row.className = 'fasilitas-row grid grid-cols-[minmax(0,1fr)_minmax(5.75rem,7rem)_auto] items-end gap-2 rounded-lg border border-gray-200 bg-gray-50 p-3 sm:gap-3 sm:grid-cols-[minmax(0,1fr)_180px_auto]';
                row.innerHTML = `
                    <div class="min-w-0">
                        <label class="mb-1 block text-xs font-medium text-gray-700 sm:text-sm">Fasilitas</label>
                        <select name="fasilitas_items[${index}][nama]"
                                class="fasilitas-nama-select ${formControlClass}">
                            ${buildFasilitasOptions('')}
                        </select>
                        <div class="fasilitas-custom-wrap hidden">
                            <input type="text"
                                   name="fasilitas_items[${index}][nama_custom]"
                                   maxlength="${fasilitasNamaMaxLength}"
                                   placeholder="Ketik nama fasilitas…"
                                   class="fasilitas-nama-custom ${formControlClass}">
                            <button type="button"
                                    class="fasilitas-use-standard mt-1.5 text-xs font-medium text-emerald-700 hover:text-emerald-900 hover:underline">
                                ← Pilih dari daftar standar
                            </button>
                        </div>
                    </div>
                    <div class="min-w-0">
                        <label class="mb-1 block text-xs font-medium text-gray-700 sm:text-sm">Kondisi</label>
                        <select name="fasilitas_items[${index}][kondisi]"
                                class="fasilitas-kondisi-select ${formControlClass}"
                                disabled>
                            ${buildKondisiOptions('')}
                        </select>
                    </div>
                    <div class="flex shrink-0 items-end">
                        <button type="button" class="remove-fasilitas-row rounded-lg border border-red-200 bg-white px-2.5 py-2 text-xs font-medium text-red-700 hover:bg-red-50 sm:px-3 sm:text-sm" title="Hapus baris fasilitas" aria-label="Hapus baris fasilitas"><span class="sm:hidden" aria-hidden="true">✕</span><span class="hidden sm:inline">Hapus</span></button>
                    </div>
                `;
                container.appendChild(row);
                bindRowControls();
                syncFasilitasOptions();
            });

            bindRowControls();
            syncFasilitasOptions();
        });
    </script>
@endpush
