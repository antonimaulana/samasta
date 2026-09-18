@props(['user' => null])

@php
    $selectedTeamIds = collect(old('tim_pelaksana_ids', $user?->timPelaksanas?->pluck('id')->all() ?? []))
        ->map(fn ($id) => (string) $id)
        ->all();
    $allWilayah = filter_var(old('akses_semua_wilayah', $user?->akses_semua_wilayah ?? false), FILTER_VALIDATE_BOOLEAN);
@endphp

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <label for="name" class="mb-1 block text-sm font-medium text-gray-700">Nama *</label>
        <input type="text" name="name" id="name" required value="{{ old('name', $user?->name) }}"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
    </div>

    <div>
        <label for="email" class="mb-1 block text-sm font-medium text-gray-700">Email *</label>
        <input type="email" name="email" id="email" required value="{{ old('email', $user?->email) }}"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
    </div>

    <div class="md:col-span-2">
        <label for="role" class="mb-1 block text-sm font-medium text-gray-700">Peran *</label>
        <select name="role" id="role" required
                class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
            @foreach (\App\Models\User::ROLES as $value => $label)
                <option value="{{ $value }}" @selected(old('role', $user?->role ?? 'operator') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <ul class="mt-2 list-inside list-disc space-y-0.5 text-xs text-gray-500">
            <li><strong class="font-medium text-gray-700">Administrator</strong> — kuasa penuh sistem &amp; pengguna</li>
            <li><strong class="font-medium text-gray-700">Admin</strong> — backpanel operasional (tanpa menu Sistem/DPA); tanpa import massal, hapus master, atau kelola pengguna</li>
            <li><strong class="font-medium text-gray-700">Pengawas</strong> — pantau tim (Wilayah 1–4, Armada, Nursery) + <strong>wajib input lapangan</strong></li>
            <li><strong class="font-medium text-gray-700">Pimpinan</strong> — ringkasan &amp; evaluasi seluruh kota; lihat saja</li>
        </ul>
    </div>

    <div id="operator-wilayah-field" class="md:col-span-2 hidden space-y-4 rounded-xl border border-gray-200 bg-gray-50 p-4">
        <div id="akses-semua-wilayah-wrap">
            <label class="flex cursor-pointer items-center gap-2">
                <input type="checkbox" name="akses_semua_wilayah" id="akses_semua_wilayah" value="1"
                       @checked($allWilayah)
                       class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                <span class="text-sm font-medium text-gray-800">Akses semua wilayah</span>
            </label>
            <p class="mt-1 text-xs text-gray-500">Hanya untuk peran Admin — kelola data seluruh tim/wilayah.</p>
        </div>

        <div id="tim-pelaksana-list">
            <p class="mb-2 text-sm font-medium text-gray-700">Tim pelaksana (boleh lebih dari satu)</p>
            <div class="grid gap-2 sm:grid-cols-2">
                @foreach (\App\Models\TimPelaksana::query()->where('aktif', true)->orderBy('urutan')->orderBy('nama')->get() as $tim)
                    <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
                        <input type="checkbox" name="tim_pelaksana_ids[]" value="{{ $tim->id }}"
                               @checked(in_array((string) $tim->id, $selectedTeamIds, true))
                               class="tim-pelaksana-checkbox rounded border-gray-300 text-green-600 focus:ring-green-500">
                        <span>{{ $tim->nama }}</span>
                    </label>
                @endforeach
            </div>
            <p class="mt-2 text-xs text-gray-500">
                Wajib untuk Admin (kecuali akses semua wilayah) dan Pengawas — pilih tim yang diampu.
            </p>
            @error('tim_pelaksana_ids')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="password" class="mb-1 block text-sm font-medium text-gray-700">
            Password {{ $user ? '(kosongkan jika tidak diubah)' : '*' }}
        </label>
        <input type="password" name="password" id="password" {{ $user ? '' : 'required' }}
               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
    </div>

    <div>
        <label for="password_confirmation" class="mb-1 block text-sm font-medium text-gray-700">Konfirmasi Password</label>
        <input type="password" name="password_confirmation" id="password_confirmation"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
    </div>
</div>

<script>
    (function () {
        const roleSelect = document.getElementById('role');
        const wilayahField = document.getElementById('operator-wilayah-field');
        const allWilayahWrap = document.getElementById('akses-semua-wilayah-wrap');
        const allWilayahCheckbox = document.getElementById('akses_semua_wilayah');
        const timList = document.getElementById('tim-pelaksana-list');
        const timCheckboxes = () => Array.from(document.querySelectorAll('.tim-pelaksana-checkbox'));

        function roleNeedsWilayah() {
            return roleSelect.value === 'operator' || roleSelect.value === 'pengawas';
        }

        function syncWilayahField() {
            const needsWilayah = roleNeedsWilayah();
            wilayahField.classList.toggle('hidden', !needsWilayah);

            if (!needsWilayah) {
                allWilayahCheckbox.checked = false;
                timCheckboxes().forEach((checkbox) => {
                    checkbox.checked = false;
                    checkbox.disabled = false;
                });
                return;
            }

            const isPengawas = roleSelect.value === 'pengawas';
            allWilayahWrap.classList.toggle('hidden', isPengawas);
            if (isPengawas) {
                allWilayahCheckbox.checked = false;
            }

            syncTimList();
        }

        function syncTimList() {
            const lockTeams = allWilayahCheckbox.checked && roleSelect.value === 'operator';
            timList.classList.toggle('opacity-50', lockTeams);

            timCheckboxes().forEach((checkbox) => {
                checkbox.disabled = lockTeams;
                if (lockTeams) {
                    checkbox.checked = false;
                }
            });
        }

        roleSelect.addEventListener('change', syncWilayahField);
        allWilayahCheckbox.addEventListener('change', syncTimList);
        syncWilayahField();
    })();
</script>

<div class="mt-6 flex gap-3">
    <button type="submit" class="rounded-lg bg-green-600 px-5 py-2 text-sm font-medium text-white hover:bg-green-700">
        Simpan
    </button>
    <a href="{{ route('admin.users.index') }}" class="rounded-lg border border-gray-300 px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
        Batal
    </a>
</div>
