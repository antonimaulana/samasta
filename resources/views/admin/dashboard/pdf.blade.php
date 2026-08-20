<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Ringkasan Operasional {{ config('app.name') }}</title>
    @include('admin.partials.pdf_styles')
</head>
<body>
    <h1>Ringkasan Operasional Pertamanan</h1>
    <p class="meta">
        {{ config('app.name') }} — Dinas Perumahan, Kawasan Permukiman dan Pertamanan Kota Batam<br>
        Dicetak: <strong>{{ $generated_at->translatedFormat('d F Y H:i') }} WIB</strong><br>
        Status operasional: <strong>{{ $executive_status['label'] }}</strong>
        — {{ $executive_status['description'] }}
    </p>

    <table class="kpi-table">
        <tr>
            <td class="kpi">
                <div class="kpi-label">Taman Terdaftar</div>
                <div class="kpi-value">{{ number_format($total_taman) }}</div>
            </td>
            <td class="kpi">
                <div class="kpi-label">Penyelesaian Operasional</div>
                <div class="kpi-value">{{ $layanan_selesai_persen }}%</div>
            </td>
            <td class="kpi">
                <div class="kpi-label">Kepuasan Masyarakat</div>
                <div class="kpi-value">
                    {{ $survey_summary['total'] > 0 ? number_format($survey_summary['average'], 1).'/5' : '—' }}
                </div>
            </td>
            <td class="kpi">
                <div class="kpi-label">Aduan Baru</div>
                <div class="kpi-value">{{ number_format($aduan_baru) }}</div>
            </td>
        </tr>
    </table>

    <h2>Jadwal Operasional</h2>
    <table>
        <thead>
            <tr>
                <th>Indikator</th>
                <th class="num">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>Hari Ini</td><td class="num">{{ $jadwal_counts['hari_ini'] }}</td></tr>
            <tr><td>Besok (H-1)</td><td class="num">{{ $jadwal_counts['besok'] }}</td></tr>
            <tr><td>Sedang Diproses</td><td class="num">{{ $jadwal_counts['diproses'] }}</td></tr>
            <tr><td>Terlambat</td><td class="num">{{ $jadwal_counts['terlambat'] }}</td></tr>
        </tbody>
    </table>

    <h2>Aset Hijau & Pembibitan</h2>
    <table>
        <tbody>
            <tr><td>Stok bibit</td><td class="num">{{ number_format($total_stok_bibit) }} unit ({{ $bibit_siap_persen }}% siap tanam)</td></tr>
            <tr><td>Varietas bibit</td><td class="num">{{ number_format($total_varietas_bibit) }}</td></tr>
            <tr><td>Operasional aktif</td><td class="num">{{ number_format($layanan_aktif) }}</td></tr>
            <tr><td>Aduan aktif</td><td class="num">{{ number_format($aduan_aktif) }}</td></tr>
            <tr><td>Aduan selesai bulan ini</td><td class="num">{{ number_format($aduan_selesai_bulan) }}</td></tr>
        </tbody>
    </table>

    <h2>Status Operasional</h2>
    <table>
        <thead>
            <tr>
                <th>Status</th>
                <th class="num">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>Rencana</td><td class="num">{{ number_format($status_global['rencana']) }}</td></tr>
            <tr><td>Diproses</td><td class="num">{{ number_format($status_global['diproses']) }}</td></tr>
            <tr><td>Selesai</td><td class="num">{{ number_format($status_global['selesai']) }}</td></tr>
        </tbody>
    </table>

    <h2>Operasional per Jenis</h2>
    <table>
        <thead>
            <tr>
                <th>Jenis</th>
                <th class="num">Rencana</th>
                <th class="num">Diproses</th>
                <th class="num">Selesai</th>
                <th class="num">Progress</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($layanan_per_jenis as $row)
                <tr>
                    <td>{{ $row['jenis'] }}</td>
                    <td class="num">{{ $row['rencana'] }}</td>
                    <td class="num">{{ $row['diproses'] }}</td>
                    <td class="num">{{ $row['selesai'] }}</td>
                    <td class="num">{{ $row['progress'] }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if ($operationalAlertsTotal > 0)
        <h2>Catatan Operasional</h2>
        <table>
            <thead>
                <tr>
                    <th>Indikator</th>
                    <th class="num">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($operationalAlerts as $alert)
                    <tr>
                        <td>{{ $alert['label'] }} — {{ $alert['description'] }}</td>
                        <td class="num">{{ $alert['count'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <p class="footer">Dokumen otomatis dari sistem {{ config('app.name') }} · {{ config('app.url') }}</p>
</body>
</html>
