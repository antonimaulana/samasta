@php
    $permohonanStatus = $permohonan->status;
    $currentStatus = old('status', $permohonanStatus === 'Rencana' ? 'Diproses' : $permohonanStatus);
    $isPohonTumbang = $permohonan->jenis_layanan === 'Penanganan Pohon Tumbang';
    $isMiniGarden = $permohonan->jenis_layanan === 'Pemasangan Mini Garden';
    $today = now()->toDateString();
    $tanggalProgres = old('tanggal_progres', $today);
    $todayEntry = $permohonan->progres->first(fn ($row) => $row->tanggal->toDateString() === $tanggalProgres);
    $hariKe = \App\Support\PemangkasanSchedule::hariKe($permohonan->tanggal_eksekusi, $tanggalProgres);
    $totalHari = (int) ($permohonan->total_hari ?? 1);
    $showWorkPhotos = $permohonanStatus !== 'Rencana' || $currentStatus === 'Diproses';
    $statusBadgeClass = match ($permohonanStatus) {
        'Selesai' => 'bg-emerald-100 text-emerald-800',
        'Diproses' => 'bg-amber-100 text-amber-800',
        default => 'bg-blue-100 text-blue-800',
    };
@endphp

@if ($permohonan->progres->isNotEmpty())
    <div class="mb-6">
        <p class="lapangan-section-title mb-3 text-sm font-bold text-gray-800">Riwayat hari sebelumnya</p>
        <div class="space-y-2">
            @foreach ($permohonan->progres as $entry)
                <div class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm">
                    <div>
                        <p class="font-bold text-gray-900">
                            📆 Hari ke-{{ $entry->hari_ke }} · {{ $entry->tanggal->format('d/m/Y') }}
                        </p>
                        <p class="text-xs text-gray-600">{{ $entry->jumlah_personil }} personil
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
        <p class="mt-1 text-xs text-gray-500">Ubah tanggal jika mengisi progres hari lain dalam jadwal.</p>

        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
                <label for="tanggal_progres" class="mb-1 block text-sm font-bold text-gray-700">Tanggal progres *</label>
                <input type="date" name="tanggal_progres" id="tanggal_progres"
                       value="{{ $tanggalProgres }}"
                       min="{{ $permohonan->tanggal_eksekusi->format('Y-m-d') }}"
                       max="{{ ($permohonan->tanggal_akhir_jadwal ?? $permohonan->tanggal_eksekusi)->format('Y-m-d') }}"
                       required
                       class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 text-base focus:border-green-500 focus:outline-none focus:ring-4 focus:ring-green-100">
                <p class="mt-2 inline-flex items-center gap-1 rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-800">
                    Hari ke-<span id="hari-ke-label">{{ $hariKe }}</span> dari {{ $totalHari }} hari
                </p>
                @error('tanggal_progres')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="jumlah_personil" class="mb-1 block text-sm font-bold text-gray-700">Jumlah personil *</label>
                <input type="number" name="jumlah_personil" id="jumlah_personil" min="1" max="9999" required
                       value="{{ old('jumlah_personil', $todayEntry?->jumlah_personil) }}"
                       placeholder="Contoh: 5"
                       class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 text-base focus:border-green-500 focus:outline-none focus:ring-4 focus:ring-green-100">
                @error('jumlah_personil')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        @unless ($isMiniGarden)
            <div id="foto-hint-banner" class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2.5 text-xs text-amber-900 {{ $showWorkPhotos ? '' : 'hidden' }}">
                📸 <strong>Foto wajib</strong> — ambil di lokasi kerja, pastikan pencahayaan cukup.
            </div>

            <div id="foto-sebelum-saat-section" class="mt-4 grid gap-3 {{ $showWorkPhotos ? '' : 'hidden' }}">
                @foreach ([
                    'foto_sebelum' => ['label' => 'Foto SEBELUM', 'hint' => 'Kondisi lokasi sebelum dikerjakan', 'emoji' => '📷'],
                    'foto_saat' => ['label' => 'Foto SAAT', 'hint' => 'Tim sedang bekerja', 'emoji' => '👷'],
                ] as $field => $meta)
                    <div class="rounded-xl border-2 border-dashed border-emerald-300 bg-emerald-50/50 p-4">
                        <label for="{{ $field }}" class="flex items-center gap-2 text-sm font-black text-emerald-900">
                            <span aria-hidden="true">{{ $meta['emoji'] }}</span> {{ $meta['label'] }} *
                        </label>
                        <p class="mt-0.5 text-xs text-emerald-700">{{ $meta['hint'] }}</p>
                        <input type="file" name="{{ $field }}" id="{{ $field }}" accept="image/*" capture="environment"
                               class="mt-3 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-600 file:px-4 file:py-2 file:font-bold file:text-white">
                        @error($field)
                            <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                        @if ($todayEntry?->{$field})
                            <img src="{{ $todayEntry->fotoUrl($field) }}" alt="{{ $meta['label'] }}"
                                 class="mt-3 h-36 w-full rounded-xl border border-gray-200 object-cover shadow-sm">
                        @endif
                    </div>
                @endforeach
            </div>

            <div id="foto-sesudah-section" class="mt-3 {{ $currentStatus === 'Selesai' ? 'hidden' : '' }}">
                <div class="rounded-xl border-2 border-dashed border-emerald-300 bg-emerald-50/50 p-4">
                    <label for="foto_sesudah" class="flex items-center gap-2 text-sm font-black text-emerald-900">
                        <span aria-hidden="true">✨</span> Foto SESUDAH *
                    </label>
                    <p class="mt-0.5 text-xs text-emerald-700">Hasil akhir pekerjaan hari ini</p>
                    <input type="file" name="foto_sesudah" id="foto_sesudah" accept="image/*" capture="environment"
                           class="mt-3 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-600 file:px-4 file:py-2 file:font-bold file:text-white">
                    @error('foto_sesudah')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                    @if ($todayEntry?->foto_sesudah)
                        <img src="{{ $todayEntry->fotoUrl('foto_sesudah') }}" alt="Foto sesudah"
                             class="mt-3 h-36 w-full rounded-xl border border-gray-200 object-cover shadow-sm">
                    @endif
                </div>
            </div>
        @endunless

        <div class="mt-4">
            <label for="catatan" class="mb-1 block text-sm font-bold text-gray-700">Catatan pekerjaan</label>
            <textarea name="catatan" id="catatan" rows="3"
                      placeholder="Contoh: Pemangkasan 15 pohon di area utara taman"
                      class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 text-sm focus:border-green-500 focus:outline-none focus:ring-4 focus:ring-green-100">{{ old('catatan', $todayEntry?->catatan) }}</textarea>
            @error('catatan')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    @if ($isPohonTumbang)
        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
            <label for="dampak" class="mb-1 block text-sm font-bold text-gray-800">Dampak ke sekitar</label>
            <textarea name="dampak" id="dampak" rows="3"
                      placeholder="Contoh: Menutup badan jalan sementara"
                      class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm">{{ old('dampak', $permohonan->dampak) }}</textarea>
            @error('dampak')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    @endif

    @include('admin.pemeliharaan_tamans._armada-form', [
        'kinerja' => $permohonan,
        'armadaInventory' => $armadaInventory,
        'isTimArmada' => $permohonan->usesTimArmada(),
        'operatorTim' => $permohonan->usesTimArmada() ? \App\Models\PemeliharaanTaman::TIM_ARMADA : null,
        'armadaVisibility' => 'always',
        'armadaRequired' => false,
    ])

    <div class="rounded-2xl border-2 border-blue-200 bg-gradient-to-br from-blue-50 to-white p-4 sm:p-5">
        <p class="lapangan-section-title text-sm font-black text-blue-900">Konfirmasi status pekerjaan</p>
        <p class="mt-1 text-xs text-blue-800">Isi progres di atas terlebih dahulu, lalu tentukan langkah selanjutnya di bawah.</p>

        <div class="mt-4 flex flex-wrap items-center gap-2 rounded-xl border border-blue-100 bg-white px-4 py-3">
            <span class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Status saat ini</span>
            <span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ $statusBadgeClass }}">{{ $permohonanStatus }}</span>
        </div>

        @if ($permohonanStatus === 'Rencana')
            <input type="hidden" name="status" id="status" value="Diproses">
            <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3.5 text-sm text-amber-950">
                <p class="font-bold">🚀 Mulai pekerjaan</p>
                <p class="mt-1 text-xs leading-relaxed">
                    Simpan form ini untuk memulai pekerjaan. Status akan berubah ke <strong>Diproses</strong>
                    dan progres hari ini tercatat sebagai hari pertama.
                </p>
            </div>
        @elseif ($permohonanStatus === 'Diproses')
            <label for="status" class="mt-4 mb-2 block text-sm font-bold text-blue-900">Tindakan konfirmasi *</label>
            <select name="status" id="status" required
                    class="w-full rounded-xl border-2 border-blue-200 bg-white px-4 py-3.5 text-base font-semibold focus:border-green-500 focus:outline-none focus:ring-4 focus:ring-green-100">
                <option value="Diproses" @selected($currentStatus === 'Diproses')>💾 Simpan progres hari ini (masih dikerjakan)</option>
                <option value="Selesai" @selected($currentStatus === 'Selesai')>✅ Konfirmasi pekerjaan selesai</option>
            </select>
            @error('status')
                <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
            @enderror
        @endif

        <div id="tanggal-penyelesaian-section" class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 p-4 {{ $currentStatus === 'Selesai' ? '' : 'hidden' }}">
            <label for="tanggal_penyelesaian" class="mb-1 block text-sm font-black text-emerald-900">Tanggal penyelesaian *</label>
            <p class="mb-2 text-xs text-emerald-800">Isi tanggal pekerjaan benar-benar selesai.</p>
            <input type="date" name="tanggal_penyelesaian" id="tanggal_penyelesaian"
                   value="{{ old('tanggal_penyelesaian', $permohonan->tanggal_penyelesaian?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                   class="w-full rounded-xl border-2 border-emerald-200 bg-white px-4 py-3 text-base">
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
            const fotoSebelumSaatSection = document.getElementById('foto-sebelum-saat-section');
            const fotoSesudahSection = document.getElementById('foto-sesudah-section');
            const fotoHintBanner = document.getElementById('foto-hint-banner');
            const mulai = @json($permohonan->tanggal_eksekusi->format('Y-m-d'));
            const isStartingWork = @json($permohonanStatus === 'Rencana');

            function toggleStatusFields() {
                const status = statusSelect?.value;
                const isSelesai = status === 'Selesai';
                const showPhotos = isStartingWork || status === 'Diproses';

                penyelesaianSection?.classList.toggle('hidden', ! isSelesai);
                fotoSebelumSaatSection?.classList.toggle('hidden', ! showPhotos);
                fotoSesudahSection?.classList.toggle('hidden', isSelesai);
                fotoHintBanner?.classList.toggle('hidden', ! showPhotos);

                if (penyelesaianInput) {
                    penyelesaianInput.required = isSelesai;
                }
            }

            function updateHariKe() {
                if (! tanggalProgresInput || ! hariKeLabel || ! mulai) return;
                const start = new Date(mulai + 'T00:00:00');
                const current = new Date(tanggalProgresInput.value + 'T00:00:00');
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
