<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Evaluasi Masukan Masyarakat</title>
    @include('admin.partials.pdf_styles')
</head>
<body>
    <h1>Evaluasi Masukan Masyarakat</h1>
    <p class="meta">
        Dinas Perumahan, Kawasan Permukiman dan Pertamanan Kota Batam<br>
        Periode: <strong>{{ $labelPeriode }}</strong>
        @if ($jenisAduan) · Jenis Aduan: {{ $jenisAduan }} @endif
        @if ($status) · Status: {{ $status }} @endif
        @if ($kategoriSurvey) · Kategori Survey: {{ $kategoriSurvey }} @endif
    </p>

    <table class="kpi-table">
        <tr>
            <td class="kpi">
                <div class="kpi-label">Total Masukan</div>
                <div class="kpi-value">{{ number_format($totalMasukan) }}</div>
                <div class="kpi-label">{{ number_format($totalAduan) }} aduan · {{ number_format($totalSurvey) }} survey</div>
            </td>
            <td class="kpi">
                <div class="kpi-label">Aduan Selesai</div>
                <div class="kpi-value">{{ number_format($aduanSelesai) }}</div>
                <div class="kpi-label">{{ $persenSelesai }}%</div>
            </td>
            <td class="kpi">
                <div class="kpi-label">Aduan Aktif</div>
                <div class="kpi-value">{{ number_format($aduanOpen) }}</div>
                <div class="kpi-label">{{ number_format($aduanOverdue) }} terlambat ditinjau</div>
            </td>
            <td class="kpi">
                <div class="kpi-label">Rating Survey</div>
                <div class="kpi-value">{{ $surveyAverage !== null ? number_format($surveyAverage, 1).' / 5' : '—' }}</div>
                <div class="kpi-label">{{ $persenPuas !== null ? $persenPuas.'% puas' : '—' }}</div>
            </td>
        </tr>
    </table>

    <h2>Rekap Aduan per Jenis</h2>
    <table>
        <thead>
            <tr>
                <th>Jenis Aduan</th>
                <th class="num">Total</th>
                <th class="num">Selesai</th>
                <th class="num">Aktif</th>
                <th class="num">Terlambat</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rekapPerJenis as $row)
                <tr>
                    <td>{{ $row['jenis'] }}</td>
                    <td class="num">{{ number_format($row['total']) }}</td>
                    <td class="num">{{ number_format($row['selesai']) }}</td>
                    <td class="num">{{ number_format($row['open']) }}</td>
                    <td class="num">{{ number_format($row['overdue']) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty">Tidak ada aduan.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Rekap Survey per Kategori</h2>
    <table>
        <thead>
            <tr>
                <th>Kategori</th>
                <th class="num">Total</th>
                <th class="num">Rata-rata</th>
                <th class="num">Puas (≥4)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rekapSurvey as $row)
                <tr>
                    <td>{{ $row['kategori'] }}</td>
                    <td class="num">{{ number_format($row['total']) }}</td>
                    <td class="num">{{ $row['average'] !== null ? number_format($row['average'], 1) : '—' }}</td>
                    <td class="num">{{ number_format($row['puas']) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="empty">Tidak ada survey.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Taman dengan Aduan Terbanyak</h2>
    <table>
        <thead>
            <tr>
                <th>Taman</th>
                <th>Kecamatan</th>
                <th class="num">Aduan Periode</th>
                <th class="num">Masih Aktif</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($topTaman as $row)
                <tr>
                    <td>{{ $row['nama'] }}</td>
                    <td>{{ $row['wilayah'] }}</td>
                    <td class="num">{{ number_format($row['total']) }}</td>
                    <td class="num">{{ number_format($row['open']) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="empty">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Backlog Aduan Aktif</h2>
    <table>
        <thead>
            <tr>
                <th>Nomor</th>
                <th>Jenis</th>
                <th>Lokasi</th>
                <th>Status</th>
                <th>Diterima</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($openAduans as $aduan)
                <tr>
                    <td>{{ $aduan->nomor_aduan }}</td>
                    <td>{{ $aduan->jenis_aduan }}</td>
                    <td>{{ $aduan->taman?->nama_taman ?? $aduan->lokasi }}</td>
                    <td>{{ $aduan->status }}</td>
                    <td>{{ $aduan->created_at->timezone(config('app.timezone'))->format('d/m/Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty">Tidak ada aduan aktif.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
