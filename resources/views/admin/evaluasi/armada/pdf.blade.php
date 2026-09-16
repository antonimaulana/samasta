<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Evaluasi Utilisasi Armada</title>
    @include('admin.partials.pdf_styles')
</head>
<body>
    <h1>Evaluasi Utilisasi Armada</h1>
    <p class="meta">
        Dinas Perumahan, Kawasan Permukiman dan Pertamanan Kota Batam<br>
        Periode: <strong>{{ $labelPeriode }}</strong>
    </p>

    <table class="kpi-table">
        <tr>
            <td class="kpi">
                <div class="kpi-label">Total Penugasan</div>
                <div class="kpi-value">{{ number_format($totalPenugasan) }}</div>
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
                <div class="kpi-label">Armada Terpakai</div>
                <div class="kpi-value">{{ number_format($armadaTerpakai) }} / {{ number_format($totalArmada) }}</div>
            </td>
        </tr>
    </table>

    <h2>Rekap Utilisasi per Armada</h2>
    <table>
        <thead>
            <tr>
                <th>Armada</th>
                <th>Jenis</th>
                <th>No. Plat</th>
                <th class="num">Pemeliharaan</th>
                <th class="num">Permohonan</th>
                <th class="num">Total</th>
                <th>Sopir</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rekapPerArmada as $row)
                <tr>
                    <td>{{ $row['armada']->nama }}</td>
                    <td>{{ $row['armada']->jenis }}</td>
                    <td>{{ $row['armada']->no_plat ?: '—' }}</td>
                    <td class="num">{{ number_format($row['jumlah_pemeliharaan']) }}</td>
                    <td class="num">{{ number_format($row['jumlah_permohonan']) }}</td>
                    <td class="num">{{ number_format($row['total_penugasan']) }}</td>
                    <td>{{ implode(', ', $row['sopir']) ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="empty">Tidak ada inventaris armada.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if ($rekapSopir->isNotEmpty())
        <h2>Sopir Paling Aktif</h2>
        <table>
            <thead>
                <tr>
                    <th>Sopir</th>
                    <th class="num">Penugasan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rekapSopir as $row)
                    <tr>
                        <td>{{ $row['sopir'] }}</td>
                        <td class="num">{{ number_format($row['jumlah']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
