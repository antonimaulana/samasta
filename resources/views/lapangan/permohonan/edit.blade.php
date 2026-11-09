@extends('layouts.lapangan')

@section('title', 'Progres Permohonan')
@section('header', 'Isi Progres Pekerjaan')

@section('content')
    <x-lapangan.back-link :href="route('lapangan.permohonan.index')" label="Kembali ke daftar" />

    <div class="lapangan-card mb-5 p-5">
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-2xl" aria-hidden="true">{{ \App\Models\Pemangkasan::layananIcon($permohonan->jenis_layanan) }}</span>
            <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ \App\Models\Pemangkasan::badgeClass($permohonan->jenis_layanan) }}">
                {{ $permohonan->jenis_layanan }}
            </span>
            <x-pemangkasan-kategori-badge :kategori="$permohonan->kategori" />
        </div>

        <h2 class="mt-3 text-lg font-black text-gray-900">{{ $permohonan->lokasi_pohon }}</h2>

        @php
            $totalHari = (int) ($permohonan->total_hari ?? 1);
            $hariTercapai = (int) ($permohonan->hari_tercapai ?? 0);
            $persen = (int) ($permohonan->persentase_progres ?? 0);
        @endphp

        <div class="mt-4 rounded-xl bg-green-50 px-4 py-3">
            <div class="flex justify-between text-sm font-semibold text-green-900">
                <span>Progres keseluruhan</span>
                <span>{{ $hariTercapai }}/{{ $totalHari }} hari ({{ $persen }}%)</span>
            </div>
            <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-green-200">
                <div class="lapangan-progress-bar h-full rounded-full" style="width: {{ max(4, $persen) }}%"></div>
            </div>
        </div>

        <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
            <div class="rounded-xl bg-gray-50 px-3 py-2.5">
                <dt class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Jadwal</dt>
                <dd class="mt-1 font-semibold text-gray-900">{{ \App\Support\PemangkasanSchedule::labelRentang($permohonan) }}</dd>
            </div>
            <div class="rounded-xl bg-gray-50 px-3 py-2.5">
                <dt class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Penanggung Jawab</dt>
                <dd class="mt-1 font-semibold text-gray-900">{{ $permohonan->penanggungjawab }}</dd>
            </div>
            <div class="rounded-xl bg-gray-50 px-3 py-2.5">
                <dt class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Asal</dt>
                <dd class="mt-1 text-gray-800">{{ $permohonan->asal }}</dd>
            </div>
            <div class="rounded-xl bg-gray-50 px-3 py-2.5">
                <dt class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Kontak</dt>
                <dd class="mt-1 text-gray-800">{{ $permohonan->kontak_permohonan }}</dd>
            </div>
            <div class="sm:col-span-2 rounded-xl bg-gray-50 px-3 py-2.5">
                <dt class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Tim Pelaksana</dt>
                <dd class="mt-1 text-gray-800">{{ $permohonan->pelaksanaLabel() ?: '—' }}</dd>
            </div>
        </dl>

        @if ($permohonan->hasPendukungPelaksanaanFile())
            <div class="mt-4 border-t border-gray-100 pt-4">
                <p class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Lampiran Admin</p>
                @if ($permohonan->pendukungPelaksanaanIsPdf())
                    <a href="{{ $permohonan->pendukung_pelaksanaan_url }}" target="_blank" rel="noopener"
                       class="mt-2 inline-flex items-center gap-2 rounded-xl border border-green-200 bg-green-50 px-3 py-2 text-sm font-semibold text-green-800">
                        📄 {{ $permohonan->pendukungPelaksanaanFilename() }}
                    </a>
                @else
                    <a href="{{ $permohonan->pendukung_pelaksanaan_url }}" target="_blank" rel="noopener" class="mt-2 inline-block">
                        <img src="{{ $permohonan->pendukung_pelaksanaan_url }}" alt="Lampiran"
                             class="h-28 rounded-xl border border-gray-200 object-cover shadow-sm">
                    </a>
                @endif
            </div>
        @endif
    </div>

    <div class="lapangan-card p-5 sm:p-6">
        <form id="lapangan-permohonan-progres-form"
              action="{{ route('lapangan.permohonan.update', $permohonan) }}"
              method="POST"
              enctype="multipart/form-data">
            @csrf

            @include('lapangan.permohonan._progress-form', [
                'permohonan' => $permohonan,
                'armadaInventory' => $armadaInventory,
                'rostersByTeam' => $rostersByTeam ?? [],
                'inputTeams' => $inputTeams ?? [],
                'operatorTim' => $operatorTim ?? null,
                'showArmadaForm' => $showArmadaForm ?? false,
                'teamStatusByDate' => $teamStatusByDate ?? [],
            ])

            <div class="lapangan-form-actions mt-8 space-y-3 sm:border-t sm:border-gray-100 sm:pt-6">
                <button type="submit"
                        class="lapangan-btn-primary w-full rounded-2xl px-6 py-4 text-base font-black text-white sm:w-auto">
                    ✅ Simpan Progres
                </button>
                <a href="{{ route('lapangan.permohonan.index') }}"
                   class="block w-full rounded-2xl border-2 border-gray-200 px-6 py-3 text-center text-sm font-semibold text-gray-600 hover:bg-gray-50 sm:w-auto">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('lapangan-permohonan-progres-form');
            if (!form) {
                return;
            }

            const teamStatusByDate = @json($teamStatusByDate ?? []);
            const statusPanel = document.getElementById('tim-progress-status-panel');

            function refreshTeamStatusPanel() {
                if (! statusPanel) {
                    return;
                }

                const tanggalInput = document.getElementById('tanggal_progres');
                const datePart = (tanggalInput?.value || '').split('T')[0];
                const statuses = teamStatusByDate[datePart] || teamStatusByDate[@json(now()->toDateString())] || {};

                statusPanel.querySelectorAll('[data-tim-status]').forEach(function (chip) {
                    const tim = chip.dataset.timStatus;
                    const done = statuses[tim] === @json(\App\Support\PermohonanPelaksanaProgress::STATUS_DIKERJAKAN);
                    chip.classList.toggle('border-emerald-200', done);
                    chip.classList.toggle('bg-emerald-50', done);
                    chip.classList.toggle('text-emerald-900', done);
                    chip.classList.toggle('border-gray-200', ! done);
                    chip.classList.toggle('bg-gray-50', ! done);
                    chip.classList.toggle('text-gray-600', ! done);
                    const badge = chip.querySelector('[data-tim-status-label]');
                    if (badge) {
                        badge.textContent = done ? 'Dikerjakan' : 'Menunggu';
                        badge.classList.toggle('bg-emerald-200', done);
                        badge.classList.toggle('text-emerald-900', done);
                        badge.classList.toggle('bg-gray-200', ! done);
                        badge.classList.toggle('text-gray-700', ! done);
                    }
                });
            }

            document.getElementById('tanggal_progres')?.addEventListener('change', refreshTeamStatusPanel);
            document.getElementById('tanggal_progres')?.addEventListener('input', refreshTeamStatusPanel);
            refreshTeamStatusPanel();

            const mulai = @json($permohonan->tanggal_eksekusi->format('Y-m-d'));
            const akhir = @json(($permohonan->tanggal_akhir_jadwal ?? $permohonan->tanggal_eksekusi)->format('Y-m-d'));
            const totalHari = @json((int) ($permohonan->total_hari ?? 1));
            const confirmEarlyInput = document.getElementById('confirm_early_start');
            const confirmExtendInput = document.getElementById('confirm_extend_schedule');

            form.addEventListener('submit', function (event) {
                if (! form.checkValidity()) {
                    event.preventDefault();
                    form.reportValidity();
                    form.querySelector(':invalid')?.scrollIntoView({ behavior: 'smooth', block: 'center' });

                    return;
                }

                const tanggalInput = document.getElementById('tanggal_progres');
                const datePart = (tanggalInput?.value || '').split('T')[0];

                if (! datePart) {
                    return;
                }

                const start = new Date(mulai + 'T00:00:00');
                const end = new Date(akhir + 'T00:00:00');
                const current = new Date(datePart + 'T00:00:00');
                const hariKe = Math.max(0, Math.round((current - start) / 86400000)) + 1;

                if (current < start && confirmEarlyInput?.value !== '1') {
                    event.preventDefault();
                    const lanjut = window.confirm(
                        'Pelaksanaan dijadwalkan mulai '+mulai+', tetapi tanggal yang dipilih lebih awal. Lanjutkan mulai pekerjaan?',
                    );

                    if (! lanjut) {
                        return;
                    }

                    if (confirmEarlyInput) {
                        confirmEarlyInput.value = '1';
                    }

                    form.requestSubmit();

                    return;
                }

                if ((current > end || hariKe > totalHari) && confirmExtendInput?.value !== '1') {
                    event.preventDefault();
                    const lanjut = window.confirm(
                        'Tanggal pelaksanaan melebihi estimasi jadwal ('+totalHari+' hari). Pekerjaan perlu waktu lebih lanjut?',
                    );

                    if (! lanjut) {
                        return;
                    }

                    if (confirmExtendInput) {
                        confirmExtendInput.value = '1';
                    }

                    form.requestSubmit();

                    return;
                }
            });
        });
    </script>
@endpush
