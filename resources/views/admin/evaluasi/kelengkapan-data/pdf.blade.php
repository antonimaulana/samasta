<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Evaluasi Kelengkapan & Kemutakhiran Data</title>
    @include('admin.partials.pdf_styles')
</head>
<body>
    <h1>Evaluasi Kelengkapan & Kemutakhiran Data</h1>
    <p class="meta">
        Dinas Perumahan, Kawasan Permukiman dan Pertamanan Kota Batam<br>
        {{ $labelSnapshot }}<br>
        {{ $labelKriteria }}
        @if ($kategori) · Kategori: {{ $kategori }} @endif
        @if ($kecamatan_id)
            · Kecamatan: {{ $daftarKecamatan->firstWhere('id', (int) $kecamatan_id)?->nama ?? $kecamatan_id }}
        @endif
    </p>

    <table class="kpi-table">
        <tr>
            <td class="kpi">
                <div class="kpi-label">Total Lokasi</div>
                <div class="kpi-value">{{ number_format($totalLokasi) }}</div>
                <div class="kpi-label">Rata. skor {{ $rataScore }}%</div>
            </td>
            <td class="kpi">
                <div class="kpi-label">Data Lengkap</div>
                <div class="kpi-value">{{ number_format($lokasiLengkap) }}</div>
                <div class="kpi-label">{{ $persenLengkap }}%</div>
            </td>
            <td class="kpi">
                <div class="kpi-label">Data Mutakhir</div>
                <div class="kpi-value">{{ number_format($lokasiMutakhir) }}</div>
                <div class="kpi-label">{{ $persenMutakhir }}%</div>
            </td>
            <td class="kpi">
                <div class="kpi-label">Perlu Perhatian</div>
                <div class="kpi-value">{{ number_format($lokasiBelumLengkap + $lokasiKedaluwarsa + $lokasiBelumVerifikasi) }}</div>
                <div class="kpi-label">{{ number_format($lokasiKedaluwarsa) }} kedaluwarsa · {{ number_format($lokasiBelumVerifikasi) }} belum verifikasi</div>
            </td>
        </tr>
    </table>

    <h2>Field Paling Sering Kosong</h2>
    <table>
        <thead>
            <tr>
                <th>Field</th>
                <th class="num">Lokasi Kosong</th>
                <th class="num">%</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($fieldGaps as $row)
                <tr>
                    <td>{{ $row['label'] }}</td>
                    <td class="num">{{ number_format($row['missing']) }}</td>
                    <td class="num">{{ $row['persen'] }}%</td>
                </tr>
            @empty
                <tr><td colspan="3" class="empty">Tidak ada gap.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Rekap per Kategori</h2>
    <table>
        <thead>
            <tr>
                <th>Kategori</th>
                <th class="num">Lokasi</th>
                <th class="num">Lengkap</th>
                <th class="num">Mutakhir</th>
                <th class="num">Skor</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rekapPerKategori as $row)
                <tr>
                    <td>{{ $row['label'] }}</td>
                    <td class="num">{{ number_format($row['total_lokasi']) }}</td>
                    <td class="num">{{ $row['persen_lengkap'] }}%</td>
                    <td class="num">{{ $row['persen_mutakhir'] }}%</td>
                    <td class="num">{{ $row['rata_score'] }}%</td>
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
                <th class="num">Lengkap</th>
                <th class="num">Mutakhir</th>
                <th class="num">Skor</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rekapPerKecamatan as $row)
                <tr>
                    <td>{{ $row['label'] }}</td>
                    <td class="num">{{ number_format($row['total_lokasi']) }}</td>
                    <td class="num">{{ $row['persen_lengkap'] }}%</td>
                    <td class="num">{{ $row['persen_mutakhir'] }}%</td>
                    <td class="num">{{ $row['rata_score'] }}%</td>
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
                <th class="num">Skor</th>
                <th>Field Kosong</th>
                <th>Verifikasi Terakhir</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($perluPerhatian as $row)
                <tr>
                    <td>{{ $row['nama'] }}</td>
                    <td>{{ $row['kategori'] }}</td>
                    <td class="num">{{ $row['score'] }}%</td>
                    <td>{{ implode(', ', $row['missing_labels']) ?: '—' }}</td>
                    <td>
                        @if ($row['verified_at'])
                            {{ $row['verified_at']->timezone(config('app.timezone'))->format('d/m/Y') }}
                        @else
                            Belum diverifikasi
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty">Semua profil lengkap dan mutakhir.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
