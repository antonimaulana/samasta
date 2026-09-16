<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Evaluasi Pemeliharaan per Taman</title>
    @include('admin.partials.pdf_styles')
</head>
<body>
    <h1>Evaluasi Pemeliharaan per Taman</h1>
    <p class="meta">
        Dinas Perumahan, Kawasan Permukiman dan Pertamanan Kota Batam<br>
        Periode: <strong>{{ $labelPeriode }}</strong>
        @if ($tim) · Tim: {{ $tim }} @endif
        @if ($kategori) · Kategori: {{ $kategori }} @endif
    </p>

    <table class="kpi-table">
        <tr>
            <td class="kpi">
                <div class="kpi-label">Total Kegiatan</div>
                <div class="kpi-value">{{ number_format($totalKegiatan) }}</div>
            </td>
            <td class="kpi">
                <div class="kpi-label">Taman Terlayani</div>
                <div class="kpi-value">{{ number_format($totalTamanTerlayani) }} / {{ number_format($totalTamanInScope) }}</div>
            </td>
            <td class="kpi">
                <div class="kpi-label">Cakupan</div>
                <div class="kpi-value">{{ $coveragePercent }}%</div>
            </td>
            <td class="kpi">
                <div class="kpi-label">Total Personil</div>
                <div class="kpi-value">{{ number_format($totalPersonil) }}</div>
            </td>
        </tr>
    </table>

    <h2>Rekap per Tim</h2>
    <table>
        <thead>
            <tr>
                <th>Tim</th>
                <th class="num">Kegiatan</th>
                <th class="num">Personil</th>
                <th class="num">Taman</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($ringkasanTim as $row)
                <tr>
                    <td>{{ $row['tim'] }}</td>
                    <td class="num">{{ number_format($row['jumlah']) }}</td>
                    <td class="num">{{ number_format($row['personil']) }}</td>
                    <td class="num">{{ number_format($row['taman']) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="empty">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Rekapitulasi per Taman / Lokasi</h2>
    <table>
        <thead>
            <tr>
                <th>Taman / Lokasi</th>
                <th>Kategori</th>
                <th>Wilayah</th>
                <th class="num">Kegiatan</th>
                <th class="num">Personil</th>
                <th>Tim</th>
                <th>Terakhir</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rekapPerTaman as $row)
                <tr>
                    <td>{{ $row['label'] }}</td>
                    <td>{{ $row['kategori'] }}</td>
                    <td>{{ $row['wilayah'] }}</td>
                    <td class="num">{{ number_format($row['jumlah_kegiatan']) }}</td>
                    <td class="num">{{ number_format($row['total_personil']) }}</td>
                    <td>{{ implode(', ', $row['tim_terlibat']) }}</td>
                    <td>{{ \App\Support\OperasionalPelaksanaanTime::display($row['tanggal_terakhir']) }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="empty">Tidak ada data pemeliharaan.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if ($tamanBelumDipelihara->isNotEmpty())
        <h2>Taman Belum Dipelihara</h2>
        <table>
            <thead>
                <tr>
                    <th>Taman</th>
                    <th>Kategori</th>
                    <th>Wilayah</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tamanBelumDipelihara as $taman)
                    <tr>
                        <td>{{ $taman->nama_taman }}</td>
                        <td>{{ $taman->kategori }}</td>
                        <td>{{ $taman->kelurahan?->nama ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
