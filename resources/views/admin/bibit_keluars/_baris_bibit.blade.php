@php
    $bibits = $bibits ?? collect();
    $rows = old('items');

    if ($rows === null) {
        $rows = isset($batch)
            ? $batch->items->map(fn ($item) => [
                'bibit_id' => $item->bibit_id,
                'sumber' => $item->sumber,
                'jumlah' => $item->jumlah,
            ])->all()
            : [['bibit_id' => request('bibit_id'), 'sumber' => '', 'jumlah' => '']];
    }

    if ($rows === []) {
        $rows = [['bibit_id' => '', 'sumber' => '', 'jumlah' => '']];
    }
@endphp

<div>
    <div class="mb-2 flex items-center justify-between gap-2">
        <label class="block text-sm font-medium text-gray-700">Daftar Bibit Keluar *</label>
        <button type="button" id="bibit-keluar-add-row"
                class="rounded-lg border border-green-300 px-3 py-1.5 text-xs font-medium text-green-800 hover:bg-green-50">
            + Tambah jenis bibit
        </button>
    </div>
    <p class="mb-3 text-xs text-gray-500">Satu transaksi dapat mencakup beberapa jenis bibit. Tentukan sumber stok (Produksi, Pengadaan, atau Hibah) per baris — stok hanya diambil dari sumber yang dipilih.</p>

    <div id="bibit-keluar-items" class="space-y-3">
        @foreach ($rows as $index => $row)
            @include('admin.bibit_keluars._baris_bibit_row', [
                'index' => $index,
                'row' => $row,
                'bibits' => $bibits,
                'editStokBonus' => $editStokBonus ?? [],
                'canRemove' => count($rows) > 1,
            ])
        @endforeach
    </div>

    @error('items')
        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<template id="bibit-keluar-row-template">
    @include('admin.bibit_keluars._baris_bibit_row', [
        'index' => '__INDEX__',
        'row' => ['bibit_id' => '', 'sumber' => '', 'jumlah' => ''],
        'bibits' => $bibits,
        'editStokBonus' => $editStokBonus ?? [],
        'canRemove' => true,
    ])
</template>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const container = document.getElementById('bibit-keluar-items');
                const template = document.getElementById('bibit-keluar-row-template');
                const addBtn = document.getElementById('bibit-keluar-add-row');

                function reindexRows() {
                    container?.querySelectorAll('[data-bibit-keluar-row]').forEach(function (row, index) {
                        row.querySelectorAll('[name^="items["]').forEach(function (input) {
                            input.name = input.name.replace(/items\[\d+]/, 'items[' + index + ']');
                        });
                        const removeBtn = row.querySelector('[data-remove-row]');
                        if (removeBtn) {
                            removeBtn.classList.toggle('hidden', container.querySelectorAll('[data-bibit-keluar-row]').length <= 1);
                        }
                    });
                }

                function parseStokSumber(option) {
                    if (! option?.dataset?.stokSumber) {
                        return null;
                    }
                    try {
                        return JSON.parse(option.dataset.stokSumber);
                    } catch (e) {
                        return null;
                    }
                }

                function bindRow(row) {
                    const select = row.querySelector('[data-bibit-select]');
                    const sumberSelect = row.querySelector('[data-sumber-select]');
                    const jumlahInput = row.querySelector('[data-jumlah-input]');
                    const stokHint = row.querySelector('[data-stok-hint]');

                    function refreshSumberOptions() {
                        const option = select?.options[select.selectedIndex];
                        const perSumber = parseStokSumber(option);
                        if (! sumberSelect) {
                            return;
                        }
                        sumberSelect.querySelectorAll('option[value]').forEach(function (opt) {
                            if (! opt.value) {
                                return;
                            }
                            const jumlah = perSumber ? (perSumber[opt.value] ?? 0) : 0;
                            opt.disabled = ! option?.value || jumlah <= 0;
                            opt.textContent = opt.value + (perSumber ? (' (' + jumlah + ')') : '');
                        });
                        if (sumberSelect.value && sumberSelect.selectedOptions[0]?.disabled) {
                            sumberSelect.value = '';
                        }
                    }

                    function refreshStokHint() {
                        const option = select?.options[select.selectedIndex];
                        const perSumber = parseStokSumber(option);
                        const sumber = sumberSelect?.value;
                        if (! stokHint) {
                            return;
                        }
                        if (! option?.value) {
                            stokHint.textContent = 'Pilih bibit dan sumber stok.';
                            jumlahInput?.removeAttribute('max');
                            return;
                        }
                        if (perSumber) {
                            const parts = Object.keys(perSumber).map(function (key) {
                                return key + ': ' + perSumber[key];
                            });
                            stokHint.textContent = 'Stok per sumber — ' + parts.join(' · ');
                        }
                        const stokSumber = sumber && perSumber ? (perSumber[sumber] ?? 0) : null;
                        if (jumlahInput && stokSumber !== null && sumber) {
                            jumlahInput.max = stokSumber;
                        } else if (jumlahInput) {
                            jumlahInput.removeAttribute('max');
                        }
                    }

                    select?.addEventListener('change', function () {
                        refreshSumberOptions();
                        refreshStokHint();
                    });
                    sumberSelect?.addEventListener('change', refreshStokHint);
                    refreshSumberOptions();
                    refreshStokHint();

                    row.querySelector('[data-remove-row]')?.addEventListener('click', function () {
                        row.remove();
                        reindexRows();
                    });
                }

                container?.querySelectorAll('[data-bibit-keluar-row]').forEach(bindRow);

                addBtn?.addEventListener('click', function () {
                    if (! template || ! container) {
                        return;
                    }
                    const index = container.querySelectorAll('[data-bibit-keluar-row]').length;
                    const html = template.innerHTML.replaceAll('__INDEX__', String(index));
                    const wrapper = document.createElement('div');
                    wrapper.innerHTML = html.trim();
                    const row = wrapper.firstElementChild;
                    container.appendChild(row);
                    bindRow(row);
                    reindexRows();
                });
            });
        </script>
    @endpush
@endonce
