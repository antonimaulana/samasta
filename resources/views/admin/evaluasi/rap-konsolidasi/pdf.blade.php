<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan RAP Konsolidasi</title>
    @include('admin.partials.pdf_styles')
</head>
<body>
    <h1>Laporan RAP Konsolidasi</h1>
    <p class="meta">
        Optimalisasi Pengelolaan Data dan Monitoring Operasional RTH Taman via SIMTAMAN<br>
        Dinas Perumahan, Kawasan Permukiman dan Pertamanan Kota Batam<br>
        Periode operasional: <strong>{{ $labelPeriode }}</strong><br>
        Snapshot basis data: {{ $labelSnapshot }}
        @if ($kategori) · Kategori: {{ $kategori }} @endif
    </p>

    <table class="kpi-table">
        <tr>
            <td class="kpi">
                <div class="kpi-label">Indeks Kinerja RAP</div>
                <div class="kpi-value">{{ $indeksKinerja !== null ? $indeksKinerja.'%' : '—' }}</div>
            </td>
            <td class="kpi">
                <div class="kpi-label">Kelengkapan Data</div>
                <div class="kpi-value">{{ $ringkasanBasisData['persenLengkap'] }}%</div>
            </td>
            <td class="kpi">
                <div class="kpi-label">RTH Terpelihara</div>
                <div class="kpi-value">{{ $ringkasanBasisData['persenRthTerpelihara'] }}%</div>
            </td>
            <td class="kpi">
                <div class="kpi-label">Kepuasan Survey</div>
                <div class="kpi-value">{{ $ringkasanPartisipasi['persenPuas'] !== null ? $ringkasanPartisipasi['persenPuas'].'%' : '—' }}</div>
            </td>
        </tr>
    </table>

    @foreach ($pillars as $pillar)
        <h2>{{ $pillar['pillar'] }}</h2>
        <p class="meta">{{ $pillar['description'] }}</p>
        <table>
            <thead>
                <tr>
                    <th>Indikator</th>
                    <th class="num">Capaian</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pillar['indicators'] as $indicator)
                    <tr>
                        <td>{{ $indicator['label'] }}</td>
                        <td class="num">{{ $indicator['value'] }}</td>
                        <td>{{ $indicator['detail'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach

    <h2>Prioritas Tindak Lanjut</h2>
    <table>
        <thead>
            <tr>
                <th>Prioritas</th>
                <th>Objek</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($prioritas as $item)
                <tr>
                    <td>{{ $item['prioritas'] }}</td>
                    <td>{{ $item['label'] }}</td>
                    <td>{{ $item['detail'] }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="empty">Tidak ada isu kritis.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
