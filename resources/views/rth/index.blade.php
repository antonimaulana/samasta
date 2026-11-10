@extends('layouts.public')

@php
    use App\Support\PublicRthStatisticsBuilder;
@endphp

@section('title', 'Statistik Ruang Terbuka Hijau')

@section('meta_description', 'Statistik Ruang Terbuka Hijau (RTH) Kota Batam — luas, lokasi, capaian RTRW, distribusi kategori, dan rekap per wilayah administratif dari SIMTAMAN.')

@section('content')
    {{-- Hero --}}
    <section class="relative mb-8 overflow-hidden rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-700 via-teal-700 to-green-800 px-4 py-8 text-white shadow-xl shadow-emerald-900/20 sm:mb-10 sm:rounded-3xl sm:px-10 sm:py-12">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-20 -left-10 h-56 w-56 rounded-full bg-lime-300/20 blur-3xl"></div>

        <div class="relative">
            <div class="flex flex-wrap items-center gap-2 text-xs text-emerald-100/90">
                <span class="inline-flex items-center gap-1.5">
                    <svg class="h-4 w-4 shrink-0 text-emerald-50" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span class="font-semibold text-emerald-50">Disperkimtan Kota Batam</span>
                </span>
            </div>

            <h1 class="mt-4 text-2xl font-black tracking-tight sm:text-3xl lg:text-4xl">
                Statistik Ruang Terbuka Hijau
            </h1>
            <p class="mt-3 max-w-3xl text-xs leading-relaxed text-emerald-100/90 sm:text-sm">
                Berdasarkan data ruang terbuka hijau yang dikelola oleh Disperkimtan
                <span class="mx-1.5 text-emerald-200/80" aria-hidden="true">●</span>
                Diperbarui {{ PublicRthStatisticsBuilder::formatSnapshotDate($snapshotAt) }}
            </p>

            @if ($hasData)
                <div class="mt-6 grid grid-cols-2 gap-3 sm:mt-8 sm:gap-4 xl:grid-cols-4">
                    <div class="rounded-2xl bg-white/10 p-5 ring-1 ring-white/20 backdrop-blur-sm">
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-100">Total Lokasi</p>
                        <p class="rth-hero-stat mt-2 text-3xl font-black tabular-nums"
                           data-type="count"
                           data-value="{{ $totalTaman }}">0</p>
                        <p class="mt-1 text-xs text-emerald-100/80">unit RTH</p>
                    </div>
                    <div class="rounded-2xl bg-white/10 p-5 ring-1 ring-white/20 backdrop-blur-sm">
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-100">Total Luas</p>
                        <p class="rth-hero-stat mt-2 text-2xl font-black tabular-nums sm:text-3xl"
                           data-type="area"
                           data-value="{{ $totalLuasan }}">0</p>
                        <p class="mt-1 text-xs text-emerald-100/80">meter persegi (m²)</p>
                    </div>
                    <div class="rounded-2xl bg-white/10 p-5 ring-1 ring-white/20 backdrop-blur-sm">
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-100">Luas Terpelihara</p>
                        <p class="rth-hero-stat mt-2 text-3xl font-black tabular-nums"
                           data-type="percent"
                           data-value="{{ $latestMetrics['persen_terpelihara'] ?? 0 }}"
                           data-decimals="1">0</p>
                        <p class="mt-1 text-xs text-emerald-100/80">dari total luas dikelola</p>
                    </div>
                    <div class="rounded-2xl bg-white/10 p-5 ring-1 ring-white/20 backdrop-blur-sm">
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-100">Capaian RTH</p>
                        <p class="rth-hero-stat mt-2 text-3xl font-black tabular-nums"
                           data-type="percent"
                           data-value="{{ $latestMetrics['persen_rtrw'] ?? 0 }}"
                           data-decimals="1">0</p>
                        <p class="mt-1 text-xs text-emerald-100/80">dari luas RTH publik RTRW</p>
                    </div>
                </div>
            @endif
        </div>
    </section>

    @if (! $hasData)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-6 py-10 text-center">
            <p class="text-lg font-semibold text-amber-900">Data RTH belum tersedia</p>
            <p class="mt-2 text-sm text-amber-800">Statistik akan ditampilkan setelah taman terdaftar di SIMTAMAN.</p>
        </div>
    @else
        {{-- Quick nav --}}
        <nav class="mb-8 flex flex-wrap gap-2" aria-label="Navigasi bagian statistik">
            @foreach ([
                ['#capaian-tahun', 'Capaian Tahun'],
                ['#per-kategori', 'Per Kategori'],
                ['#per-wilayah', 'Per Wilayah'],
            ] as [$href, $label])
                <a href="{{ $href }}"
                   class="rounded-full border border-emerald-200 bg-white px-4 py-2 text-sm font-semibold text-emerald-800 shadow-sm transition hover:border-emerald-300 hover:bg-emerald-50">
                    {{ $label }}
                </a>
            @endforeach
        </nav>

        <section id="capaian-tahun" class="mb-10 scroll-mt-24">
            <div class="mb-5">
                <h2 class="text-xl font-bold text-emerald-950 sm:text-2xl">Capaian RTH per Tahun</h2>
            </div>

            @include('rth.partials.yearly-summary-public')
        </section>

        {{-- Per kategori --}}
        <section id="per-kategori" class="mb-10 scroll-mt-24">
            <div class="mb-5">
                <h2 class="text-xl font-bold text-emerald-950 sm:text-2xl">RTH per Kategori</h2>
            </div>

            <div class="grid gap-6 lg:grid-cols-5 lg:items-stretch">
                <div id="rth-kategori-charts"
                     class="grid gap-6 sm:grid-cols-2 sm:items-stretch lg:col-span-2 lg:grid-cols-1 lg:content-stretch">
                    <div class="rth-profile-chart-card flex min-h-[17rem] flex-col rounded-2xl border-2 border-emerald-200 bg-gradient-to-br from-white via-emerald-50/30 to-teal-50/40 p-5 shadow-lg shadow-emerald-900/10 sm:min-h-[18rem] sm:p-6 lg:min-h-0 lg:flex-1">
                        <h3 class="shrink-0 text-base font-bold text-emerald-950">Komposisi Luasan</h3>
                        <div class="rth-profile-chart-area rth-chart-glow relative mt-4 h-44 min-h-0 sm:h-48 lg:h-auto lg:min-h-[10rem] lg:flex-1">
                            <canvas id="rthKategoriChart"
                                    class="absolute inset-0 h-full w-full"
                                    aria-label="Diagram komposisi luasan RTH per kategori"></canvas>
                        </div>
                    </div>
                    <div class="rth-profile-chart-card flex min-h-[17rem] flex-col rounded-2xl border-2 border-emerald-200 bg-gradient-to-br from-white via-emerald-50/30 to-teal-50/40 p-5 shadow-lg shadow-emerald-900/10 sm:min-h-[18rem] sm:p-6 lg:min-h-0 lg:flex-1">
                        <h3 class="shrink-0 text-base font-bold text-emerald-950">Kelengkapan Profil RTH</h3>
                        <div class="rth-profile-chart-area rth-chart-glow relative mt-4 h-44 min-h-0 sm:h-48 lg:h-auto lg:min-h-[10rem] lg:flex-1">
                            <canvas id="rthKelengkapanChart"
                                    class="absolute inset-0 h-full w-full"
                                    aria-label="Diagram kelengkapan profil RTH"></canvas>
                        </div>
                    </div>
                </div>

                <div id="rth-kategori-list" class="space-y-4 lg:col-span-3">
                    @foreach ($kategoriCards as $card)
                        <article class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:shadow-md">
                            <div class="flex flex-col sm:flex-row">
                                <div class="flex items-center gap-4 bg-gradient-to-r {{ $card['accent']['bg'] }} px-5 py-4 text-white sm:w-48 sm:flex-col sm:justify-center sm:text-center">
                                    <span class="inline-flex shrink-0 items-center justify-center rounded-xl border border-white/30 bg-white/10 p-2.5 shadow-sm"
                                          aria-hidden="true">
                                        @include('rth.partials.kategori-icon', ['kategori' => $card['kategori'], 'class' => 'h-5 w-5'])
                                    </span>
                                    <p class="font-bold leading-tight">{{ $card['label'] }}</p>
                                </div>
                                <div class="flex-1 p-5">
                                    <p class="text-sm text-gray-600">{{ $card['ringkas'] }}</p>
                                    <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
                                        <div>
                                            <p class="text-xs text-gray-500">Lokasi</p>
                                            <p class="rth-anim-stat text-xl font-black text-gray-900 tabular-nums"
                                               data-type="count"
                                               data-value="{{ $card['jumlah'] }}">{{ number_format($card['jumlah']) }}</p>
                                            <p class="rth-anim-stat rth-glow-pct text-xs text-emerald-700 tabular-nums"
                                               data-type="percent"
                                               data-value="{{ $card['persen_lokasi'] }}"
                                               data-decimals="1">0%</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">Luasan</p>
                                            <p class="rth-anim-stat text-xl font-black text-emerald-900 tabular-nums"
                                               data-type="area"
                                               data-value="{{ $card['luasan'] }}">{{ \App\Support\TamanLuasan::formatTotal($card['luasan']) }}</p>
                                            <p class="text-xs text-gray-500">m²</p>
                                        </div>
                                        <div class="col-span-2">
                                            <p class="mb-1 text-xs text-gray-500">Kontribusi luasan</p>
                                            <div class="h-2 overflow-hidden rounded-full bg-gray-100">
                                                <div class="rth-kategori-bar rth-kategori-bar-glow h-full rounded-full bg-gradient-to-r {{ $card['accent']['bg'] }} transition-[width] duration-1000 ease-out"
                                                     data-width="{{ min($card['persen_luas'], 100) }}"
                                                     style="width: 0%"></div>
                                            </div>
                                            <p class="rth-anim-stat rth-glow-pct mt-1 text-right text-xs font-semibold text-emerald-800 tabular-nums"
                                               data-type="percent"
                                               data-value="{{ $card['persen_luas'] }}"
                                               data-decimals="1">0%</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Per wilayah --}}
        <section id="per-wilayah" class="mb-10 scroll-mt-24">
            <div class="mb-5">
                <h2 class="text-xl font-bold text-emerald-950 sm:text-2xl">RTH per Wilayah</h2>
                <p class="mt-1 text-sm text-gray-600">
                    <span class="rth-anim-stat font-semibold tabular-nums text-gray-800"
                          data-type="count"
                          data-value="{{ $totalTaman }}">{{ number_format($totalTaman) }}</span>
                    lokasi terdaftar
                </p>
            </div>

            <div class="overflow-hidden rounded-2xl border border-teal-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-teal-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold text-teal-900">Kecamatan</th>
                                <th class="px-5 py-3 text-right font-semibold text-teal-900">Jumlah Lokasi</th>
                                <th class="px-5 py-3 text-right font-semibold text-teal-900">Total Luas (m²)</th>
                                <th class="px-5 py-3 text-right font-semibold text-teal-900">% Luas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($kecamatanRows as $row)
                                <tr class="hover:bg-teal-50/40">
                                    <td class="px-5 py-3 font-medium text-gray-900">{{ $row['kecamatan'] }}</td>
                                    <td class="px-5 py-3 text-right text-gray-700">
                                        <span class="rth-anim-stat tabular-nums"
                                              data-type="count"
                                              data-value="{{ $row['jumlah'] }}">{{ number_format($row['jumlah']) }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-right text-gray-700">
                                        <span class="rth-anim-stat tabular-nums"
                                              data-type="area"
                                              data-value="{{ $row['luasan'] }}">{{ \App\Support\TamanLuasan::formatTotal($row['luasan']) }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <span class="rth-anim-stat rth-glow-pct inline-flex min-w-[3rem] justify-end text-teal-800 tabular-nums"
                                              data-type="percent"
                                              data-value="{{ $row['persen_luas'] }}"
                                              data-decimals="1">{{ number_format($row['persen_luas'], 1, ',', '.') }}%</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-8 text-center text-gray-500">Belum ada data wilayah.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($kelurahanGrouped !== [])
                    <details class="border-t border-gray-100 group">
                        <summary class="cursor-pointer list-none px-6 py-4 text-sm font-semibold text-emerald-800 hover:bg-emerald-50/50 [&::-webkit-details-marker]:hidden">
                            <span class="inline-flex items-center gap-2">
                                <span class="transition group-open:rotate-90">▶</span>
                                Detail per Kelurahan
                            </span>
                        </summary>
                        <div class="space-y-4 px-6 pb-6">
                            @foreach ($kelurahanGrouped as $kecamatan)
                                <div class="overflow-hidden rounded-xl border border-gray-200">
                                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 bg-gray-50 px-4 py-3">
                                        <p class="font-semibold text-gray-900">{{ $kecamatan['kecamatan'] }}</p>
                                        <p class="text-xs text-gray-600">
                                            <span class="rth-anim-stat tabular-nums"
                                                  data-type="count"
                                                  data-value="{{ $kecamatan['jumlah'] }}">{{ number_format($kecamatan['jumlah']) }}</span>
                                            lokasi ·
                                            <span class="rth-anim-stat tabular-nums"
                                                  data-type="area"
                                                  data-value="{{ $kecamatan['luasan'] }}">{{ \App\Support\TamanLuasan::formatTotal($kecamatan['luasan']) }}</span>
                                            m²
                                        </p>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full text-xs">
                                            <tbody class="divide-y divide-gray-100">
                                                @foreach ($kecamatan['kelurahan'] as $kel)
                                                    <tr class="hover:bg-gray-50">
                                                        <td class="px-4 py-2.5 text-gray-800">{{ $kel['kelurahan'] }}</td>
                                                        <td class="px-4 py-2.5 text-right text-gray-600">
                                                            <span class="rth-anim-stat tabular-nums"
                                                                  data-type="count"
                                                                  data-value="{{ $kel['jumlah'] }}">{{ number_format($kel['jumlah']) }}</span>
                                                        </td>
                                                        <td class="px-4 py-2.5 text-right text-gray-600">
                                                            <span class="rth-anim-stat tabular-nums"
                                                                  data-type="area"
                                                                  data-value="{{ $kel['luasan'] }}">{{ \App\Support\TamanLuasan::formatTotal($kel['luasan']) }}</span>
                                                            m²
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </details>
                @endif
            </div>
        </section>

    @endif
@endsection

@if ($hasData)
    @push('styles')
        <style>
            @keyframes rth-chart-glow-pulse {
                0%, 100% { opacity: 0.35; transform: scale(0.94); }
                50% { opacity: 0.9; transform: scale(1.06); }
            }

            @keyframes rth-pct-glow {
                0%, 100% {
                    text-shadow: 0 0 6px rgba(16, 185, 129, 0.2), 0 0 12px rgba(45, 212, 191, 0.15);
                }
                50% {
                    text-shadow: 0 0 10px rgba(16, 185, 129, 0.45), 0 0 22px rgba(45, 212, 191, 0.35), 0 0 32px rgba(20, 184, 166, 0.2);
                }
            }

            @keyframes rth-bar-glow-pulse {
                0%, 100% { filter: drop-shadow(0 0 4px rgba(16, 185, 129, 0.35)); }
                50% { filter: drop-shadow(0 0 10px rgba(45, 212, 191, 0.65)) drop-shadow(0 0 18px rgba(16, 185, 129, 0.35)); }
            }

            .rth-chart-glow::before,
            .rth-chart-glow::after {
                pointer-events: none;
                position: absolute;
                border-radius: 50%;
                content: '';
                z-index: 0;
            }

            .rth-chart-glow::before {
                inset: 14%;
                background: radial-gradient(circle, rgba(52, 211, 153, 0.55) 0%, rgba(20, 184, 166, 0.2) 42%, transparent 72%);
                animation: rth-chart-glow-pulse 3.8s ease-in-out infinite;
            }

            .rth-chart-glow::after {
                inset: 26%;
                background: radial-gradient(circle, rgba(255, 255, 255, 0.85) 0%, rgba(167, 243, 208, 0.25) 40%, transparent 68%);
                animation: rth-chart-glow-pulse 3.8s ease-in-out infinite reverse;
                opacity: 0.55;
            }

            .rth-chart-glow canvas {
                position: relative;
                z-index: 1;
            }

            .rth-glow-pct {
                animation: rth-pct-glow 2.8s ease-in-out infinite;
            }

            .rth-kategori-bar-glow.rth-kategori-bar--active {
                animation: rth-bar-glow-pulse 2.6s ease-in-out infinite;
            }

            @media (prefers-reduced-motion: reduce) {
                .rth-chart-glow::before,
                .rth-chart-glow::after,
                .rth-glow-pct,
                .rth-kategori-bar-glow.rth-kategori-bar--active {
                    animation: none;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const easeOutCubic = (t) => 1 - Math.pow(1 - t, 3);
                const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                const formatCountId = (n) => Math.round(n).toLocaleString('id-ID');
                const formatAreaId = (n) => Math.round(n).toLocaleString('id-ID');
                const formatPercentId = (n, decimals = 1) => n.toLocaleString('id-ID', {
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals,
                }) + '%';

                const formatStatValue = (type, value, decimals) => {
                    if (type === 'count') {
                        return formatCountId(value);
                    }
                    if (type === 'area') {
                        return formatAreaId(value);
                    }
                    if (type === 'percent') {
                        return formatPercentId(value, decimals);
                    }

                    return String(value);
                };

                const animateRthStat = (el, { duration = 1400, immediate = false } = {}) => {
                    if (el.dataset.rthAnimated === '1') {
                        return;
                    }
                    el.dataset.rthAnimated = '1';

                    const target = parseFloat(el.dataset.value || '0');
                    const type = el.dataset.type || 'count';
                    const decimals = parseInt(el.dataset.decimals || (type === 'percent' ? '1' : '0'), 10);

                    if (Number.isNaN(target)) {
                        return;
                    }

                    if (prefersReducedMotion) {
                        el.textContent = formatStatValue(type, target, decimals);
                        return;
                    }

                    if (! immediate) {
                        el.textContent = formatStatValue(type, 0, decimals);
                    }

                    const start = performance.now();

                    const frame = (now) => {
                        const progress = Math.min(1, (now - start) / duration);
                        const current = target * easeOutCubic(progress);
                        el.textContent = formatStatValue(type, current, decimals);

                        if (progress < 1) {
                            requestAnimationFrame(frame);
                        }
                    };

                    requestAnimationFrame(frame);
                };

                document.querySelectorAll('.rth-hero-stat').forEach((el) => {
                    animateRthStat(el, { immediate: true });
                });

                const revealObserver = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (! entry.isIntersecting) {
                            return;
                        }

                        entry.target.querySelectorAll('.rth-anim-stat').forEach((el) => {
                            animateRthStat(el);
                        });

                        entry.target.querySelectorAll('.rth-kategori-bar').forEach((bar) => {
                            bar.classList.add('rth-kategori-bar--active');
                            bar.style.width = `${bar.dataset.width || 0}%`;
                        });

                        revealObserver.unobserve(entry.target);
                    });
                }, { threshold: 0.12, rootMargin: '0px 0px -4% 0px' });

                document.querySelectorAll('.rth-anim-stat').forEach((el) => {
                    if (! el.closest('#per-kategori, #per-wilayah, #capaian-tahun')) {
                        animateRthStat(el);
                        return;
                    }
                });

                ['#capaian-tahun', '#per-kategori', '#per-wilayah'].forEach((selector) => {
                    const section = document.querySelector(selector);
                    if (section) {
                        revealObserver.observe(section);
                    }
                });

                const doughnutGlowPlugin = {
                    id: 'rthDoughnutGlow',
                    beforeDatasetDraw(chart, args) {
                        const { ctx } = chart;
                        ctx.save();
                        ctx.shadowColor = 'rgba(16, 185, 129, 0.45)';
                        ctx.shadowBlur = 18;
                        ctx.shadowOffsetX = 0;
                        ctx.shadowOffsetY = 0;
                    },
                    afterDatasetDraw(chart) {
                        chart.ctx.restore();
                    },
                };

                const doughnutDefaults = {
                    type: 'doughnut',
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '58%',
                        animation: {
                            animateRotate: true,
                            animateScale: true,
                            duration: 1400,
                            easing: 'easeOutQuart',
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                    padding: 12,
                                    font: { size: 11 },
                                },
                            },
                        },
                    },
                };

                let kategoriChart = null;
                const kategoriCanvas = document.getElementById('rthKategoriChart');
                if (kategoriCanvas) {
                    kategoriChart = new Chart(kategoriCanvas, {
                        ...doughnutDefaults,
                        plugins: [doughnutGlowPlugin],
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
                            ...doughnutDefaults.options,
                            plugins: {
                                ...doughnutDefaults.options.plugins,
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

                const syncKelengkapanColumnHeight = () => {
                    const leftCol = document.getElementById('rth-kategori-charts');
                    const list = document.getElementById('rth-kategori-list');
                    if (!leftCol || !list) {
                        return;
                    }

                    if (window.matchMedia('(min-width: 1024px)').matches) {
                        leftCol.style.minHeight = `${list.offsetHeight}px`;
                    } else {
                        leftCol.style.minHeight = '';
                    }
                };

                const syncProfileChartCardHeights = () => {
                    const cards = document.querySelectorAll('.rth-profile-chart-card');
                    if (cards.length < 2) {
                        return;
                    }

                    cards.forEach((card) => {
                        card.style.minHeight = '';
                    });

                    if (window.matchMedia('(min-width: 1024px)').matches) {
                        return;
                    }

                    let maxHeight = 0;
                    cards.forEach((card) => {
                        maxHeight = Math.max(maxHeight, card.offsetHeight);
                    });
                    const uniform = `${maxHeight}px`;
                    cards.forEach((card) => {
                        card.style.minHeight = uniform;
                    });
                };

                let kelengkapanChart = null;
                const kelengkapanCanvas = document.getElementById('rthKelengkapanChart');
                if (kelengkapanCanvas) {
                    const lengkap = @json($jumlahDataLengkap);
                    const belum = @json($jumlahBelumLengkap);
                    kelengkapanChart = new Chart(kelengkapanCanvas, {
                        ...doughnutDefaults,
                        plugins: [doughnutGlowPlugin],
                        data: {
                            labels: ['Profil lengkap', 'Belum lengkap'],
                            datasets: [{
                                data: [lengkap, belum],
                                backgroundColor: ['rgba(22, 163, 74, 0.85)', 'rgba(245, 158, 11, 0.85)'],
                                borderWidth: 2,
                                borderColor: '#ffffff',
                            }],
                        },
                        options: {
                            ...doughnutDefaults.options,
                            plugins: {
                                ...doughnutDefaults.options.plugins,
                                tooltip: {
                                    callbacks: {
                                        label(context) {
                                            const value = context.raw ?? 0;
                                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            const pct = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                            return ` ${context.label}: ${value.toLocaleString('id-ID')} lokasi (${pct}%)`;
                                        },
                                    },
                                },
                            },
                        },
                    });
                }

                const reflowKelengkapanLayout = () => {
                    syncKelengkapanColumnHeight();
                    syncProfileChartCardHeights();
                    kategoriChart?.resize();
                    kelengkapanChart?.resize();
                };

                reflowKelengkapanLayout();
                window.addEventListener('resize', reflowKelengkapanLayout);
                if (document.fonts?.ready) {
                    document.fonts.ready.then(reflowKelengkapanLayout);
                }
            });
        </script>
    @endpush
@endif
