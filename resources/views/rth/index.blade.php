@extends('layouts.public')

@php
    use App\Support\PublicRthStatisticsBuilder;
@endphp

@section('title', 'Statistik RTH Kota Batam')

@section('meta_description', 'Pedoman data resmi Ruang Terbuka Hijau (RTH) Kota Batam — luas, lokasi, capaian RTRW, distribusi kategori, dan rekap per wilayah administratif dari SIMTAMAN.')

@section('content')
    {{-- Hero --}}
    <section class="relative mb-10 overflow-hidden rounded-3xl border border-emerald-200 bg-gradient-to-br from-emerald-700 via-teal-700 to-green-800 px-6 py-10 text-white shadow-xl shadow-emerald-900/20 sm:px-10 sm:py-12">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-20 -left-10 h-56 w-56 rounded-full bg-lime-300/20 blur-3xl"></div>

        <div class="relative">
            <div class="flex flex-wrap items-center gap-3">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1 text-xs font-bold uppercase tracking-wider text-emerald-50 ring-1 ring-white/20">
                    <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    Pedoman Data Resmi
                </span>
                <span class="text-xs text-emerald-100/90">Disperakimtan Kota Batam · SIMTAMAN</span>
            </div>

            <h1 class="mt-4 max-w-3xl text-3xl font-black tracking-tight sm:text-4xl lg:text-5xl">
                Statistik Ruang Terbuka Hijau Kota Batam
            </h1>
            <p class="mt-4 max-w-2xl text-base leading-relaxed text-emerald-50/95 sm:text-lg">
                Data terpadu luas, lokasi, capaian RTRW, dan distribusi RTH per kategori &amp; wilayah —
                diselaraskan dengan Laporan Taman internal Dinas untuk keperluan publik dan eksternal.
            </p>

            <p class="mt-4 text-xs text-emerald-100/80">
                Snapshot data: {{ PublicRthStatisticsBuilder::formatSnapshot($snapshotAt) }}
            </p>

            @if ($hasData)
                <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-2xl bg-white/10 p-5 ring-1 ring-white/20 backdrop-blur-sm">
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-100">Total Luas Dikelola</p>
                        <p class="mt-2 text-3xl font-black">{{ PublicRthStatisticsBuilder::formatArea($totalLuasan) }}</p>
                        <p class="mt-1 text-xs text-emerald-100/80">meter persegi (m²)</p>
                    </div>
                    <div class="rounded-2xl bg-white/10 p-5 ring-1 ring-white/20 backdrop-blur-sm">
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-100">Total Lokasi</p>
                        <p class="mt-2 text-3xl font-black">{{ PublicRthStatisticsBuilder::formatCount($totalTaman) }}</p>
                        <p class="mt-1 text-xs text-emerald-100/80">unit RTH terdaftar</p>
                    </div>
                    <div class="rounded-2xl bg-white/10 p-5 ring-1 ring-white/20 backdrop-blur-sm">
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-100">Capaian vs RTRW {{ $latestYear }}</p>
                        <p class="mt-2 text-3xl font-black">{{ PublicRthStatisticsBuilder::formatPercent($latestMetrics['persen_rtrw'] ?? null) }}</p>
                        <p class="mt-1 text-xs text-emerald-100/80">luas dikelola ÷ RTRW</p>
                    </div>
                    <div class="rounded-2xl bg-white/10 p-5 ring-1 ring-white/20 backdrop-blur-sm">
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-100">Luas Terpelihara {{ $latestYear }}</p>
                        <p class="mt-2 text-3xl font-black">{{ PublicRthStatisticsBuilder::formatPercent($latestMetrics['persen_terpelihara'] ?? null) }}</p>
                        <p class="mt-1 text-xs text-emerald-100/80">dari total luas dikelola</p>
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
                ['#capaian-rtrw', 'Capaian RTRW'],
                ['#per-kategori', 'Per Kategori'],
                ['#per-wilayah', 'Per Wilayah'],
                ['#capaian-tahun', 'Capaian Tahun'],
            ] as [$href, $label])
                <a href="{{ $href }}"
                   class="rounded-full border border-emerald-200 bg-white px-4 py-2 text-sm font-semibold text-emerald-800 shadow-sm transition hover:border-emerald-300 hover:bg-emerald-50">
                    {{ $label }}
                </a>
            @endforeach
        </nav>

        {{-- Capaian RTRW highlight --}}
        <section id="capaian-rtrw" class="mb-10 scroll-mt-24">
            <div class="mb-5">
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-600">Ringkasan Capaian</p>
                <h2 class="mt-1 text-2xl font-bold text-emerald-950">Posisi RTH Dikelola Dinas vs RTRW</h2>
                <p class="mt-2 max-w-3xl text-sm text-gray-600">
                    Indikator utama tahun {{ $latestYear }} — sama dengan perhitungan pada menu Admin → Laporan Taman.
                </p>
            </div>

            <div class="grid gap-4 lg:grid-cols-3">
                <div class="rounded-2xl border border-emerald-100 bg-white p-6 shadow-sm lg:col-span-2">
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Luas RTH dikelola (A)</p>
                            <p class="mt-1 text-2xl font-black text-emerald-900">{{ PublicRthStatisticsBuilder::formatArea($latestMetrics['luasan'] ?? null) }} <span class="text-base font-semibold">m²</span></p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600">Luas terpelihara (E)</p>
                            <p class="mt-1 text-2xl font-black text-teal-900">{{ PublicRthStatisticsBuilder::formatArea($latestMetrics['luasan_terpelihara'] ?? null) }} <span class="text-base font-semibold">m²</span></p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600">Lokasi RTH (B / F)</p>
                            <p class="mt-1 text-2xl font-black text-gray-900">{{ PublicRthStatisticsBuilder::formatCount($latestMetrics['lokasi'] ?? null) }} <span class="text-base font-semibold">unit</span></p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600">Acuan RTRW (C)</p>
                            <p class="mt-1 text-2xl font-black text-sky-900">{{ PublicRthStatisticsBuilder::formatArea($rtrwLuasan) }} <span class="text-base font-semibold">m²</span></p>
                        </div>
                    </div>

                    <div class="mt-8 space-y-5">
                        <div>
                            <div class="mb-2 flex items-end justify-between gap-3">
                                <p class="text-sm font-semibold text-gray-700">Persentase vs RTRW (D)</p>
                                <p class="text-lg font-black text-emerald-800">{{ PublicRthStatisticsBuilder::formatPercent($latestMetrics['persen_rtrw'] ?? null, 2) }}</p>
                            </div>
                            <div class="h-3 overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-500 transition-all"
                                     style="width: {{ min((float) ($latestMetrics['persen_rtrw'] ?? 0), 100) }}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="mb-2 flex items-end justify-between gap-3">
                                <p class="text-sm font-semibold text-gray-700">Persentase luas terpelihara (G)</p>
                                <p class="text-lg font-black text-teal-800">{{ PublicRthStatisticsBuilder::formatPercent($latestMetrics['persen_terpelihara'] ?? null, 2) }}</p>
                            </div>
                            <div class="h-3 overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-gradient-to-r from-teal-500 to-cyan-500 transition-all"
                                     style="width: {{ min((float) ($latestMetrics['persen_terpelihara'] ?? 0), 100) }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-4">
                    <div class="flex-1 rounded-2xl border border-green-100 bg-gradient-to-br from-green-50 to-emerald-50 p-6">
                        <p class="text-sm font-semibold text-green-800">Kelengkapan Profil Data</p>
                        <p class="mt-2 text-4xl font-black text-green-900">{{ $persenDataLengkap }}%</p>
                        <p class="mt-2 text-sm text-green-800/80">
                            {{ PublicRthStatisticsBuilder::formatCount($jumlahDataLengkap) }} dari {{ PublicRthStatisticsBuilder::formatCount($totalTaman) }} lokasi memiliki profil lengkap.
                        </p>
                        <div class="mt-4 h-2 overflow-hidden rounded-full bg-green-200/60">
                            <div class="h-full rounded-full bg-green-600" style="width: {{ $persenDataLengkap }}%"></div>
                        </div>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Jelajahi data</p>
                        <div class="mt-3 flex flex-col gap-2">
                            <a href="{{ route('tamans.index') }}"
                               class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                                Daftar Taman →
                            </a>
                            <a href="{{ route('tamans.map') }}"
                               class="inline-flex items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-800 hover:bg-emerald-100">
                                🗺️ Peta Interaktif
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Per kategori --}}
        <section id="per-kategori" class="mb-10 scroll-mt-24">
            <div class="mb-5 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-emerald-600">Distribusi</p>
                    <h2 class="mt-1 text-2xl font-bold text-emerald-950">RTH per Kategori</h2>
                    <p class="mt-2 text-sm text-gray-600">Rekap jumlah lokasi dan luasan — selaras dengan tabel kategori di Laporan Taman.</p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-5">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm lg:col-span-2">
                    <h3 class="text-sm font-semibold text-gray-800">Komposisi Luasan</h3>
                    <div class="relative mx-auto mt-4 h-56 w-56 max-w-full">
                        <canvas id="rthKategoriChart" aria-label="Diagram komposisi luasan RTH per kategori"></canvas>
                    </div>
                    <p class="mt-3 text-center text-xs text-gray-500">Proporsi luasan per kategori RTH</p>
                </div>

                <div class="space-y-4 lg:col-span-3">
                    @foreach ($kategoriCards as $card)
                        <article class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:shadow-md">
                            <div class="flex flex-col sm:flex-row">
                                <div class="flex items-center gap-4 bg-gradient-to-r {{ $card['accent']['bg'] }} px-5 py-4 text-white sm:w-48 sm:flex-col sm:justify-center sm:text-center">
                                    <span class="text-3xl" aria-hidden="true">{{ $card['icon'] }}</span>
                                    <p class="font-bold leading-tight">{{ $card['label'] }}</p>
                                </div>
                                <div class="flex-1 p-5">
                                    <p class="text-sm text-gray-600">{{ $card['ringkas'] }}</p>
                                    <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
                                        <div>
                                            <p class="text-xs text-gray-500">Lokasi</p>
                                            <p class="text-xl font-black text-gray-900">{{ number_format($card['jumlah']) }}</p>
                                            <p class="text-xs text-emerald-700">{{ $card['persen_lokasi'] }}%</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">Luasan</p>
                                            <p class="text-xl font-black text-emerald-900">{{ number_format($card['luasan'], 0, ',', '.') }}</p>
                                            <p class="text-xs text-gray-500">m²</p>
                                        </div>
                                        <div class="col-span-2">
                                            <p class="mb-1 text-xs text-gray-500">Kontribusi luasan</p>
                                            <div class="h-2 overflow-hidden rounded-full bg-gray-100">
                                                <div class="h-full rounded-full bg-gradient-to-r {{ $card['accent']['bg'] }}"
                                                     style="width: {{ min($card['persen_luas'], 100) }}%"></div>
                                            </div>
                                            <p class="mt-1 text-right text-xs font-semibold text-emerald-800">{{ $card['persen_luas'] }}%</p>
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
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-600">Wilayah Administratif</p>
                <h2 class="mt-1 text-2xl font-bold text-emerald-950">RTH per Kecamatan &amp; Kelurahan</h2>
                <p class="mt-2 text-sm text-gray-600">
                    Agregasi {{ number_format($totalTaman) }} lokasi terdaftar, termasuk entri yang belum memiliki kelurahan (ditampilkan sebagai "Belum diset").
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
                                    <td class="px-5 py-3 text-right text-gray-700">{{ number_format($row['jumlah']) }}</td>
                                    <td class="px-5 py-3 text-right text-gray-700">{{ number_format($row['luasan'], 0, ',', '.') }}</td>
                                    <td class="px-5 py-3 text-right">
                                        <span class="inline-flex min-w-[3rem] justify-end font-semibold text-teal-800">{{ $row['persen_luas'] }}%</span>
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
                                Detail per Kelurahan ({{ count($kelurahanGrouped) }} kecamatan)
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

        {{-- Capaian per tahun --}}
        <section id="capaian-tahun" class="mb-10 scroll-mt-24">
            <div class="mb-5">
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-600">Tabel Capaian</p>
                <h2 class="mt-1 text-2xl font-bold text-emerald-950">Capaian RTH per Tahun (A–G)</h2>
                <p class="mt-2 text-sm text-gray-600">Format indikator sama dengan Laporan Taman admin — siap dijadikan acuan eksternal.</p>
            </div>

            @include('rth.partials.yearly-summary-public')
        </section>

        {{-- Footer note --}}
        <aside class="rounded-2xl border border-gray-200 bg-gray-50 px-6 py-5 text-sm text-gray-600">
            <p class="font-semibold text-gray-800">Tentang data ini</p>
            <ul class="mt-2 list-inside list-disc space-y-1 leading-relaxed">
                <li>Sumber: SIMTAMAN — basis data RTH Disperakimtan Kota Batam.</li>
                <li>Angka diselaraskan dengan menu Admin → Laporan Taman (tanpa filter).</li>
                <li>Data diperbarui otomatis dari registrasi taman; snapshot ditampilkan di bagian atas halaman.</li>
                <li>Untuk pertanyaan data, hubungi Disperakimtan Kota Batam.</li>
            </ul>
        </aside>
    @endif
@endsection

@if ($hasData)
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const canvas = document.getElementById('rthKategoriChart');
                if (!canvas) return;

                new Chart(canvas, {
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
                        cutout: '62%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                    padding: 12,
                                    font: { size: 11 },
                                },
                            },
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
            });
        </script>
    @endpush
@endif
