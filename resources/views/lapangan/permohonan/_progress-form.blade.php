@php

    $permohonanStatus = $permohonan->status;

    $currentStatus = old('status', $permohonanStatus === 'Rencana' ? 'Diproses' : $permohonanStatus);

    $isPohonTumbang = $permohonan->jenis_layanan === 'Penanganan Pohon Tumbang';

    $isMiniGarden = $permohonan->jenis_layanan === 'Pemasangan Mini Garden';

    $tanggalProgresDate = filled(old('tanggal_progres'))
        ? \App\Support\OperasionalPelaksanaanTime::datePart(old('tanggal_progres'))
        : now()->toDateString();

    $hariKe = \App\Support\PemangkasanSchedule::hariKe($permohonan->tanggal_eksekusi, $tanggalProgresDate);

    $totalHari = (int) ($permohonan->total_hari ?? 1);

    $showWorkPhotos = $permohonanStatus !== 'Rencana' || $currentStatus === 'Diproses';

    $statusBadgeClass = match ($permohonanStatus) {

        'Selesai' => 'bg-emerald-100 text-emerald-800',

        'Diproses' => 'bg-amber-100 text-amber-800',

        default => 'bg-blue-100 text-blue-800',

    };

    $jadwalAkhir = $permohonan->tanggal_akhir_jadwal ?? $permohonan->tanggal_eksekusi;

@endphp



@if ($permohonan->progres->isNotEmpty())

    <div class="mb-6">

        <p class="lapangan-section-title mb-3 text-sm font-bold text-gray-800">Riwayat hari sebelumnya</p>

        <div class="space-y-2">

            @foreach ($permohonan->progres as $entry)

                <div class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm">

                    <div>

                        <p class="font-bold text-gray-900">

                            📆 Hari ke-{{ $entry->hari_ke }} · {{ \App\Support\OperasionalPelaksanaanTime::display($entry->tanggal) }}

                        </p>

                        <p class="text-xs text-gray-600">{{ $entry->petugasLabel() }}

                            @if ($entry->catatan) · {{ Str::limit($entry->catatan, 50) }} @endif

                        </p>

                    </div>

                    <a href="{{ route('lapangan.permohonan.progres.pdf', ['pemangkasan' => $permohonan, 'pemangkasanProgres' => $entry]) }}"

                       class="inline-flex items-center gap-1 rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-bold text-red-700 hover:bg-red-50"

                       target="_blank" rel="noopener">

                        📄 Unduh PDF

                    </a>

                </div>

            @endforeach

        </div>

    </div>

@endif



<div class="space-y-5">

    <div class="rounded-2xl border border-gray-200 bg-white p-4 sm:p-5">

        <p class="lapangan-section-title text-sm font-black text-gray-900">1. Data progres hari ini</p>

        <div class="mt-4 grid gap-4 sm:grid-cols-2">

            <div>

                <x-operasional-pelaksanaan-datetime

                    name="tanggal_progres"

                    :min="$permohonan->tanggal_eksekusi->format('Y-m-d').'T00:00'"

                    :max="$jadwalAkhir->format('Y-m-d').'T23:59'"

                    variant="lapangan"

                />

                <p class="mt-2 inline-flex items-center gap-1 rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-800">

                    Hari ke-<span id="hari-ke-label">{{ $hariKe }}</span> dari {{ $totalHari }} hari

                </p>

            </div>

            <div class="sm:col-span-2">
                <x-lapangan.field-label required>Petugas</x-lapangan.field-label>
                <x-petugas-picker
                    :team-names="$permohonan->pelaksana ?? []"
                    :selected-ids="old('petugas_ids', [])"
                    :rosters-by-team="$rostersByTeam ?? []"
                    :roster-url="route('lapangan.tim-pelaksanas.roster')"
                    variant="lapangan"
                />
            </div>

        </div>



        @unless ($isMiniGarden)

            <div id="foto-dokumentasi-section" class="{{ $showWorkPhotos ? '' : 'hidden' }}">

                <x-operasional-foto-fields

                    :record="null"

                    variant="lapangan"

                    :required="$showWorkPhotos"

                />

            </div>

        @endunless



        <div class="mt-4">

            <x-lapangan.field-label for="catatan">Catatan pekerjaan</x-lapangan.field-label>

            <textarea name="catatan" id="catatan" rows="3"

                      placeholder="Contoh: Pemangkasan 15 pohon di area utara taman"

                      class="lapangan-field-input min-h-[5rem] text-sm">{{ old('catatan') }}</textarea>

            @error('catatan')

                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>

            @enderror

        </div>

    </div>



    @if ($isPohonTumbang)

        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">

            <x-lapangan.field-label for="dampak">Dampak ke sekitar</x-lapangan.field-label>

            <textarea name="dampak" id="dampak" rows="3"

                      placeholder="Contoh: Menutup badan jalan sementara"

                      class="lapangan-field-input min-h-[5rem] text-sm">{{ old('dampak') }}</textarea>

            @error('dampak')

                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>

            @enderror

        </div>

    @endif



    @include('admin.pemeliharaan_tamans._armada-form', [

        'kinerja' => null,

        'armadaRows' => old('armada'),

        'armadaInventory' => $armadaInventory,

        'isTimArmada' => $permohonan->usesTimArmada(),

        'operatorTim' => $permohonan->usesTimArmada() ? \App\Models\PemeliharaanTaman::TIM_ARMADA : null,

        'armadaVisibility' => 'always',

        'armadaRequired' => false,

    ])



    <div class="rounded-2xl border-2 border-blue-200 bg-gradient-to-br from-blue-50 to-white p-4 sm:p-5">

        <p class="lapangan-section-title text-sm font-black text-blue-900">Konfirmasi status pekerjaan</p>

        <div class="mt-4 flex flex-wrap items-center gap-2 rounded-xl border border-blue-100 bg-white px-4 py-3">

            <span class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Status saat ini</span>

            <span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ $statusBadgeClass }}">{{ $permohonanStatus }}</span>

        </div>



        @if ($permohonanStatus === 'Rencana')

            <input type="hidden" name="status" id="status" value="Diproses">

            <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3.5 text-sm font-bold text-amber-950">

                🚀 Mulai pekerjaan

            </div>

        @elseif ($permohonanStatus === 'Diproses')

            <x-lapangan.field-label for="status" :required="true" class="mt-4">Tindakan konfirmasi</x-lapangan.field-label>

            <select name="status" id="status" required

                    class="lapangan-field-input border-blue-200 font-semibold">

                <option value="Diproses" @selected($currentStatus === 'Diproses')>💾 Simpan progres hari ini (masih dikerjakan)</option>

                <option value="Selesai" @selected($currentStatus === 'Selesai')>✅ Konfirmasi pekerjaan selesai</option>

            </select>

            @error('status')

                <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>

            @enderror

        @endif



        <div id="tanggal-penyelesaian-section" class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 p-4 {{ $currentStatus === 'Selesai' ? '' : 'hidden' }}">

            <x-lapangan.field-label for="tanggal_penyelesaian" :required="true">Tanggal penyelesaian</x-lapangan.field-label>

            <input type="date" name="tanggal_penyelesaian" id="tanggal_penyelesaian"

                   value="{{ old('tanggal_penyelesaian', $permohonan->tanggal_penyelesaian?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"

                   class="lapangan-field-input border-emerald-200">

            @error('tanggal_penyelesaian')

                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>

            @enderror

        </div>

    </div>

</div>



@push('scripts')

    <script>

        document.addEventListener('DOMContentLoaded', function () {

            const statusSelect = document.getElementById('status');

            const penyelesaianSection = document.getElementById('tanggal-penyelesaian-section');

            const penyelesaianInput = document.getElementById('tanggal_penyelesaian');

            const tanggalProgresInput = document.getElementById('tanggal_progres');

            const hariKeLabel = document.getElementById('hari-ke-label');

            const fotoDokumentasiSection = document.getElementById('foto-dokumentasi-section');

            const mulai = @json($permohonan->tanggal_eksekusi->format('Y-m-d'));

            const isStartingWork = @json($permohonanStatus === 'Rencana');



            function toggleStatusFields() {

                const status = statusSelect?.value;

                const isSelesai = status === 'Selesai';

                const showPhotos = isStartingWork || status === 'Diproses' || isSelesai;



                penyelesaianSection?.classList.toggle('hidden', ! isSelesai);

                fotoDokumentasiSection?.classList.toggle('hidden', ! showPhotos);



                if (penyelesaianInput) {

                    penyelesaianInput.required = isSelesai;

                }

            }



            function updateHariKe() {

                if (! tanggalProgresInput || ! hariKeLabel || ! mulai) return;

                const datePart = (tanggalProgresInput.value || '').split('T')[0];

                if (! datePart) return;

                const start = new Date(mulai + 'T00:00:00');

                const current = new Date(datePart + 'T00:00:00');

                const diff = Math.max(0, Math.round((current - start) / 86400000));

                hariKeLabel.textContent = String(diff + 1);

            }



            statusSelect?.addEventListener('change', toggleStatusFields);

            tanggalProgresInput?.addEventListener('change', updateHariKe);

            toggleStatusFields();

            updateHariKe();

        });

    </script>

@endpush

