@php
    $fasilitasItems = old('fasilitas_items');
    if ($fasilitasItems === null) {
        $fasilitasItems = $taman?->fasilitas_items ?? [['nama' => '', 'kondisi' => 'Baik']];
    }
    if ($fasilitasItems === []) {
        $fasilitasItems = [['nama' => '', 'kondisi' => 'Baik']];
    }

    $formControlClass = 'w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500';
@endphp

<x-admin.rth-section
    title="Fasilitas & Kondisi"
    description="Pilih fasilitas dari daftar standar dan catat kondisinya.">
    <x-slot:actions>
        <button type="button" id="add-fasilitas-row"
                class="rounded-lg border border-emerald-200 bg-white px-3 py-1.5 text-sm font-medium text-emerald-800 hover:bg-emerald-50">
            + Tambah Fasilitas
        </button>
    </x-slot:actions>

    <div id="fasilitas-rows" class="space-y-3">
        @foreach ($fasilitasItems as $index => $item)
            <div class="fasilitas-row grid gap-3 rounded-lg border border-gray-200 bg-gray-50 p-3 sm:grid-cols-[1fr_180px_auto]">
                <div>
                    <x-admin.form.label>Fasilitas</x-admin.form.label>
                    <select name="fasilitas_items[{{ $index }}][nama]"
                            class="fasilitas-nama-select {{ $formControlClass }}">
                        <option value="">Pilih fasilitas</option>
                        @foreach (\App\Models\Taman::FASILITAS_DAFTAR as $fasilitas)
                            <option value="{{ $fasilitas }}" @selected(($item['nama'] ?? '') === $fasilitas)>{{ $fasilitas }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-admin.form.label>Kondisi</x-admin.form.label>
                    <select name="fasilitas_items[{{ $index }}][kondisi]"
                            class="{{ $formControlClass }}">
                        @foreach (\App\Models\Taman::FASILITAS_KONDISI as $kondisi)
                            <option value="{{ $kondisi }}" @selected(($item['kondisi'] ?? 'Baik') === $kondisi)>{{ $kondisi }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="button"
                            class="remove-fasilitas-row w-full rounded-lg border border-red-200 bg-white px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-50 sm:w-auto">
                        Hapus
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

            const fasilitasDaftar = @json(\App\Models\Taman::FASILITAS_DAFTAR);
            const kondisiOptions = @json(\App\Models\Taman::FASILITAS_KONDISI);
            const formControlClass = @json($formControlClass);

            function buildFasilitasOptions(selected) {
                let html = '<option value="">Pilih fasilitas</option>';
                fasilitasDaftar.forEach(function (fasilitas) {
                    const isSelected = selected === fasilitas ? ' selected' : '';
                    html += '<option value="' + fasilitas + '"' + isSelected + '>' + fasilitas + '</option>';
                });
                return html;
            }

            function syncFasilitasOptions() {
                const selected = Array.from(container.querySelectorAll('.fasilitas-nama-select'))
                    .map(function (select) { return select.value; })
                    .filter(Boolean);

                container.querySelectorAll('.fasilitas-nama-select').forEach(function (select) {
                    const current = select.value;
                    Array.from(select.options).forEach(function (option) {
                        if (option.value === '' || option.value === current) {
                            option.disabled = false;
                            return;
                        }
                        option.disabled = selected.includes(option.value);
                    });
                });
            }

            function bindFasilitasSelects() {
                container.querySelectorAll('.fasilitas-nama-select').forEach(function (select) {
                    select.onchange = syncFasilitasOptions;
                });
            }

            function bindRemoveButtons() {
                container.querySelectorAll('.remove-fasilitas-row').forEach(function (button) {
                    button.onclick = function () {
                        const rows = container.querySelectorAll('.fasilitas-row');
                        if (rows.length <= 1) {
                            const select = rows[0].querySelector('.fasilitas-nama-select');
                            const kondisi = rows[0].querySelector('select[name*="[kondisi]"]');
                            if (select) select.value = '';
                            if (kondisi) kondisi.value = 'Baik';
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

            addButton.addEventListener('click', function () {
                const index = container.querySelectorAll('.fasilitas-row').length;
                const row = document.createElement('div');
                row.className = 'fasilitas-row grid gap-3 rounded-lg border border-gray-200 bg-gray-50 p-3 sm:grid-cols-[1fr_180px_auto]';
                row.innerHTML = `
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Fasilitas</label>
                        <select name="fasilitas_items[${index}][nama]"
                                class="fasilitas-nama-select ${formControlClass}">
                            ${buildFasilitasOptions('')}
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Kondisi</label>
                        <select name="fasilitas_items[${index}][kondisi]"
                                class="${formControlClass}">
                            ${kondisiOptions.map(function (kondisi) {
                                return '<option value="' + kondisi + '">' + kondisi + '</option>';
                            }).join('')}
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="button" class="remove-fasilitas-row w-full rounded-lg border border-red-200 bg-white px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-50 sm:w-auto">Hapus</button>
                    </div>
                `;
                container.appendChild(row);
                bindRemoveButtons();
                bindFasilitasSelects();
                syncFasilitasOptions();
            });

            bindRemoveButtons();
            bindFasilitasSelects();
            syncFasilitasOptions();
        });
    </script>
@endpush
