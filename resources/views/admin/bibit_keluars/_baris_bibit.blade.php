@php
    $bibits = $bibits ?? collect();
    $rows = old('items');

    if ($rows === null) {
        $rows = isset($batch)
            ? $batch->items->map(fn ($item) => ['bibit_id' => $item->bibit_id, 'jumlah' => $item->jumlah])->all()
            : [['bibit_id' => request('bibit_id'), 'jumlah' => '']];
    }

    if ($rows === []) {
        $rows = [['bibit_id' => '', 'jumlah' => '']];
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
    <p class="mb-3 text-xs text-gray-500">Satu transaksi dapat mencakup beberapa jenis bibit untuk peruntukan dan lokasi yang sama.</p>

    <div id="bibit-keluar-items" class="space-y-3">
        @foreach ($rows as $index => $row)
            @include('admin.bibit_keluars._baris_bibit_row', [
                'index' => $index,
                'row' => $row,
                'bibits' => $bibits,
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
        'row' => ['bibit_id' => '', 'jumlah' => ''],
        'bibits' => $bibits,
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

                function bindRow(row) {
                    const select = row.querySelector('[data-bibit-select]');
                    const jumlahInput = row.querySelector('[data-jumlah-input]');
                    const stokHint = row.querySelector('[data-stok-hint]');

                    function refreshStokHint() {
                        const option = select?.options[select.selectedIndex];
                        const stok = option?.dataset?.stok;
                        if (! stokHint) {
                            return;
                        }
                        stokHint.textContent = stok ? ('Stok tersedia: ' + stok) : 'Pilih bibit.';
                        if (jumlahInput && stok) {
                            jumlahInput.max = stok;
                        } else if (jumlahInput) {
                            jumlahInput.removeAttribute('max');
                        }
                    }

                    select?.addEventListener('change', refreshStokHint);
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
