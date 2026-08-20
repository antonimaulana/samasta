@php
    $armadaInventory = $armadaInventory ?? collect();
    $sopirOptions = \App\Models\PemeliharaanTamanArmada::SOPIR_OPTIONS;
    $armadaRows = $armadaRows ?? old('armada', $kinerja?->armadas?->map(fn ($row) => [
        'alat_sarana_operasional_id' => $row->alat_sarana_operasional_id ?? '',
        'sopir' => $row->sopir ?? '',
    ])->all() ?? [['alat_sarana_operasional_id' => '', 'sopir' => '']]);
    if ($armadaRows === []) {
        $armadaRows = [['alat_sarana_operasional_id' => '', 'sopir' => '']];
    }
@endphp

<div id="armada-section" class="mt-8 border-t border-gray-100 pt-6 {{ ($isTimArmada ?? false) ? '' : 'hidden' }}">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h3 class="text-sm font-bold text-green-800">Data Armada</h3>
            <p class="mt-1 text-xs text-gray-500">
                Pilih armada dari inventaris Alat/Sarana Operasional (Tim Armada) dan tentukan sopir.
            </p>
        </div>
        <button type="button" id="add-armada-row"
                class="rounded-lg border border-green-300 px-3 py-1.5 text-sm font-medium text-green-700 hover:bg-green-50">
            + Tambah Armada
        </button>
    </div>

    @error('armada')
        <p class="mb-3 text-sm text-red-600">{{ $message }}</p>
    @enderror

    @if ($armadaInventory->isEmpty())
        <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            Belum ada data armada di
            <a href="{{ route('admin.alat-sarana-operasionals.index') }}" class="font-semibold underline">Alat/Sarana Operasional</a>.
            Tambahkan inventaris armada untuk Tim Armada terlebih dahulu.
        </div>
    @endif

    <div id="armada-rows" class="space-y-4">
        @foreach ($armadaRows as $index => $armada)
            @php
                $selectedId = (string) ($armada['alat_sarana_operasional_id'] ?? '');
                $selectedSopir = (string) ($armada['sopir'] ?? '');
            @endphp
            <div class="armada-row rounded-xl border border-gray-200 bg-gray-50 p-4" data-index="{{ $index }}">
                <div class="mb-3 flex items-center justify-between gap-2">
                    <p class="text-sm font-semibold text-gray-800">Armada #<span class="armada-number">{{ $index + 1 }}</span></p>
                    <button type="button"
                            class="remove-armada-row rounded border border-red-200 px-2 py-1 text-xs text-red-700 hover:bg-red-50 {{ count($armadaRows) === 1 ? 'hidden' : '' }}">
                        Hapus
                    </button>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Nama Armada *</label>
                        <select name="armada[{{ $index }}][alat_sarana_operasional_id]"
                                class="armada-select w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500"
                                @disabled($armadaInventory->isEmpty())>
                            <option value="">Pilih armada</option>
                            @foreach ($armadaInventory as $option)
                                <option value="{{ $option->id }}" @selected($selectedId === (string) $option->id)>
                                    {{ $option->nama }} - {{ $option->jenis }}
                                </option>
                            @endforeach
                        </select>
                        @error('armada.'.$index.'.alat_sarana_operasional_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Sopir *</label>
                        <select name="armada[{{ $index }}][sopir]"
                                class="armada-sopir w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                            <option value="">Pilih sopir</option>
                            @foreach ($sopirOptions as $sopir)
                                <option value="{{ $sopir }}" @selected($selectedSopir === $sopir)>{{ $sopir }}</option>
                            @endforeach
                        </select>
                        @error('armada.'.$index.'.sopir')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<template id="armada-row-template">
    <div class="armada-row rounded-xl border border-gray-200 bg-gray-50 p-4" data-index="__INDEX__">
        <div class="mb-3 flex items-center justify-between gap-2">
            <p class="text-sm font-semibold text-gray-800">Armada #<span class="armada-number">__NUMBER__</span></p>
            <button type="button"
                    class="remove-armada-row rounded border border-red-200 px-2 py-1 text-xs text-red-700 hover:bg-red-50">
                Hapus
            </button>
        </div>
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Nama Armada *</label>
                <select name="armada[__INDEX__][alat_sarana_operasional_id]"
                        class="armada-select w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                    <option value="">Pilih armada</option>
                    @foreach ($armadaInventory as $option)
                        <option value="{{ $option->id }}">{{ $option->nama }} - {{ $option->jenis }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Sopir *</label>
                <select name="armada[__INDEX__][sopir]"
                        class="armada-sopir w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                    <option value="">Pilih sopir</option>
                    @foreach ($sopirOptions as $sopir)
                        <option value="{{ $sopir }}">{{ $sopir }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</template>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const timArmadaName = @json(\App\Models\PemeliharaanTaman::TIM_ARMADA);
        const timSelect = document.getElementById('tim');
        const armadaSection = document.getElementById('armada-section');
        const armadaRows = document.getElementById('armada-rows');
        const addArmadaButton = document.getElementById('add-armada-row');
        const armadaTemplate = document.getElementById('armada-row-template');

        function currentTimValue() {
            if (! timSelect) {
                return @json($operatorTim ?? '');
            }
            return timSelect.value;
        }

        function toggleArmadaSection() {
            const show = currentTimValue() === timArmadaName;
            armadaSection?.classList.toggle('hidden', ! show);
            armadaSection?.querySelectorAll('.armada-select, .armada-sopir').forEach(function (input) {
                input.required = show;
            });
        }

        function renumberArmadaRows() {
            const rows = armadaRows?.querySelectorAll('.armada-row') ?? [];
            rows.forEach(function (row, index) {
                row.dataset.index = String(index);
                row.querySelector('.armada-number').textContent = String(index + 1);
                row.querySelectorAll('[name^="armada["]').forEach(function (input) {
                    input.name = input.name.replace(/armada\[\d+]/, 'armada[' + index + ']');
                });
                const removeButton = row.querySelector('.remove-armada-row');
                if (removeButton) {
                    removeButton.classList.toggle('hidden', rows.length === 1);
                }
            });
        }

        addArmadaButton?.addEventListener('click', function () {
            if (! armadaTemplate || ! armadaRows) return;
            const index = armadaRows.querySelectorAll('.armada-row').length;
            const html = armadaTemplate.innerHTML
                .replaceAll('__INDEX__', String(index))
                .replaceAll('__NUMBER__', String(index + 1));
            armadaRows.insertAdjacentHTML('beforeend', html);
            renumberArmadaRows();
            toggleArmadaSection();
        });

        armadaRows?.addEventListener('click', function (event) {
            const button = event.target.closest('.remove-armada-row');
            if (! button) return;
            button.closest('.armada-row')?.remove();
            renumberArmadaRows();
        });

        timSelect?.addEventListener('change', toggleArmadaSection);
        toggleArmadaSection();
    });
</script>
@endpush
