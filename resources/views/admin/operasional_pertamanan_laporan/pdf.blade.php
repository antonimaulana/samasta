<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Operasional Pertamanan</title>
    @include('admin.partials.pdf_styles')
</head>
<body>
    <h1>Laporan Operasional Pertamanan</h1>
    <p class="meta">
        Dinas Perumahan, Kawasan Permukiman dan Pertamanan Kota Batam<br>
        Periode: <strong>{{ $labelPeriode }}</strong>
        @if ($jenisLayanan !== '')
            · Jenis: <strong>{{ $jenisLayanan }}</strong>
        @else
            · Semua jenis operasional
        @endif
        · Berdasarkan tanggal pelaksanaan
        @if ($search !== '')
            <br>Filter pencarian: <strong>{{ $search }}</strong>
        @endif
    </p>

    <table class="kpi-table">
        <tr>
            <td class="kpi">
                <div class="kpi-label">Total Operasional</div>
                <div class="kpi-value">{{ number_format($totalLayanan) }}</div>
            </td>
            <td class="kpi">
                <div class="kpi-label">Rencana</div>
                <div class="kpi-value">{{ number_format($totalRencana) }}</div>
            </td>
            <td class="kpi">
                <div class="kpi-label">Diproses</div>
                <div class="kpi-value">{{ number_format($totalDiproses) }}</div>
            </td>
            <td class="kpi">
                <div class="kpi-label">Selesai</div>
                <div class="kpi-value">{{ number_format($totalSelesai) }}</div>
            </td>
        </tr>
    </table>

    <h2>Ringkasan per Jenis Operasional</h2>
    <table>
        <thead>
            <tr>
                <th>Jenis Operasional</th>
                <th style="width:12%" class="num">Rencana</th>
                <th style="width:12%" class="num">Diproses</th>
                <th style="width:12%" class="num">Selesai</th>
                <th style="width:12%" class="num">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($ringkasanPerJenis as $row)
                <tr>
                    <td>{{ $row['jenis'] }}</td>
                    <td class="num">{{ number_format($row['rencana']) }}</td>
                    <td class="num">{{ number_format($row['diproses']) }}</td>
                    <td class="num">{{ number_format($row['selesai']) }}</td>
                    <td class="num"><strong>{{ number_format($row['total']) }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="empty">Tidak ada data pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Daftar Operasional ({{ number_format($daftarLayanan->count()) }})</h2>
    <table>
        <thead>
            <tr>
                <th style="width:4%">No</th>
                <th style="width:12%">Tgl. Pelaksanaan</th>
                <th style="width:18%">Jenis</th>
                <th style="width:24%">Lokasi</th>
                <th style="width:14%">Pelaksana</th>
                <th style="width:10%">Status</th>
                <th style="width:18%">Asal / Kategori</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($daftarLayanan as $index => $layanan)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $layanan->tanggal_eksekusi->format('d/m/Y') }}</td>
                    <td>{{ $layanan->jenis_layanan }}</td>
                    <td>{{ $layanan->lokasi_pohon }}</td>
                    <td>{{ $layanan->pelaksanaLabel() ?: '—' }}</td>
                    <td>{{ $layanan->status }}</td>
                    <td>{{ $layanan->asal }} · {{ $layanan->kategori }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="empty">Tidak ada operasional pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak otomatis dari Sistem {{ config('app.name') }} · {{ now()->format('d/m/Y H:i') }} WIB
    </div>
</body>
</html>
