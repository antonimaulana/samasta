@php
    $timPelaksana = $timPelaksana ?? null;
    $memilikiWilayah = (bool) old('memiliki_wilayah_kerja', $timPelaksana?->memiliki_wilayah_kerja ?? true);
@endphp

<div class="space-y-8">
    <div class="overflow-hidden rounded-2xl border border-green-100 bg-white shadow-sm">
        <div class="border-b border-green-50 bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-4">
            <h2 class="text-lg font-semibold text-green-800">Identitas Tim</h2>
        </div>
        <div class="grid gap-4 p-6 md:grid-cols-2">
            <div class="md:col-span-2">
                <label for="nama" class="mb-1 block text-sm font-medium text-gray-700">Nama tim kerja *</label>
                <input type="text" name="nama" id="nama" required
                       value="{{ old('nama', $timPelaksana?->nama) }}"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                @error('nama')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="nama_pengawas" class="mb-1 block text-sm font-medium text-gray-700">Nama pengawas</label>
                <input type="text" name="nama_pengawas" id="nama_pengawas"
                       value="{{ old('nama_pengawas', $timPelaksana?->nama_pengawas) }}"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
            </div>
            <div class="flex items-end">
                <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 px-4 py-3 text-sm">
                    <input type="hidden" name="memiliki_wilayah_kerja" value="0">
                    <input type="checkbox" name="memiliki_wilayah_kerja" value="1" id="memiliki_wilayah_kerja"
                           @checked($memilikiWilayah)
                           class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                    <span>Wilayah kerja per kelurahan</span>
                </label>
            </div>
        </div>
    </div>

    <div id="wilayah-kelurahan-panel" class="overflow-hidden rounded-2xl border border-blue-100 bg-white shadow-sm @unless($memilikiWilayah) hidden @endunless">
        <div class="border-b border-blue-50 bg-blue-50 px-6 py-4">
            <h2 class="text-lg font-semibold text-blue-900">Wilayah Kerja (Kelurahan)</h2>
        </div>
        <div class="max-h-[28rem] space-y-4 overflow-y-auto p-6">
            @foreach ($kecamatans as $kecamatanNama => $kelurahans)
                <div class="overflow-hidden rounded-xl border border-gray-200">
                    <div class="border-b border-gray-100 bg-gray-50 px-4 py-2">
                        <h3 class="text-sm font-semibold text-gray-900">{{ $kecamatanNama }}</h3>
                    </div>
                    <div class="grid gap-2 p-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($kelurahans as $kelurahan)
                            <label class="flex cursor-pointer items-center gap-2 rounded-lg border px-3 py-2 text-sm transition
                                {{ in_array($kelurahan->id, old('kelurahan_ids', $assignedIds), true) ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-green-300' }}">
                                <input type="checkbox"
                                       name="kelurahan_ids[]"
                                       value="{{ $kelurahan->id }}"
                                       @checked(in_array($kelurahan->id, old('kelurahan_ids', $assignedIds), true))
                                       class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                <span>{{ $kelurahan->nama }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-emerald-100 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-emerald-50 bg-gradient-to-r from-emerald-50 to-green-50 px-6 py-4">
            <h2 class="text-lg font-semibold text-emerald-900">Anggota Kerja</h2>
            <button type="button" id="add-member-row"
                    class="rounded-lg border border-emerald-400 bg-white px-4 py-2 text-sm font-semibold text-emerald-800 hover:bg-emerald-50">
                + Tambah baris
            </button>
        </div>
        <div class="overflow-x-auto p-4">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-2 py-2">Nama *</th>
                        <th class="px-2 py-2">NIP</th>
                        <th class="px-2 py-2">Jabatan</th>
                        <th class="px-2 py-2 w-16"></th>
                    </tr>
                </thead>
                <tbody id="member-rows">
                    @foreach ($defaultMembers as $index => $member)
                        @include('admin.tim_pelaksanas._member-row', ['index' => $index, 'member' => $member])
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<template id="member-row-template">
    @include('admin.tim_pelaksanas._member-row', ['index' => '__INDEX__', 'member' => ['nama' => '', 'nip' => '', 'jabatan' => '']])
</template>

@push('scripts')
    <script>
        (function () {
            const checkbox = document.getElementById('memiliki_wilayah_kerja');
            const panel = document.getElementById('wilayah-kelurahan-panel');
            const tbody = document.getElementById('member-rows');
            const addBtn = document.getElementById('add-member-row');
            const template = document.getElementById('member-row-template');

            if (checkbox && panel) {
                checkbox.addEventListener('change', function () {
                    panel.classList.toggle('hidden', !this.checked);
                });
            }

            function nextIndex() {
                const rows = tbody.querySelectorAll('[data-member-row]');
                let max = -1;
                rows.forEach(function (row) {
                    const idx = parseInt(row.getAttribute('data-member-row'), 10);
                    if (!Number.isNaN(idx) && idx > max) {
                        max = idx;
                    }
                });
                return max + 1;
            }

            function bindRemove(row) {
                const btn = row.querySelector('[data-remove-member]');
                if (!btn) {
                    return;
                }
                btn.addEventListener('click', function () {
                    const rows = tbody.querySelectorAll('[data-member-row]');
                    if (rows.length <= 1) {
                        row.querySelectorAll('input[type="text"]').forEach(function (input) {
                            input.value = '';
                        });
                        const hiddenId = row.querySelector('input[name*="[id]"]');
                        if (hiddenId) {
                            hiddenId.remove();
                        }
                        return;
                    }
                    row.remove();
                });
            }

            tbody.querySelectorAll('[data-member-row]').forEach(bindRemove);

            if (addBtn && template) {
                addBtn.addEventListener('click', function () {
                    const index = nextIndex();
                    const html = template.innerHTML.replace(/__INDEX__/g, String(index));
                    const wrap = document.createElement('tbody');
                    wrap.innerHTML = html.trim();
                    const row = wrap.firstElementChild;
                    tbody.appendChild(row);
                    bindRemove(row);
                });
            }
        })();
    </script>
@endpush
