@php
    $pemangkasan = $pemangkasan ?? null;
    $currentStatus = old('status', $pemangkasan?->status ?? 'Rencana');
    $currentJenis = old('jenis_layanan', $pemangkasan?->jenis_layanan ?? request('jenis', ''));
    $currentKategori = old('kategori', $pemangkasan?->kategori ?? '');
    $isLokasiLuar = filter_var(old('lokasi_luar', ($pemangkasan && ! $pemangkasan->taman_id) ? '1' : '0'), FILTER_VALIDATE_BOOLEAN);
    $isMiniGarden = $currentJenis === 'Pemasangan Mini Garden';
    $tamans = $tamans ?? collect();
@endphp

@if ($errors->any())
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        <p class="font-semibold">Form belum bisa disimpan:</p>
        <ul class="mt-1 list-inside list-disc space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid gap-6 md:grid-cols-2">
    <div class="md:col-span-2">
        <label for="jenis_layanan" class="mb-1 block text-sm font-medium text-gray-700">Jenis Operasional *</label>
        <select name="jenis_layanan" id="jenis_layanan" required
                class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
            <option value="" @selected($currentJenis === '') disabled>Pilih jenis operasional</option>
            @foreach (\App\Models\Pemangkasan::JENIS_LAYANAN as $jenis)
                <option value="{{ $jenis }}" @selected($currentJenis === $jenis)>{{ $jenis }}</option>
            @endforeach
        </select>
    </div>

    <div class="md:col-span-2">
        <p id="section-title" class="mb-3 text-sm font-semibold text-gray-800">Data Permohonan</p>
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label for="asal" id="label-asal" class="mb-1 block text-sm font-medium text-gray-700">Asal Permohonan *</label>
                <input type="text" name="asal" id="asal"
                       value="{{ old('asal', $pemangkasan?->asal ?? '') }}"
                       required
                       placeholder="Contoh: Dinas PU, Warga RT 05, Tim Survei Lapangan"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
            </div>

            <div>
                <label for="penanggungjawab" id="label-penanggungjawab" class="mb-1 block text-sm font-medium text-gray-700">Penanggung Jawab *</label>
                <input type="text" name="penanggungjawab" id="penanggungjawab"
                       value="{{ old('penanggungjawab', $pemangkasan?->penanggungjawab ?? '') }}"
                       required
                       placeholder="Contoh: Budi Santoso, Koordinator RT 05"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                @error('penanggungjawab')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="kontak_permohonan" id="label-kontak-permohonan" class="mb-1 block text-sm font-medium text-gray-700">Kontak Permohonan *</label>
                <input type="tel" name="kontak_permohonan" id="kontak_permohonan"
                       value="{{ old('kontak_permohonan', $pemangkasan?->kontak_permohonan ?? '') }}"
                       required
                       placeholder="Contoh: 081234567890"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                @error('kontak_permohonan')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="tanggal_permohonan" id="label-tanggal-permohonan" class="mb-1 block text-sm font-medium text-gray-700">Tanggal Permohonan *</label>
                <input type="date" name="tanggal_permohonan" id="tanggal_permohonan"
                       value="{{ old('tanggal_permohonan', $pemangkasan?->tanggal_permohonan?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                       required
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
            </div>

            <div>
                <label for="kategori" class="mb-1 block text-sm font-medium text-gray-700">Kategori *</label>
                <select name="kategori" id="kategori" required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                    <option value="" @selected($currentKategori === '') disabled>Pilih kategori</option>
                    @foreach (\App\Models\Pemangkasan::KATEGORI as $kategori)
                        <option value="{{ $kategori }}" @selected($currentKategori === $kategori)>
                            {{ $kategori }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="pendukung_pelaksanaan" class="mb-1 block text-sm font-medium text-gray-700">Pendukung Pelaksanaan</label>
                <input type="file" name="pendukung_pelaksanaan" id="pendukung_pelaksanaan"
                       accept="image/*,.pdf,application/pdf"
                       class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 file:mr-3 file:rounded file:border-0 file:bg-gray-100 file:px-3 file:py-1 file:text-gray-800 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                <p class="mt-1 text-xs text-gray-500">Opsional — gambar atau PDF, maks. 5 MB.</p>
                @error('pendukung_pelaksanaan')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
                @if ($pemangkasan?->hasPendukungPelaksanaanFile())
                    <div class="mt-2 rounded-lg border border-gray-200 bg-gray-50 p-2">
                        <p class="mb-1 text-xs font-medium text-gray-600">File saat ini:</p>
                        @if ($pemangkasan->pendukungPelaksanaanIsPdf())
                            <a href="{{ $pemangkasan->pendukung_pelaksanaan_url }}" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-1 text-xs font-medium text-green-700 hover:text-green-800">
                                📄 {{ $pemangkasan->pendukungPelaksanaanFilename() }}
                            </a>
                        @else
                            <a href="{{ $pemangkasan->pendukung_pelaksanaan_url }}" target="_blank" rel="noopener">
                                <img src="{{ $pemangkasan->pendukung_pelaksanaan_url }}" alt="Pendukung pelaksanaan"
                                     class="h-24 rounded border border-gray-200 object-cover">
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            <div id="lokasi-pohon-section" class="md:col-span-2 {{ $isMiniGarden ? 'hidden' : '' }}">
                <label class="mb-1 block text-sm font-medium text-gray-700">Lokasi *</label>
                <label class="mb-3 flex cursor-pointer items-center gap-2">
                    <input type="checkbox" name="lokasi_luar" id="lokasi_luar" value="1"
                           @checked($isLokasiLuar)
                           class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                    <span class="text-sm text-gray-700">Lokasi di luar wilayah pemeliharaan</span>
                </label>

                <div id="lokasi-taman-section" class="{{ $isLokasiLuar ? 'hidden' : '' }}">
                    @if ($tamans->isEmpty())
                        <p class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                            Belum ada data taman.
                            <a href="{{ route('admin.tamans.create') }}" class="font-semibold underline">Tambah taman</a>
                            terlebih dahulu, atau centang lokasi manual di atas.
                        </p>
                    @else
                        <x-admin.taman-select
                            :tamans="$tamans"
                            :selected="old('taman_id', $pemangkasan?->taman_id)"
                            :required="true"
                        />
                    @endif
                    @error('taman_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div id="lokasi-manual-section" class="{{ $isLokasiLuar ? '' : 'hidden' }}">
                    <input type="text" name="lokasi_pohon" id="lokasi_pohon"
                           value="{{ old('lokasi_pohon', $isLokasiLuar ? ($pemangkasan?->lokasi_pohon ?? '') : '') }}"
                           placeholder="Contoh: Jl. Raja Haji Fisabilillah, Batam Center"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                    @error('lokasi_pohon')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="md:col-span-2">
        <label class="mb-1 block text-sm font-medium text-gray-700">Jadwal Pelaksanaan *</label>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="tanggal_eksekusi" class="mb-1 block text-xs font-medium text-gray-500">Tanggal Mulai</label>
                <input type="date" name="tanggal_eksekusi" id="tanggal_eksekusi"
                       value="{{ old('tanggal_eksekusi', $pemangkasan?->tanggal_eksekusi?->format('Y-m-d') ?? '') }}"
                       required
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
            </div>
            <div>
                <label for="tanggal_akhir_jadwal" class="mb-1 block text-xs font-medium text-gray-500">Tanggal Selesai (Rencana)</label>
                <input type="date" name="tanggal_akhir_jadwal" id="tanggal_akhir_jadwal"
                       value="{{ old('tanggal_akhir_jadwal', $pemangkasan?->tanggal_akhir_jadwal?->format('Y-m-d') ?? $pemangkasan?->tanggal_eksekusi?->format('Y-m-d') ?? '') }}"
                       required
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
            </div>
        </div>
        <p class="mt-2 text-xs text-gray-600">
            Total rencana: <strong id="total-hari-jadwal">{{ $pemangkasan?->total_hari ?? '—' }}</strong> hari
            @if ($pemangkasan && $pemangkasan->progres->isNotEmpty())
                · Progres: <strong>{{ \App\Support\PemangkasanSchedule::progressSummary($pemangkasan) }}</strong>
            @endif
        </p>
        <div id="jadwal-konflik-warning" class="mt-2 hidden rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900"></div>
        @error('tanggal_eksekusi')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
        @error('tanggal_akhir_jadwal')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        @php
            $selectedPelaksana = old('pelaksana', $pemangkasan?->pelaksana ?? []);
            if (! is_array($selectedPelaksana)) {
                $selectedPelaksana = $selectedPelaksana !== '' && $selectedPelaksana !== null
                    ? [(string) $selectedPelaksana]
                    : [];
            }
        @endphp
        <p class="mb-2 text-sm font-medium text-gray-700">Pelaksana *</p>
        <p class="mb-3 text-xs text-gray-500">Centang satu atau lebih tim pelaksana.</p>
        <div class="grid gap-2 sm:grid-cols-2">
            @foreach (\App\Models\PemeliharaanTaman::timNames() as $tim)
                @php $isChecked = in_array($tim, $selectedPelaksana, true); @endphp
                <label class="flex cursor-pointer items-center gap-3 rounded-lg border px-3 py-2 transition
                    {{ $isChecked ? 'border-green-500 bg-green-50' : 'border-gray-200 bg-white hover:border-green-300 hover:bg-green-50/50' }}">
                    <input type="checkbox"
                           name="pelaksana[]"
                           value="{{ $tim }}"
                           @checked($isChecked)
                           class="h-4 w-4 shrink-0 rounded border-gray-300 text-green-600 focus:ring-green-500">
                    <span class="text-sm font-medium text-gray-800">{{ $tim }}</span>
                </label>
            @endforeach
        </div>
        @error('pelaksana')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
        @error('pelaksana.*')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
        <div id="tim-suggest-notice" class="mt-3 hidden rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs text-blue-900"></div>
    </div>

    <div>
        <label for="status" class="mb-1 block text-sm font-medium text-gray-700">Status *</label>
        <select name="status" id="status" required
                class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
            @foreach (\App\Models\Pemangkasan::STATUS as $status)
                <option value="{{ $status }}" @selected($currentStatus === $status)>{{ $status }}</option>
            @endforeach
        </select>
    </div>

    <div id="tanggal-penyelesaian-section" class="{{ $currentStatus === 'Selesai' ? '' : 'hidden' }}">
        <label for="tanggal_penyelesaian" class="mb-1 block text-sm font-medium text-gray-700">Tanggal Penyelesaian *</label>
        <input type="date" name="tanggal_penyelesaian" id="tanggal_penyelesaian"
               value="{{ old('tanggal_penyelesaian', $pemangkasan?->tanggal_penyelesaian?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
        @error('tanggal_penyelesaian')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div id="dampak-section" class="md:col-span-2 hidden">
        <label for="dampak" class="mb-1 block text-sm font-medium text-gray-700">Dampak</label>
        <textarea name="dampak" id="dampak" rows="3"
                  placeholder="Opsional — contoh: Menutup badan jalan, merusak kabel listrik, dll."
                  class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">{{ old('dampak', $pemangkasan?->dampak ?? '') }}</textarea>
    </div>
</div>

@unless($hideFormActions ?? false)
<div class="mt-6 flex gap-3">
    <button type="submit"
            class="rounded-lg bg-green-600 px-5 py-2 text-sm font-medium text-white hover:bg-green-700">
        Simpan
    </button>
    <a href="{{ $cancelUrl ?? route('admin.pemangkasans.index') }}"
       class="rounded-lg border border-gray-300 px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
        Batal
    </a>
</div>
@endunless

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const jenisSelect = document.getElementById('jenis_layanan');
            const statusSelect = document.getElementById('status');
            const dampakSection = document.getElementById('dampak-section');
            const penyelesaianSection = document.getElementById('tanggal-penyelesaian-section');
            const penyelesaianInput = document.getElementById('tanggal_penyelesaian');
            const pendukungInput = document.getElementById('pendukung_pelaksanaan');
            const lokasiLuarCheckbox = document.getElementById('lokasi_luar');
            const lokasiTamanSection = document.getElementById('lokasi-taman-section');
            const lokasiManualSection = document.getElementById('lokasi-manual-section');
            const tamanHiddenInput = document.getElementById('taman_id');
            const tamanSearchInput = document.getElementById('taman_id_search');
            const lokasiManualInput = document.getElementById('lokasi_pohon');
            const lokasiPohonSection = document.getElementById('lokasi-pohon-section');

            const labels = {
                pemangkasan: {
                    section: 'Data Permohonan',
                    asal: 'Asal Permohonan *',
                    penanggungjawab: 'Penanggung Jawab *',
                    kontakPermohonan: 'Kontak Permohonan *',
                    tanggalPermohonan: 'Tanggal Permohonan *',
                    tanggalPelaksanaan: 'Jadwal Pelaksanaan *',
                },
                tumbang: {
                    section: 'Data Laporan',
                    asal: 'Asal Laporan *',
                    penanggungjawab: 'Penanggung Jawab *',
                    kontakPermohonan: 'Kontak Laporan *',
                    tanggalPermohonan: 'Tanggal Laporan *',
                    tanggalPelaksanaan: 'Jadwal Pelaksanaan *',
                },
                miniGarden: {
                    section: 'Data Permohonan',
                    asal: 'Asal Permohonan *',
                    penanggungjawab: 'Penanggung Jawab *',
                    kontakPermohonan: 'Kontak Permohonan *',
                    tanggalPermohonan: 'Tanggal Permohonan *',
                    tanggalPelaksanaan: 'Jadwal Pelaksanaan *',
                },
            };

            function isMiniGarden() {
                return jenisSelect.value === 'Pemasangan Mini Garden';
            }

            function isPohonTumbang() {
                return jenisSelect.value === 'Penanganan Pohon Tumbang';
            }

            function toggleJenisLayanan() {
                const mini = isMiniGarden();
                const config = mini
                    ? labels.miniGarden
                    : (isPohonTumbang() ? labels.tumbang : labels.pemangkasan);

                document.getElementById('section-title').textContent = config.section;
                document.getElementById('label-asal').textContent = config.asal;
                document.getElementById('label-penanggungjawab').textContent = config.penanggungjawab;
                document.getElementById('label-kontak-permohonan').textContent = config.kontakPermohonan;
                document.getElementById('label-tanggal-permohonan').textContent = config.tanggalPermohonan;

                lokasiPohonSection.classList.toggle('hidden', mini);
                dampakSection.classList.toggle('hidden', mini || ! isPohonTumbang());

                if (mini) {
                    if (tamanHiddenInput) {
                        tamanHiddenInput.removeAttribute('data-searchable-required');
                    }

                    if (lokasiManualInput) {
                        lokasiManualInput.required = false;
                    }
                } else {
                    toggleLokasiMode();
                }
            }

            function toggleLokasiMode() {
                if (isMiniGarden()) {
                    return;
                }

                const isManual = lokasiLuarCheckbox.checked;
                lokasiTamanSection.classList.toggle('hidden', isManual);
                lokasiManualSection.classList.toggle('hidden', ! isManual);

                if (tamanHiddenInput) {
                    if (isManual) {
                        tamanHiddenInput.value = '';
                        tamanHiddenInput.removeAttribute('data-searchable-required');
                    } else {
                        tamanHiddenInput.setAttribute('data-searchable-required', 'true');
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

            function toggleSelesaiSection() {
                const isSelesai = statusSelect.value === 'Selesai';
                penyelesaianSection.classList.toggle('hidden', ! isSelesai);
                if (pendukungInput) {
                    pendukungInput.required = false;
                }
                penyelesaianInput.required = isSelesai;

                if (isSelesai && ! penyelesaianInput.value) {
                    penyelesaianInput.value = new Date().toISOString().slice(0, 10);
                }
            }

            jenisSelect.addEventListener('change', function () {
                toggleJenisLayanan();
                toggleSelesaiSection();
            });
            statusSelect.addEventListener('change', toggleSelesaiSection);
            lokasiLuarCheckbox.addEventListener('change', toggleLokasiMode);
            toggleJenisLayanan();
            toggleSelesaiSection();
            toggleLokasiMode();

            document.querySelectorAll('input[name="pelaksana[]"]').forEach(function (checkbox) {
                function syncLabel() {
                    const label = checkbox.closest('label');
                    if (! label) return;
                    label.classList.toggle('border-green-500', checkbox.checked);
                    label.classList.toggle('bg-green-50', checkbox.checked);
                    label.classList.toggle('border-gray-200', ! checkbox.checked);
                    label.classList.toggle('bg-white', ! checkbox.checked);
                }
                checkbox.addEventListener('change', syncLabel);
                syncLabel();
            });

            const tanggalEksekusiInput = document.getElementById('tanggal_eksekusi');
            const tanggalAkhirInput = document.getElementById('tanggal_akhir_jadwal');
            const totalHariLabel = document.getElementById('total-hari-jadwal');

            function updateTotalHariJadwal() {
                if (! tanggalEksekusiInput || ! tanggalAkhirInput || ! totalHariLabel) {
                    return;
                }

                const mulai = tanggalEksekusiInput.value;
                const akhir = tanggalAkhirInput.value || mulai;

                if (! mulai) {
                    totalHariLabel.textContent = '—';
                    return;
                }

                const start = new Date(mulai + 'T00:00:00');
                const end = new Date(akhir + 'T00:00:00');
                const diff = Math.max(0, Math.round((end - start) / 86400000)) + 1;
                totalHariLabel.textContent = String(diff);
            }

            tanggalEksekusiInput?.addEventListener('change', function () {
                if (tanggalAkhirInput && (! tanggalAkhirInput.value || tanggalAkhirInput.value < tanggalEksekusiInput.value)) {
                    tanggalAkhirInput.value = tanggalEksekusiInput.value;
                }
                updateTotalHariJadwal();
                queueScheduleConflictCheck();
            });
            tanggalAkhirInput?.addEventListener('change', function () {
                updateTotalHariJadwal();
                queueScheduleConflictCheck();
            });
            updateTotalHariJadwal();

            const pemangkasanForm = document.getElementById('pemangkasan-form');
            const excludePemangkasanId = {{ $pemangkasan?->id ?? 'null' }};
            const scheduleConflictUrl = @json(route('admin.pemangkasans.schedule-conflicts'));
            const jadwalKonflikWarning = document.getElementById('jadwal-konflik-warning');
            let scheduleConflicts = [];
            let scheduleConflictConfirmed = false;
            let scheduleConflictTimer = null;

            function selectedPelaksanaTeams() {
                return Array.from(document.querySelectorAll('input[name="pelaksana[]"]:checked'))
                    .map(function (input) { return input.value; });
            }

            function formatConflictMessage(conflicts) {
                const lines = conflicts.map(function (item) {
                    const teams = item.teams_overlap.join(', ');
                    return '• ' + teams + ': ' + item.jenis_layanan + ' — ' + item.lokasi_pohon + ' (' + item.status + ')';
                });

                return 'Tim pelaksana sudah memiliki jadwal pada tanggal ini:\\n\\n'
                    + lines.join('\\n')
                    + '\\n\\nLanjutkan menyimpan operasional ini?';
            }

            function renderScheduleConflictWarning(conflicts) {
                if (! jadwalKonflikWarning) {
                    return;
                }

                if (conflicts.length === 0) {
                    jadwalKonflikWarning.classList.add('hidden');
                    jadwalKonflikWarning.innerHTML = '';
                    return;
                }

                const items = conflicts.map(function (item) {
                    return '<li><strong>' + item.teams_overlap.join(', ') + '</strong>: '
                        + item.jenis_layanan + ' — ' + item.lokasi_pohon + ' (' + item.status + ')</li>';
                }).join('');

                jadwalKonflikWarning.innerHTML =
                    '<p class="font-semibold">⚠️ Tim sudah memiliki jadwal pada tanggal ini</p>'
                    + '<ul class="mt-1 list-inside list-disc space-y-0.5">' + items + '</ul>'
                    + '<p class="mt-2 text-amber-800">Anda akan diminta konfirmasi saat menyimpan.</p>';
                jadwalKonflikWarning.classList.remove('hidden');
            }

            function checkScheduleConflicts() {
                if (! tanggalEksekusiInput) {
                    return;
                }

                const date = tanggalEksekusiInput.value;
                const teams = selectedPelaksanaTeams();

                if (! date || teams.length === 0) {
                    scheduleConflicts = [];
                    scheduleConflictConfirmed = false;
                    renderScheduleConflictWarning([]);
                    return;
                }

                const params = new URLSearchParams();
                params.append('tanggal_eksekusi', date);
                teams.forEach(function (team) {
                    params.append('pelaksana[]', team);
                });
                if (excludePemangkasanId) {
                    params.append('exclude_id', String(excludePemangkasanId));
                }

                fetch(scheduleConflictUrl + '?' + params.toString(), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                })
                    .then(function (response) {
                        if (! response.ok) {
                            throw new Error('Gagal memeriksa jadwal tim.');
                        }
                        return response.json();
                    })
                    .then(function (data) {
                        scheduleConflicts = data.conflicts || [];
                        scheduleConflictConfirmed = false;
                        renderScheduleConflictWarning(scheduleConflicts);
                    })
                    .catch(function () {
                        scheduleConflicts = [];
                        scheduleConflictConfirmed = false;
                        renderScheduleConflictWarning([]);
                    });
            }

            function queueScheduleConflictCheck() {
                scheduleConflictConfirmed = false;
                clearTimeout(scheduleConflictTimer);
                scheduleConflictTimer = setTimeout(checkScheduleConflicts, 300);
            }

            document.querySelectorAll('input[name="pelaksana[]"]').forEach(function (checkbox) {
                checkbox.addEventListener('change', queueScheduleConflictCheck);
            });

            if (pemangkasanForm) {
                pemangkasanForm.addEventListener('submit', function (event) {
                    if (selectedPelaksanaTeams().length === 0) {
                        event.preventDefault();
                        alert('Centang minimal satu tim pelaksana sebelum menyimpan.');
                        return;
                    }

                    if (! pemangkasanForm.checkValidity()) {
                        event.preventDefault();
                        pemangkasanForm.reportValidity();
                        return;
                    }

                    if (scheduleConflicts.length === 0 || scheduleConflictConfirmed) {
                        return;
                    }

                    event.preventDefault();

                    if (confirm(formatConflictMessage(scheduleConflicts))) {
                        scheduleConflictConfirmed = true;
                        if (typeof pemangkasanForm.requestSubmit === 'function') {
                            pemangkasanForm.requestSubmit();
                        } else {
                            pemangkasanForm.submit();
                        }
                    }
                });
            }

            queueScheduleConflictCheck();
        });
    </script>
    @include('admin.partials.tim-auto-suggest', ['mode' => 'checkboxes'])
@endpush
