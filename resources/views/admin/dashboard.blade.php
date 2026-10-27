@extends('layouts.admin')

@section('title', 'Dashboard')
@section('header', 'Dashboard')

@section('content')
    @php
        $needsAttention = in_array($executive_status['tone'] ?? 'success', ['danger', 'warning'], true);
        $statusConfig = $needsAttention
            ? [
                'label' => 'Operasional Perlu Perhatian',
                'badge' => 'bg-amber-500/25 text-amber-50 ring-amber-200/40',
                'dot' => 'bg-amber-300',
            ]
            : [
                'label' => 'Operasional Baik',
                'badge' => 'bg-white/15 text-white ring-white/25',
                'dot' => 'bg-emerald-300',
            ];

        $jadwalTiles = [
            ['key' => 'hari_ini', 'label' => 'Hari Ini', 'desc' => 'Pelaksanaan aktif hari ini', 'danger' => false],
            ['key' => 'besok', 'label' => 'Besok (H-1)', 'desc' => 'Persiapan besok', 'danger' => false],
            ['key' => 'diproses', 'label' => 'Sedang Diproses', 'desc' => 'Dikerjakan di lapangan', 'danger' => false],
            ['key' => 'terlambat', 'label' => 'Terlambat', 'desc' => 'Lewat jadwal / belum selesai', 'danger' => true],
        ];

        $formatLuasan = function (int $luasan): string {
            if ($luasan >= 1_000_000) {
                return number_format($luasan / 1_000_000, 1, ',', '.').' jt m²';
            }
            if ($luasan >= 10_000) {
                return number_format($luasan / 1_000, 1, ',', '.').' rb m²';
            }

            return number_format($luasan, 0, ',', '.').' m²';
        };

        $surveyLabel = match (true) {
            $survey_summary['total'] === 0 => 'Belum ada data',
            $survey_summary['average'] >= 4.5 => 'Sangat Baik',
            $survey_summary['average'] >= 4.0 => 'Baik',
            $survey_summary['average'] >= 3.0 => 'Cukup',
            default => 'Perlu Perhatian',
        };

        $surveyValue = $survey_summary['total'] > 0
            ? number_format($survey_summary['average'], 1).' /5'
            : '—';

        $aduanTone = $aduan_baru > 0 ? 'rose' : 'sky';
    @endphp

    {{-- Hero --}}
    <section class="relative mb-8 overflow-hidden rounded-2xl bg-gradient-to-br from-green-700 via-emerald-600 to-teal-600 p-6 text-white shadow-lg shadow-emerald-200/50 sm:p-8">
        <div class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-white/10 blur-2xl"></div>
        <div class="pointer-events-none absolute -bottom-20 -left-10 h-48 w-48 rounded-full bg-teal-300/20 blur-2xl"></div>

        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
            <div class="max-w-3xl">
                @if ($is_viewer)
                    <p class="dash-kicker text-green-100">Ringkasan Pimpinan</p>
                @else
                    <p class="text-[10px] font-bold uppercase leading-snug tracking-wide text-green-100 sm:text-[11px]">
                        Dinas Perumahan, Kawasan Permukiman dan Pertamanan
                        <span class="mx-1.5 font-normal text-green-200/70">·</span>
                        Bidang Pertamanan dan Pemakaman
                    </p>
                @endif
                <h2 class="mt-3 text-2xl font-black sm:text-3xl">
                    {{ $is_viewer ? 'Snapshot Operasional Pertamanan Batam' : 'Dashboard Operasional' }}
                </h2>
                <span class="mt-3 inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $statusConfig['badge'] }}">
                    <span class="h-2 w-2 rounded-full {{ $statusConfig['dot'] }}"></span>
                    {{ $statusConfig['label'] }}
                </span>
                <p class="mt-3 text-sm leading-relaxed text-green-50/90">
                    {{ now()->translatedFormat('l, d F Y') }} · {{ now()->translatedFormat('H:i') }} WIB
                </p>
            </div>

            <div class="flex shrink-0 flex-col gap-4 lg:items-end">
                <a href="{{ route('admin.dashboard.export-pdf') }}" class="dash-btn-hero">
                    <x-admin.nav-icon name="download" class="h-4 w-4" />
                    Unduh Ringkasan
                </a>
            </div>
        </div>
    </section>

    {{-- Ringkasan data induk --}}
    <div class="mb-8 grid gap-4 lg:grid-cols-2 xl:grid-cols-4">
        <x-admin.dash-stat-card
            :href="route('admin.tamans.index')"
            tone="emerald"
            label="Data Taman"
            :value="number_format($total_taman)"
            subtitle="Total lokasi"
            footer="Kelola taman →"
            animate="0">
            <x-slot:meta>
                <span class="dash-chip dash-chip--emerald">{{ $formatLuasan($total_luasan_taman) }} luasan</span>
            </x-slot:meta>
            <x-slot:icon>
                <x-admin.nav-icon name="taman" class="h-5 w-5" />
            </x-slot:icon>
        </x-admin.dash-stat-card>

        <x-admin.dash-stat-card
            :href="route('admin.bibits.index')"
            tone="teal"
            label="Data Bibit"
            :value="number_format($total_stok_bibit)"
            subtitle="Total stok"
            footer="Kelola bibit →"
            animate="1">
            <x-slot:meta>
                <div class="flex flex-wrap gap-2">
                    <span class="dash-chip dash-chip--teal">{{ number_format($total_varietas_bibit) }} varietas</span>
                    <span class="dash-chip dash-chip--cyan">{{ $bibit_siap_persen }}% siap tanam</span>
                </div>
            </x-slot:meta>
            <x-slot:icon>
                <x-admin.nav-icon name="bibit" class="h-5 w-5" />
            </x-slot:icon>
        </x-admin.dash-stat-card>

        <x-admin.dash-stat-card
            :href="route('admin.armadas.index')"
            tone="green"
            label="Data Armada"
            :value="number_format($total_armada)"
            subtitle="Unit"
            footer="Lihat armada →"
            animate="2">
            <x-slot:extra>
                @if ($armada_recent_usage->isNotEmpty())
                    <ul class="mt-3 space-y-1.5 border-t border-green-100 pt-3 text-xs text-gray-600">
                        @foreach ($armada_recent_usage->take(3) as $entry)
                            <li class="flex items-center justify-between gap-2">
                                <span class="truncate font-medium text-gray-800">{{ Str::limit($entry['armada'], 22) }}</span>
                                <span class="shrink-0 text-gray-400">{{ $entry['tanggal']?->format('d/m') ?? '—' }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-3 text-xs text-gray-500">Belum ada riwayat penggunaan tercatat.</p>
                @endif
            </x-slot:extra>
            <x-slot:icon>
                <x-admin.nav-icon name="armada" class="h-5 w-5" />
            </x-slot:icon>
        </x-admin.dash-stat-card>

        <x-admin.dash-stat-card
            :href="route('admin.tim-pelaksanas.index')"
            tone="cyan"
            label="Data Petugas"
            :value="number_format($total_petugas)"
            subtitle="Petugas"
            footer="Kelola tim kerja →"
            animate="3">
            <x-slot:meta>
                <span class="dash-chip dash-chip--cyan">{{ number_format($total_tim_kerja) }} tim kerja</span>
            </x-slot:meta>
            <x-slot:icon>
                <x-admin.nav-icon name="users" class="h-5 w-5" />
            </x-slot:icon>
        </x-admin.dash-stat-card>
    </div>

    <div class="mb-8 grid gap-4 sm:grid-cols-2">
        <x-admin.dash-stat-card
            :href="route('admin.survey-kepuasan.index')"
            tone="violet"
            label="Kepuasan Masyarakat"
            :value="$surveyValue"
            :subtitle="$surveyLabel">
            <x-slot:meta>
                <p class="text-xs text-gray-500">{{ number_format($survey_summary['total']) }} respons · {{ now()->translatedFormat('F Y') }}</p>
            </x-slot:meta>
            <x-slot:icon>
                <x-admin.nav-icon name="star" class="h-5 w-5" />
            </x-slot:icon>
        </x-admin.dash-stat-card>

        <x-admin.dash-stat-card
            :href="route('admin.aduan-masyarakats.index')"
            :tone="$aduanTone"
            label="Masukan Masyarakat"
            :value="number_format($aduan_baru)"
            :subtitle="'Baru · '.number_format($aduan_aktif).' aktif'">
            <x-slot:icon>
                <x-admin.nav-icon name="chat" class="h-5 w-5" />
            </x-slot:icon>
        </x-admin.dash-stat-card>
    </div>

    {{-- Operasional pertamanan --}}
    <x-admin.dash-section title="Operasional Pertamanan">
        <x-slot:icon>
            <x-admin.nav-icon name="operasional" class="h-5 w-5" />
        </x-slot:icon>

        <div class="space-y-8">
            <div>
                <div class="dash-block-heading">
                    <p class="dash-kicker dash-kicker--emerald">Pemeliharaan Rutin</p>
                    <p class="dash-section__desc">Pelaksanaan hari ini per tim</p>
                </div>

                @if (count($pemeliharaan_tim_hari_ini) > 0)
                    <div class="dash-metric-grid-6 dash-metric-grid--pemeliharaan">
                        @foreach ($pemeliharaan_tim_hari_ini as $index => $timRow)
                            @php
                                $theme = ($index % 6) + 1;
                                $isIdle = ($timRow['count'] ?? 0) === 0;
                            @endphp
                            <a href="{{ $timRow['url'] }}"
                               title="{{ $timRow['tim'] }}"
                               class="dash-metric-tile dash-metric-tile--{{ $theme }} {{ $isIdle ? 'dash-metric-tile--idle' : '' }}">
                                <p class="dash-metric-tile__value">{{ $timRow['count'] }}</p>
                                <p class="dash-metric-tile__label">{{ Str::replaceFirst('Tim ', '', $timRow['tim']) }}</p>
                                <p class="dash-metric-tile__meta">Hari ini</p>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500">Belum ada kegiatan pemeliharaan rutin tercatat untuk hari ini.</p>
                @endif
            </div>

            <div class="border-t border-gray-100 pt-6">
                <div class="dash-block-heading">
                    <p class="dash-kicker dash-kicker--emerald">Operasional Permohonan</p>
                    <p class="dash-section__desc">Jadwal pelaksanaan layanan permohonan masyarakat / instansi</p>
                </div>

                <div class="dash-metric-grid-4 dash-metric-grid--permohonan">
                    @foreach ($jadwalTiles as $index => $tile)
                        @php
                            $count = $jadwal_counts[$tile['key']] ?? 0;
                            $theme = ($index % 4) + 1;
                            $isIdle = $count === 0;
                        @endphp
                        <a href="{{ route('admin.pemangkasans.index', ['view' => $tile['key']]) }}"
                           class="dash-metric-tile dash-metric-tile--{{ $theme }} {{ $isIdle ? 'dash-metric-tile--idle' : '' }}">
                            <p class="dash-metric-tile__value">{{ $count }}</p>
                            <p class="dash-metric-tile__label">{{ $tile['label'] }}</p>
                            <p class="dash-metric-tile__meta">{{ $tile['desc'] }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </x-admin.dash-section>

    @include('admin.partials.operational_alerts')

    @unless ($canWrite ?? auth()->user()?->canWrite())
        <p class="mt-8 text-center text-xs text-gray-400">
            Klik kartu untuk melihat detail modul terkait.
        </p>
    @endunless
@endsection
