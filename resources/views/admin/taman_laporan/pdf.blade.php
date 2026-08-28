<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Ruang Terbuka Hijau (RTH)</title>
    @include('admin.partials.pdf_styles')
</head>
<body>
    @php
        $formatRthValue = function (array $row, int $year): string {
            $value = $row['values'][$year] ?? 0;

            return match ($row['format']) {
                'area', 'count' => number_format((float) $value, 0, ',', '.'),
                'percent' => number_format((float) $value, 2, ',', '.').'%',
                default => (string) $value,
            };
        };

        $filterParts = array_filter([
            $search ? 'Pencarian: '.$search : null,
            $kategori ? 'Kategori: '.$kategori : null,
            $statusData ? 'Status: '.($statusData === \App\Models\Taman::STATUS_DATA_LENGKAP ? 'Lengkap' : 'Belum Lengkap') : null,
        ]);
    @endphp

    <h1>Laporan Ruang Terbuka Hijau (RTH)</h1>
    <p class="meta">
        Dinas Perumahan, Kawasan Permukiman dan Pertamanan Kota Batam<br>
        Dicetak: <strong>{{ $generatedAt->translatedFormat('d F Y H:i') }} WIB</strong>
        · Total: <strong>{{ number_format($totalTaman) }} RTH</strong>
        · Luasan: <strong>{{ number_format($totalLuasan, 0, ',', '.') }} m²</strong>
        @if ($filterParts)
            <br>Filter: <strong>{{ implode(' · ', $filterParts) }}</strong>
        @endif
    </p>

    <table class="kpi-table">
        <tr>
            <td class="kpi">
                <div class="kpi-label">Total RTH</div>
                <div class="kpi-value">{{ number_format($totalTaman) }}</div>
            </td>
            <td class="kpi">
                <div class="kpi-label">Total Luasan (m²)</div>
                <div class="kpi-value">{{ number_format($totalLuasan, 0, ',', '.') }}</div>
            </td>
            <td class="kpi">
                <div class="kpi-label">Data Lengkap</div>
                <div class="kpi-value">{{ number_format($rekapStatusData['Lengkap'] ?? 0) }}</div>
            </td>
            <td class="kpi">
                <div class="kpi-label">Belum Lengkap</div>
                <div class="kpi-value">{{ number_format($rekapStatusData['Belum Lengkap'] ?? 0) }}</div>
            </td>
        </tr>
    </table>

    <h2>Capaian Ruang Terbuka Hijau (RTH) per Tahun</h2>
    <table>
        <thead>
            <tr>
                <th style="width:42%">Keterangan</th>
                @foreach ($rthYearlySummary['years'] as $year)
                    <th class="num">{{ $year }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($rthYearlySummary['rows'] as $row)
                <tr>
                    <td>{{ $row['label'] }}</td>
                    @foreach ($rthYearlySummary['years'] as $year)
                        <td class="num">{{ $formatRthValue($row, $year) }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Rekap Ruang Terbuka Hijau (RTH) per Kategori</h2>
    <table>
        <thead>
            <tr>
                <th>Kategori</th>
                <th style="width:18%" class="num">Jumlah</th>
                <th style="width:22%" class="num">Luasan (m²)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rekapKategori as $row)
                <tr>
                    <td>{{ $row['kategori'] }}</td>
                    <td class="num">{{ number_format($row['jumlah']) }}</td>
                    <td class="num">{{ number_format($row['luasan'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="empty">Tidak ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Rekap Ruang Terbuka Hijau (RTH) per Wilayah — Per Kecamatan</h2>
    <table>
        <thead>
            <tr>
                <th>Kecamatan</th>
                <th style="width:18%" class="num">Jumlah</th>
                <th style="width:22%" class="num">Luasan (m²)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rekapKecamatan as $row)
                <tr>
                    <td>{{ $row['kecamatan'] }}</td>
                    <td class="num">{{ number_format($row['jumlah']) }}</td>
                    <td class="num">{{ number_format($row['luasan'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="empty">Tidak ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Rekap Ruang Terbuka Hijau (RTH) per Wilayah — Per Kelurahan</h2>
    <table>
        <thead>
            <tr>
                <th style="width:22%">Kecamatan</th>
                <th>Kelurahan</th>
                <th style="width:14%" class="num">Jumlah</th>
                <th style="width:18%" class="num">Luasan (m²)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rekapKelurahanPerKecamatan as $kecamatanGroup)
                @foreach ($kecamatanGroup['kelurahan'] as $index => $row)
                    <tr>
                        @if ($index === 0)
                            <td rowspan="{{ $kecamatanGroup['kelurahan']->count() }}"><strong>{{ $kecamatanGroup['kecamatan'] }}</strong></td>
                        @endif
                        <td>{{ $row['kelurahan'] }}</td>
                        <td class="num">{{ number_format($row['jumlah']) }}</td>
                        <td class="num">{{ number_format($row['luasan'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="4" class="empty">Tidak ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($tamansPerKategori->flatten()->isNotEmpty())
        <h2>Daftar Ruang Terbuka Hijau (RTH) per Kategori</h2>

        @foreach ($tamansPerKategori as $kategori => $items)
            @if ($items->isNotEmpty())
                <h2>{{ $kategori }} ({{ number_format($items->count()) }} lokasi · {{ number_format($items->sum('luasan'), 0, ',', '.') }} m²)</h2>
                <table>
                    <thead>
                        <tr>
                            <th style="width:4%">No</th>
                            <th style="width:18%">Nama RTH</th>
                            <th style="width:16%">Wilayah</th>
                            <th style="width:10%" class="num">Luasan (m²)</th>
                            <th>Alamat</th>
                            <th style="width:14%">Koordinat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $index => $taman)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><strong>{{ $taman->nama_taman }}</strong></td>
                                <td>
                                    @if ($taman->kelurahan)
                                        {{ $taman->kelurahan->kecamatan->nama ?? '—' }}<br>
                                        <span style="color:#6b7280;">{{ $taman->kelurahan->nama }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="num">{{ number_format($taman->luasan, 0, ',', '.') }}</td>
                                <td>{{ $taman->alamat ?? '—' }}</td>
                                <td>
                                    @if ($taman->latitude && $taman->longitude)
                                        {{ $taman->latitude }}, {{ $taman->longitude }}
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @endforeach
    @else
        <h2>Daftar Ruang Terbuka Hijau (RTH) per Kategori</h2>
        <p class="empty">Tidak ada data RTH untuk filter yang dipilih.</p>
    @endif

    <div class="footer">
        Dicetak otomatis dari Sistem {{ config('app.name') }} · {{ $generatedAt->format('d/m/Y H:i') }} WIB
    </div>
</body>
</html>
