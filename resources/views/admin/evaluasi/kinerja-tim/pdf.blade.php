<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Evaluasi Kinerja Tim</title>
    @include('admin.partials.pdf_styles')
</head>
<body>
    <h1>Evaluasi Kinerja Tim</h1>
    <p class="meta">
        Dinas Perumahan, Kawasan Permukiman dan Pertamanan Kota Batam<br>
        Periode: <strong>{{ $labelPeriode }}</strong>
        @if ($tim) · Tim: {{ $tim }} @endif
    </p>

    <table class="kpi-table">
        <tr>
            <td class="kpi">
                <div class="kpi-label">Total Kegiatan</div>
                <div class="kpi-value">{{ number_format($totalKegiatan) }}</div>
            </td>
            <td class="kpi">
                <div class="kpi-label">Pemeliharaan</div>
                <div class="kpi-value">{{ number_format($totalPemeliharaan) }}</div>
            </td>
            <td class="kpi">
                <div class="kpi-label">Permohonan</div>
                <div class="kpi-value">{{ number_format($totalPermohonan) }}</div>
            </td>
            <td class="kpi">
                <div class="kpi-label">Total Personil</div>
                <div class="kpi-value">{{ number_format($totalPersonil) }}</div>
            </td>
            <td class="kpi">
                <div class="kpi-label">Tepat Waktu</div>
                <div class="kpi-value">{{ $rataTepatWaktu !== null ? $rataTepatWaktu.'%' : '—' }}</div>
            </td>
        </tr>
    </table>

    <h2>Rekapitulasi Kinerja per Tim</h2>
    <table>
        <thead>
            <tr>
                <th>Tim</th>
                <th>Pengawas</th>
                <th class="num">Pemeliharaan</th>
                <th class="num">Permohonan</th>
                <th class="num">Total</th>
                <th class="num">Personil</th>
                <th class="num">Rata. Personil</th>
                <th class="num">Taman</th>
                <th class="num">Tepat Waktu</th>
                <th class="num">Terlambat</th>
                <th class="num">Survey</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rekapPerTim as $row)
                <tr>
                    <td>{{ $row['tim'] }}</td>
                    <td>{{ $row['nama_pengawas'] ?: '—' }}</td>
                    <td class="num">{{ number_format($row['jumlah_pemeliharaan']) }}</td>
                    <td class="num">{{ number_format($row['jumlah_permohonan']) }}</td>
                    <td class="num">{{ number_format($row['total_kegiatan']) }}</td>
                    <td class="num">{{ number_format($row['total_personil']) }}</td>
                    <td class="num">{{ number_format($row['rata_personil'], 1) }}</td>
                    <td class="num">{{ number_format($row['taman_terlayani']) }}</td>
                    <td class="num">{{ $row['persen_tepat_waktu'] !== null ? $row['persen_tepat_waktu'].'%' : '—' }}</td>
                    <td class="num">{{ number_format($row['permohonan_terlambat']) }}</td>
                    <td class="num">
                        @if ($row['survey_rata'] !== null)
                            {{ $row['survey_rata'] }}/5 ({{ number_format($row['survey_jumlah']) }})
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="11" class="empty">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
