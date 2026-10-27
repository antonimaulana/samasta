@extends('layouts.public')

@php
    use App\Support\PublicRthStatisticsBuilder;
@endphp

@section('title', 'Statistik RTH Kota Batam')
@section('meta_description', 'Statistik Ruang Terbuka Hijau Kota Batam — luas, lokasi, capaian RTRW, dan sebaran per kategori serta wilayah.')

@push('styles')
    @vite('resources/css/rth-public.css')
@endpush

@section('content')
    <div class="rth-page mx-auto max-w-7xl px-4 pb-16 pt-6 sm:px-6 lg:px-8">
        <section class="rth-hero mb-8 px-6 py-8 sm:px-10 sm:py-10">
            <div class="relative">
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-100/90">SIMTAMAN · Disperakimtan Kota Batam</p>
                <h1 class="mt-2 text-3xl font-black tracking-tight sm:text-4xl">Statistik RTH Kota Batam</h1>
                <p class="mt-2 text-xs text-emerald-100/85">Diperbarui {{ PublicRthStatisticsBuilder::formatSnapshot($snapshotAt) }}</p>

                @if ($hasData)
                    <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        <div class="rth-kpi">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-100">Luas dikelola</p>
                            <p class="mt-1 text-2xl font-black sm:text-3xl">{{ PublicRthStatisticsBuilder::formatArea($totalLuasan) }}</p>
                            <p class="text-[11px] text-emerald-100/80">m²</p>
                        </div>
                        <div class="rth-kpi">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-100">Lokasi</p>
                            <p class="mt-1 text-2xl font-black sm:text-3xl">{{ PublicRthStatisticsBuilder::formatCount($totalTaman) }}</p>
                            <p class="text-[11px] text-emerald-100/80">unit</p>
                        </div>
                        <div class="rth-kpi">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-100">Capaian RTRW {{ $latestYear }}</p>
                            <p class="mt-1 text-2xl font-black sm:text-3xl">{{ PublicRthStatisticsBuilder::formatPercent($latestMetrics['persen_rtrw'] ?? null) }}</p>
                        </div>
                        <div class="rth-kpi">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-100">Terpelihara {{ $latestYear }}</p>
                            <p class="mt-1 text-2xl font-black sm:text-3xl">{{ PublicRthStatisticsBuilder::formatPercent($latestMetrics['persen_terpelihara'] ?? null) }}</p>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap gap-2">
                        <a href="{{ route('tamans.index') }}"
                           class="inline-flex items-center rounded-full bg-white px-4 py-2 text-sm font-bold text-emerald-800 shadow-sm hover:bg-emerald-50">
                            Daftar taman
                        </a>
                        <a href="{{ route('tamans.map') }}"
                           class="inline-flex items-center rounded-full border border-white/40 px-4 py-2 text-sm font-bold text-white hover:bg-white/10">
                            Peta RTH
                        </a>
                    </div>
                @endif
            </div>
        </section>

        @if (! $hasData)
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-6 py-10 text-center">
                <p class="text-lg font-semibold text-amber-900">Belum ada data taman</p>
                <p class="mt-2 text-sm text-amber-800">Statistik akan muncul setelah data RTH terinput di SIMTAMAN.</p>
            </div>
        @else
            <nav class="rth-nav mb-8" aria-label="Bagian statistik" data-rth-section-nav>
                @foreach ([
                    ['#rth-capaian', 'Capaian'],
                    ['#rth-kategori', 'Kategori'],
                    ['#rth-wilayah', 'Wilayah'],
                    ['#rth-tahun', 'Per tahun'],
                ] as [$href, $label])
                    <a href="{{ $href }}" class="rth-nav__link" data-rth-nav-link>{{ $label }}</a>
                @endforeach
            </nav>

            <section id="rth-capaian" class="mb-10 scroll-mt-28">
                <h2 class="text-xl font-black text-emerald-950 sm:text-2xl">Capaian {{ $latestYear }}</h2>

                <div class="mt-4 grid gap-4 lg:grid-cols-3">
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm lg:col-span-2">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="rounded-xl bg-emerald-50/80 p-4">
                                <p class="text-xs font-semibold text-emerald-800">Luas dikelola (A)</p>
                                <p class="mt-1 text-xl font-black text-emerald-950">{{ PublicRthStatisticsBuilder::formatArea($latestMetrics['luasan'] ?? null) }} m²</p>
                            </div>
                            <div class="rounded-xl bg-teal-50/80 p-4">
                                <p class="text-xs font-semibold text-teal-800">Luas terpelihara (E)</p>
                                <p class="mt-1 text-xl font-black text-teal-950">{{ PublicRthStatisticsBuilder::formatArea($latestMetrics['luasan_terpelihara'] ?? null) }} m²</p>
                            </div>
                            <div class="rounded-xl bg-gray-50 p-4">
                                <p class="text-xs font-semibold text-gray-600">Lokasi (B)</p>
                                <p class="mt-1 text-xl font-black text-gray-900">{{ PublicRthStatisticsBuilder::formatCount($latestMetrics['lokasi'] ?? null) }} unit</p>
                            </div>
                            <div class="rounded-xl bg-sky-50/80 p-4">
                                <p class="text-xs font-semibold text-sky-800">Acuan RTRW (C)</p>
                                <p class="mt-1 text-xl font-black text-sky-950">{{ PublicRthStatisticsBuilder::formatArea($rtrwLuasan) }} m²</p>
                            </div>
                        </div>

                        <div class="mt-6 space-y-5">
                            <div data-rth-animate-bar>
                                <div class="mb-2 flex items-end justify-between gap-2">
                                    <p class="text-sm font-semibold text-gray-700">Capaian RTRW (D)</p>
                                    <p class="text-base font-black text-emerald-800">{{ PublicRthStatisticsBuilder::formatPercent($latestMetrics['persen_rtrw'] ?? null, 2) }}</p>
                                </div>
                                <div class="rth-progress">
                                    <div class="rth-progress__fill bg-gradient-to-r from-emerald-500 to-teal-500"
                                         style="--rth-progress: {{ min((float) ($latestMetrics['persen_rtrw'] ?? 0), 100) }}%"></div>
                                </div>
                            </div>
                            <div data-rth-animate-bar>
                                <div class="mb-2 flex items-end justify-between gap-2">
                                    <p class="text-sm font-semibold text-gray-700">Rasio terpelihara (G)</p>
                                    <p class="text-base font-black text-teal-800">{{ PublicRthStatisticsBuilder::formatPercent($latestMetrics['persen_terpelihara'] ?? null, 2) }}</p>
                                </div>
                                <div class="rth-progress">
                                    <div class="rth-progress__fill bg-gradient-to-r from-teal-500 to-cyan-500"
                                         style="--rth-progress: {{ min((float) ($latestMetrics['persen_terpelihara'] ?? 0), 100) }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-green-100 bg-gradient-to-br from-green-50 to-emerald-50 p-6">
                        <p class="text-sm font-semibold text-green-800">Profil data lengkap</p>
                        <p class="mt-2 text-4xl font-black text-green-900">{{ $persenDataLengkap }}%</p>
                        <p class="mt-1 text-sm text-green-800/90">{{ PublicRthStatisticsBuilder::formatCount($jumlahDataLengkap) }} / {{ PublicRthStatisticsBuilder::formatCount($totalTaman) }} lokasi</p>
                        <div class="rth-progress mt-4" data-rth-animate-bar>
                            <div class="rth-progress__fill bg-green-600"
                                 style="--rth-progress: {{ $persenDataLengkap }}%"></div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="rth-kategori" class="mb-10 scroll-mt-28">
                <h2 class="text-xl font-black text-emerald-950 sm:text-2xl">Per kategori</h2>

                <div class="mt-4 grid gap-5 lg:grid-cols-5">
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm lg:col-span-2">
                        <canvas id="rthKategoriChart" class="mx-auto max-h-56" aria-label="Luasan RTH per kategori"></canvas>
                    </div>

                    <div class="space-y-3 lg:col-span-3" id="rth-kategori-list">
                        @foreach ($kategoriCards as $index => $card)
                            <button type="button"
                                    class="rth-kategori-card w-full text-left {{ $index === 0 ? 'is-active' : '' }}"
                                    data-rth-kategori-index="{{ $index }}">
                                <div class="flex items-stretch gap-0 sm:gap-4">
                                    <div class="flex w-16 flex-shrink-0 flex-col items-center justify-center bg-gradient-to-b {{ $card['accent']['bg'] }} py-4 text-white sm:w-20">
                                        <span class="text-2xl" aria-hidden="true">{{ $card['icon'] }}</span>
                                    </div>
                                    <div class="flex flex-1 flex-wrap items-center justify-between gap-3 p-4">
                                        <div>
                                            <p class="font-bold text-gray-900">{{ $card['label'] }}</p>
                                            <p class="mt-0.5 text-xs text-gray-500">{{ number_format($card['jumlah']) }} lokasi · {{ $card['persen_lokasi'] }}%</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-lg font-black text-emerald-900">{{ number_format($card['luasan'], 0, ',', '.') }}</p>
                                            <p class="text-xs text-gray-500">m² · {{ $card['persen_luas'] }}%</p>
                                        </div>
                                    </div>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="rth-wilayah" class="mb-10 scroll-mt-28">
                <h2 class="text-xl font-black text-emerald-950 sm:text-2xl">Per kecamatan</h2>

                <div class="mt-4 overflow-hidden rounded-2xl border border-teal-200 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-teal-50">
                                <tr>
                                    <th class="px-5 py-3 text-left font-semibold text-teal-900">Kecamatan</th>
                                    <th class="px-5 py-3 text-right font-semibold text-teal-900">Lokasi</th>
                                    <th class="px-5 py-3 text-right font-semibold text-teal-900">Luas (m²)</th>
                                    <th class="px-5 py-3 text-right font-semibold text-teal-900">%</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($kecamatanRows as $row)
                                    <tr class="transition hover:bg-teal-50/50">
                                        <td class="px-5 py-3 font-medium text-gray-900">{{ $row['kecamatan'] }}</td>
                                        <td class="px-5 py-3 text-right text-gray-700">{{ number_format($row['jumlah']) }}</td>
                                        <td class="px-5 py-3 text-right text-gray-700">{{ number_format($row['luasan'], 0, ',', '.') }}</td>
                                        <td class="px-5 py-3 text-right font-semibold text-teal-800">{{ $row['persen_luas'] }}%</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-5 py-8 text-center text-gray-500">Belum ada data.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($kelurahanGrouped !== [])
                        <details class="group border-t border-gray-100">
                            <summary class="cursor-pointer list-none px-6 py-4 text-sm font-semibold text-emerald-800 hover:bg-emerald-50/50 [&::-webkit-details-marker]:hidden">
                                <span class="inline-flex items-center gap-2">
                                    <span class="transition group-open:rotate-90">▶</span>
                                    Kelurahan ({{ count($kelurahanGrouped) }} kecamatan)
                                </span>
                            </summary>
                            <div class="space-y-3 px-6 pb-6">
                                @foreach ($kelurahanGrouped as $kecamatan)
                                    <div class="overflow-hidden rounded-xl border border-gray-200">
                                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 bg-gray-50 px-4 py-2.5">
                                            <p class="font-semibold text-gray-900">{{ $kecamatan['kecamatan'] }}</p>
                                            <p class="text-xs text-gray-600">{{ number_format($kecamatan['jumlah']) }} · {{ number_format($kecamatan['luasan'], 0, ',', '.') }} m²</p>
                                        </div>
                                        <table class="min-w-full text-xs">
                                            <tbody class="divide-y divide-gray-100">
                                                @foreach ($kecamatan['kelurahan'] as $kel)
                                                    <tr class="hover:bg-gray-50">
                                                        <td class="px-4 py-2 text-gray-800">{{ $kel['kelurahan'] }}</td>
                                                        <td class="px-4 py-2 text-right text-gray-600">{{ number_format($kel['jumlah']) }}</td>
                                                        <td class="px-4 py-2 text-right text-gray-600">{{ number_format($kel['luasan'], 0, ',', '.') }} m²</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endforeach
                            </div>
                        </details>
                    @endif
                </div>
            </section>

            <section id="rth-tahun" class="scroll-mt-28">
                <h2 class="text-xl font-black text-emerald-950 sm:text-2xl">Capaian per tahun</h2>
                <div class="mt-4">
                    @include('rth.partials.yearly-summary-public')
                </div>
            </section>
        @endif
    </div>
@endsection

@if ($hasData)
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const chartCanvas = document.getElementById('rthKategoriChart');
                let chartInstance = null;

                if (chartCanvas) {
                    chartInstance = new Chart(chartCanvas, {
                        type: 'doughnut',
                        data: {
                            labels: @json($chartKategoriLabels),
                            datasets: [{
                                data: @json($chartKategoriLuasan),
                                backgroundColor: @json($chartKategoriColors),
                                borderWidth: 2,
                                borderColor: '#ffffff',
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            cutout: '58%',
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label(context) {
                                            const value = context.raw ?? 0;
                                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            const pct = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                            return ` ${context.label}: ${value.toLocaleString('id-ID')} m² (${pct}%)`;
                                        },
                                    },
                                },
                            },
                        },
                    });
                }

                document.querySelectorAll('[data-rth-kategori-index]').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        document.querySelectorAll('.rth-kategori-card').forEach((el) => el.classList.remove('is-active'));
                        btn.classList.add('is-active');
                        const index = Number(btn.dataset.rthKategoriIndex);
                        if (chartInstance) {
                            chartInstance.setActiveElements([{ datasetIndex: 0, index }]);
                            chartInstance.tooltip.setActiveElements([{ datasetIndex: 0, index }], { x: 0, y: 0 });
                            chartInstance.update();
                        }
                    });
                });

                document.querySelectorAll('[data-rth-year-tab]').forEach((tab) => {
                    tab.addEventListener('click', () => {
                        const year = tab.dataset.rthYearTab;
                        document.querySelectorAll('[data-rth-year-tab]').forEach((t) => {
                            t.classList.toggle('is-active', t.dataset.rthYearTab === year);
                            t.setAttribute('aria-selected', t.dataset.rthYearTab === year ? 'true' : 'false');
                        });
                        document.querySelectorAll('[data-rth-year-panel]').forEach((panel) => {
                            panel.classList.toggle('is-active', panel.dataset.rthYearPanel === year);
                        });
                    });
                });

                const barObserver = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (!entry.isIntersecting) return;
                        entry.target.querySelectorAll('.rth-progress').forEach((bar) => bar.classList.add('is-animated'));
                        barObserver.unobserve(entry.target);
                    });
                }, { threshold: 0.2 });
                document.querySelectorAll('[data-rth-animate-bar]').forEach((el) => barObserver.observe(el));

                const sectionIds = ['rth-capaian', 'rth-kategori', 'rth-wilayah', 'rth-tahun'];
                const navLinks = document.querySelectorAll('[data-rth-nav-link]');
                const sectionObserver = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (!entry.isIntersecting) return;
                        const id = entry.target.id;
                        navLinks.forEach((link) => {
                            link.classList.toggle('is-active', link.getAttribute('href') === `#${id}`);
                        });
                    });
                }, { rootMargin: '-40% 0px -50% 0px', threshold: 0 });
                sectionIds.forEach((id) => {
                    const el = document.getElementById(id);
                    if (el) sectionObserver.observe(el);
                });
            });
        </script>
    @endpush
@endif
