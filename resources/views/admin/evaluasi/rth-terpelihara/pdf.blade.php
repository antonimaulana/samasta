<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Evaluasi RTH Terpelihara</title>
    @include('admin.partials.pdf_styles')
</head>
<body>
    <h1>Evaluasi RTH Terpelihara</h1>
    <p class="meta">
        Dinas Perumahan, Kawasan Permukiman dan Pertamanan Kota Batam<br>
        {{ $labelSnapshot }}<br>
        {{ $labelKriteria }}
        @if ($kategori) · Kategori: {{ $kategori }} @endif
        @if ($kecamatan_id)
            · Kecamatan:
            {{ $daftarKecamatan->firstWhere('id', (int) $kecamatan_id)?->nama ?? $kecamatan_id }}
        @endif
    </p>

    <table class="kpi-table">
        <tr>
            <td class="kpi">
                <div class="kpi-label">Lokasi Terpelihara</div>
                <div class="kpi-value">{{ number_format($lokasiTerpelihara) }} / {{ number_format($totalLokasi) }}</div>
                <div class="kpi-label">{{ $persenLokasi }}%</div>
            </td>
            <td class="kpi">
                <div class="kpi-label">Luasan Terpelihara</div>
                <div class="kpi-value">{{ number_format($luasanTerpelihara, 0, ',', '.') }} m²</div>
                <div class="kpi-label">{{ $persenLuasan }}%</div>
            </td>
            <td class="kpi">
                <div class="kpi-label">Perlu Perhatian</div>
                <div class="kpi-value">{{ number_format($lokasiBelumTerpelihara) }}</div>
                <div class="kpi-label">{{ number_format($luasanBelumTerpelihara, 0, ',', '.') }} m²</div>
            </td>
            <td class="kpi">
                <div class="kpi-label">vs RTRW Publik</div>
                <div class="kpi-value">{{ $persenRtrw }}%</div>
            </td>
        </tr>
    </table>

    <h2>Rekap per Kategori</h2>
    <table>
        <thead>
            <tr>
                <th>Kategori</th>
                <th class="num">Lokasi</th>
                <th class="num">Terpelihara</th>
                <th class="num">Luasan (m²)</th>
                <th class="num">%</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rekapPerKategori as $row)
                <tr>
                    <td>{{ $row['label'] }}</td>
                    <td class="num">{{ number_format($row['total_lokasi']) }}</td>
                    <td class="num">{{ number_format($row['lokasi_terpelihara']) }}</td>
                    <td class="num">{{ number_format($row['total_luasan'], 0, ',', '.') }}</td>
                    <td class="num">{{ $row['persen_lokasi'] }}%</td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Rekap per Kecamatan</h2>
    <table>
        <thead>
            <tr>
                <th>Kecamatan</th>
                <th class="num">Lokasi</th>
                <th class="num">Terpelihara</th>
                <th class="num">Luasan (m²)</th>
                <th class="num">%</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rekapPerKecamatan as $row)
                <tr>
                    <td>{{ $row['label'] }}</td>
                    <td class="num">{{ number_format($row['total_lokasi']) }}</td>
                    <td class="num">{{ number_format($row['lokasi_terpelihara']) }}</td>
                    <td class="num">{{ number_format($row['total_luasan'], 0, ',', '.') }}</td>
                    <td class="num">{{ $row['persen_lokasi'] }}%</td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Lokasi Perlu Perhatian</h2>
    <table>
        <thead>
            <tr>
                <th>Taman</th>
                <th>Kategori</th>
                <th>Wilayah</th>
                <th class="num">Luasan (m²)</th>
                <th>Pemeliharaan Terakhir</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($belumTerpelihara as $row)
                <tr>
                    <td>{{ $row['nama'] }}</td>
                    <td>{{ $row['kategori'] }}</td>
                    <td>{{ $row['wilayah'] }}</td>
                    <td class="num">{{ number_format($row['luasan'], 0, ',', '.') }}</td>
                    <td>
                        @if ($row['latest_maintenance'])
                            {{ $row['latest_maintenance']->timezone(config('app.timezone'))->format('d/m/Y') }}
                            ({{ $row['hari_sejak'] }} hari lalu)
                        @else
                            Belum pernah
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty">Semua lokasi terpelihara.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
