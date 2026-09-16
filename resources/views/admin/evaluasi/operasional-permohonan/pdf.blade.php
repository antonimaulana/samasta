<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Evaluasi Operasional Permohonan</title>
    @include('admin.partials.pdf_styles')
</head>
<body>
    <h1>Evaluasi Operasional Permohonan</h1>
    <p class="meta">
        Dinas Perumahan, Kawasan Permukiman dan Pertamanan Kota Batam<br>
        Periode: <strong>{{ $labelPeriode }}</strong>
        @if ($jenisLayanan) · Jenis: {{ $jenisLayanan }} @endif
        @if ($status) · Status: {{ $status }} @endif
        @if ($pelaksana) · Tim: {{ $pelaksana }} @endif
    </p>

    <table class="kpi-table">
        <tr>
            <td class="kpi">
                <div class="kpi-label">Total Permohonan</div>
                <div class="kpi-value">{{ number_format($totalPermohonan) }}</div>
            </td>
            <td class="kpi">
                <div class="kpi-label">Selesai</div>
                <div class="kpi-value">{{ number_format($totalSelesai) }} ({{ $persenSelesai }}%)</div>
            </td>
            <td class="kpi">
                <div class="kpi-label">Rata. Penyelesaian</div>
                <div class="kpi-value">{{ $rataHariPenyelesaian !== null ? number_format($rataHariPenyelesaian, 1).' hari' : '—' }}</div>
            </td>
            <td class="kpi">
                <div class="kpi-label">Tepat Waktu</div>
                <div class="kpi-value">{{ $persenTepatWaktu !== null ? $persenTepatWaktu.'%' : '—' }}</div>
            </td>
            <td class="kpi">
                <div class="kpi-label">Dokumentasi Lengkap</div>
                <div class="kpi-value">{{ $persenDokumentasiLengkap }}%</div>
            </td>
        </tr>
    </table>

    <h2>Rekap per Jenis Layanan</h2>
    <table>
        <thead>
            <tr>
                <th>Jenis Layanan</th>
                <th class="num">Total</th>
                <th class="num">Rencana</th>
                <th class="num">Diproses</th>
                <th class="num">Selesai</th>
                <th class="num">Rata. Hari</th>
                <th class="num">Tepat Waktu</th>
                <th class="num">Dokumentasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rekapPerJenis as $row)
                <tr>
                    <td>{{ $row['jenis'] }}</td>
                    <td class="num">{{ number_format($row['total']) }}</td>
                    <td class="num">{{ number_format($row['rencana']) }}</td>
                    <td class="num">{{ number_format($row['diproses']) }}</td>
                    <td class="num">{{ number_format($row['selesai']) }}</td>
                    <td class="num">{{ $row['rata_hari'] !== null ? number_format($row['rata_hari'], 1) : '—' }}</td>
                    <td class="num">{{ $row['persen_tepat_waktu'] !== null ? $row['persen_tepat_waktu'].'%' : '—' }}</td>
                    <td class="num">{{ $row['persen_dokumentasi'] }}%</td>
                </tr>
            @empty
                <tr><td colspan="8" class="empty">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Detail Permohonan</h2>
    <table>
        <thead>
            <tr>
                <th>Lokasi</th>
                <th>Jenis</th>
                <th>Status</th>
                <th>Pelaksana</th>
                <th class="num">Durasi (hari)</th>
                <th>SLA</th>
                <th class="num">Dok.</th>
                <th class="num">Progres</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($detailRows as $row)
                <tr>
                    <td>{{ $row['lokasi'] }}</td>
                    <td>{{ $row['jenis'] }}</td>
                    <td>{{ $row['status'] }}</td>
                    <td>{{ $row['pelaksana'] }}</td>
                    <td class="num">{{ $row['durasi_hari'] ?? '—' }}</td>
                    <td>{{ $row['sla_label'] }}</td>
                    <td class="num">{{ $row['dokumentasi_percent'] }}%</td>
                    <td class="num">{{ number_format($row['jumlah_progres']) }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="empty">Tidak ada permohonan.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
