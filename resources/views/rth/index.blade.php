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

            <div class="grid gap-6 lg:grid-cols-5">
                <div class="space-y-6 lg:col-span-2">
                    <div class="rounded-2xl border-2 border-emerald-200 bg-gradient-to-br from-white via-emerald-50/30 to-teal-50/40 p-6 shadow-lg shadow-emerald-900/10">
                        <h3 class="text-base font-bold text-emerald-950">Komposisi Luasan</h3>
                        <div class="relative mx-auto mt-5 h-72 w-72 max-w-full sm:h-80 sm:w-80">
                            <canvas id="rthKategoriChart" aria-label="Diagram komposisi luasan RTH per kategori"></canvas>
                        </div>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                        <h3 class="text-sm font-semibold text-gray-800">Kelengkapan Profil Data</h3>
                        <div class="relative mx-auto mt-4 h-52 w-52 max-w-full">
                            <canvas id="rthKelengkapanChart" aria-label="Diagram kelengkapan profil data RTH"></canvas>
                        </div>
                    </div>
                </div>

                <div class="space-y-4 lg:col-span-3">
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
                                            <p class="text-xl font-black text-gray-900">{{ number_format($card['jumlah']) }}</p>
                                            <p class="rth-kategori-pct text-xs text-emerald-700 tabular-nums"
                                               data-value="{{ $card['persen_lokasi'] }}">0%</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">Luasan</p>
                                            <p class="text-xl font-black text-emerald-900">{{ number_format($card['luasan'], 0, ',', '.') }}</p>
                                            <p class="text-xs text-gray-500">m²</p>
                                        </div>
                                        <div class="col-span-2">
                                            <p class="mb-1 text-xs text-gray-500">Kontribusi luasan</p>
                                            <div class="h-2 overflow-hidden rounded-full bg-gray-100">
                                                <div class="rth-kategori-bar h-full rounded-full bg-gradient-to-r {{ $card['accent']['bg'] }} transition-[width] duration-1000 ease-out"
                                                     data-width="{{ min($card['persen_luas'], 100) }}"
                                                     style="width: 0%"></div>
                                            </div>
                                            <p class="rth-kategori-pct mt-1 text-right text-xs font-semibold text-emerald-800 tabular-nums"
                                               data-value="{{ $card['persen_luas'] }}">0%</p>
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
                <p class="mt-1 text-sm text-gray-600">{{ number_format($totalTaman) }} lokasi terdaftar</p>
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
                                    <td class="px-5 py-3 text-right text-gray-700">{{ number_format($row['jumlah']) }}</td>
                                    <td class="px-5 py-3 text-right text-gray-700">{{ number_format($row['luasan'], 0, ',', '.') }}</td>
                                    <td class="px-5 py-3 text-right">
                                        <span class="inline-flex min-w-[3rem] justify-end text-teal-800">{{ $row['persen_luas'] }}%</span>
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
                                            {{ number_format($kecamatan['jumlah']) }} lokasi · {{ number_format($kecamatan['luasan'], 0, ',', '.') }} m²
                                        </p>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full text-xs">
                                            <tbody class="divide-y divide-gray-100">
                                                @foreach ($kecamatan['kelurahan'] as $kel)
                                                    <tr class="hover:bg-gray-50">
                                                        <td class="px-4 py-2.5 text-gray-800">{{ $kel['kelurahan'] }}</td>
                                                        <td class="px-4 py-2.5 text-right text-gray-600">{{ number_format($kel['jumlah']) }}</td>
                                                        <td class="px-4 py-2.5 text-right text-gray-600">{{ number_format($kel['luasan'], 0, ',', '.') }} m²</td>
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
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const easeOutCubic = (t) => 1 - Math.pow(1 - t, 3);

                const formatCountId = (n) => Math.round(n).toLocaleString('id-ID');
                const formatAreaId = (n) => Math.round(n).toLocaleString('id-ID');
                const formatPercentId = (n, decimals = 1) => n.toLocaleString('id-ID', {
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals,
                }) + '%';

                document.querySelectorAll('.rth-hero-stat').forEach((el) => {
                    const target = parseFloat(el.dataset.value || '0');
                    const type = el.dataset.type || 'count';
                    const decimals = parseInt(el.dataset.decimals || '0', 10);
                    const duration = 1400;
                    const start = performance.now();

                    const frame = (now) => {
                        const progress = Math.min(1, (now - start) / duration);
                        const current = target * easeOutCubic(progress);

                        if (type === 'count') {
                            el.textContent = formatCountId(current);
                        } else if (type === 'area') {
                            el.textContent = formatAreaId(current);
                        } else {
                            el.textContent = formatPercentId(current, decimals);
                        }

                        if (progress < 1) {
                            requestAnimationFrame(frame);
                        }
                    };

                    requestAnimationFrame(frame);
                });

                document.querySelectorAll('.rth-kategori-pct').forEach((el) => {
                    const target = parseFloat(el.dataset.value || '0');
                    const duration = 1200;
                    const start = performance.now();

                    const frame = (now) => {
                        const progress = Math.min(1, (now - start) / duration);
                        const current = target * easeOutCubic(progress);
                        el.textContent = current.toLocaleString('id-ID', {
                            minimumFractionDigits: 1,
                            maximumFractionDigits: 1,
                        }) + '%';

                        if (progress < 1) {
                            requestAnimationFrame(frame);
                        }
                    };

                    requestAnimationFrame(frame);
                });

                requestAnimationFrame(() => {
                    document.querySelectorAll('.rth-kategori-bar').forEach((bar) => {
                        bar.style.width = `${bar.dataset.width || 0}%`;
                    });
                });

                const doughnutDefaults = {
                    type: 'doughnut',
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
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

                const kategoriCanvas = document.getElementById('rthKategoriChart');
                if (kategoriCanvas) {
                    new Chart(kategoriCanvas, {
                        ...doughnutDefaults,
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

                const kelengkapanCanvas = document.getElementById('rthKelengkapanChart');
                if (kelengkapanCanvas) {
                    const lengkap = @json($jumlahDataLengkap);
                    const belum = @json($jumlahBelumLengkap);
                    new Chart(kelengkapanCanvas, {
                        ...doughnutDefaults,
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
            });
        </script>
    @endpush
@endif
