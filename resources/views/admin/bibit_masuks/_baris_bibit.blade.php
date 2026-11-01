@php
    $bibits = $bibits ?? collect();
    $rows = old('items');

    if ($rows === null) {
        $rows = isset($batch)
            ? $batch->items->map(fn ($item) => [
                'bibit_id' => $item->bibit_id,
                'jumlah' => $item->jumlah,
                'sumber' => $item->sumber,
                'status_siap_tanam' => $item->status_siap_tanam,
            ])->all()
            : [['bibit_id' => request('bibit_id'), 'jumlah' => '', 'sumber' => '', 'status_siap_tanam' => false]];
    }

    if ($rows === []) {
        $rows = [['bibit_id' => '', 'jumlah' => '', 'sumber' => '', 'status_siap_tanam' => false]];
    }
@endphp

<div>
    <div class="mb-2 flex items-center justify-between gap-2">
        <label class="block text-sm font-medium text-gray-700">Daftar Bibit Masuk *</label>
        <button type="button" id="bibit-masuk-add-row"
                class="rounded-lg border border-teal-300 px-3 py-1.5 text-xs font-medium text-teal-800 hover:bg-teal-50">
            + Tambah jenis bibit
        </button>
    </div>
    <p class="mb-3 text-xs text-gray-500">Satu transaksi dapat mencakup beberapa jenis bibit dengan sumber dan kesiapan masing-masing.</p>

    <div id="bibit-masuk-items" class="space-y-3">
        @foreach ($rows as $index => $row)
            @include('admin.bibit_masuks._baris_bibit_row', [
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

<template id="bibit-masuk-row-template">
    @include('admin.bibit_masuks._baris_bibit_row', [
        'index' => '__INDEX__',
        'row' => ['bibit_id' => '', 'jumlah' => '', 'sumber' => '', 'status_siap_tanam' => false],
        'bibits' => $bibits,
        'canRemove' => true,
    ])
</template>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const container = document.getElementById('bibit-masuk-items');
                const template = document.getElementById('bibit-masuk-row-template');
                const addBtn = document.getElementById('bibit-masuk-add-row');

                function reindexRows() {
                    container?.querySelectorAll('[data-bibit-masuk-row]').forEach(function (row, index) {
                        row.querySelectorAll('[name^="items["]').forEach(function (input) {
                            input.name = input.name.replace(/items\[\d+]/, 'items[' + index + ']');
                        });
                        const removeBtn = row.querySelector('[data-remove-row]');
                        if (removeBtn) {
                            removeBtn.classList.toggle('hidden', container.querySelectorAll('[data-bibit-masuk-row]').length <= 1);
                        }
                    });
                }

                function bindRow(row) {
                    row.querySelector('[data-remove-row]')?.addEventListener('click', function () {
                        row.remove();
                        reindexRows();
                    });
                }

                container?.querySelectorAll('[data-bibit-masuk-row]').forEach(bindRow);

                addBtn?.addEventListener('click', function () {
                    if (! template || ! container) {
                        return;
                    }
                    const index = container.querySelectorAll('[data-bibit-masuk-row]').length;
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
