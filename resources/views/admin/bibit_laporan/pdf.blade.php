<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Bibit</title>
    @include('admin.partials.pdf_styles')
</head>
<body>
    <h1>Laporan Mutasi Bibit</h1>
    <p class="meta">
        Dinas Perumahan, Kawasan Permukiman dan Pertamanan Kota Batam<br>
        Periode: <strong>{{ $labelPeriode }}</strong>
    </p>

    <table class="kpi-table">
        <tr>
            <td class="kpi">
                <div class="kpi-label">Stok Masuk</div>
                <div class="kpi-value">+{{ number_format($totalMasuk) }}</div>
            </td>
            <td class="kpi">
                <div class="kpi-label">Stok Keluar</div>
                <div class="kpi-value">-{{ number_format($totalKeluar) }}</div>
            </td>
            <td class="kpi">
                <div class="kpi-label">Mutasi Bersih</div>
                <div class="kpi-value">{{ $mutasiBersih >= 0 ? '+' : '' }}{{ number_format($mutasiBersih) }}</div>
            </td>
            <td class="kpi">
                <div class="kpi-label">Transaksi</div>
                <div class="kpi-value">{{ number_format($jumlahTransaksi) }}</div>
            </td>
        </tr>
    </table>

    <h2>Mutasi per Jenis Tanaman</h2>
    <table>
        <thead>
            <tr>
                <th>Nama Tanaman</th>
                <th>Jenis</th>
                <th style="width:12%" class="num">Masuk</th>
                <th style="width:12%" class="num">Keluar</th>
                <th style="width:12%" class="num">Mutasi</th>
                <th style="width:14%" class="num">Stok Sekarang</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($mutasiPerBibit as $row)
                <tr>
                    <td><x-admin.bibit-nama :bibit="$row['bibit']" pdf /></td>
                    <td>{{ $row['bibit']->jenis }}</td>
                    <td class="num">+{{ number_format($row['masuk']) }}</td>
                    <td class="num">-{{ number_format($row['keluar']) }}</td>
                    <td class="num">{{ $row['mutasi'] >= 0 ? '+' : '' }}{{ number_format($row['mutasi']) }}</td>
                    <td class="num">{{ number_format($row['stok_sekarang']) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="empty">Tidak ada mutasi stok pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Riwayat Stok Masuk ({{ number_format($riwayatMasuk->count()) }})</h2>
    <table>
        <thead>
            <tr>
                <th style="width:4%">No</th>
                <th style="width:12%">Tanggal</th>
                <th style="width:28%">Nama Tanaman</th>
                <th style="width:10%" class="num">Jumlah</th>
                <th style="width:22%">Sumber</th>
                <th style="width:10%" class="num">Foto</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($riwayatMasuk as $index => $masuk)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $masuk->tanggal_masuk->format('d/m/Y') }}</td>
                    <td><x-admin.bibit-nama :bibit="$masuk->bibit" pdf /></td>
                    <td class="num">+{{ number_format($masuk->jumlah) }}</td>
                    <td>{{ $masuk->sumber ?: '—' }}</td>
                    <td>{{ $masuk->foto ? 'Ada' : '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="empty">Tidak ada data masuk.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Riwayat Stok Keluar ({{ number_format($riwayatKeluar->count()) }})</h2>
    <table>
        <thead>
            <tr>
                <th style="width:4%">No</th>
                <th style="width:12%">Tanggal</th>
                <th style="width:28%">Nama Tanaman</th>
                <th style="width:10%" class="num">Jumlah</th>
                <th style="width:16%">Peruntukan</th>
                <th style="width:16%">Lokasi</th>
                <th style="width:8%" class="num">Foto</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($riwayatKeluar as $index => $keluar)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $keluar->tanggal_keluar->format('d/m/Y') }}</td>
                    <td><x-admin.bibit-nama :bibit="$keluar->bibit" pdf /></td>
                    <td class="num">-{{ number_format($keluar->jumlah) }}</td>
                    <td>{{ $keluar->peruntukan ?: '—' }}</td>
                    <td>{{ $keluar->taman?->nama_taman ?: '—' }}</td>
                    <td>{{ $keluar->foto ? 'Ada' : '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="empty">Tidak ada data keluar.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak otomatis dari Sistem {{ config('app.name') }} · {{ now()->format('d/m/Y H:i') }} WIB
    </div>
</body>
</html>
