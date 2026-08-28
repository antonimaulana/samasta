<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Daftar RTH — {{ config('app.name') }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin-top: 2.5cm;
            margin-bottom: 2.5cm;
            margin-left: 1.5cm;
            margin-right: 1.5cm;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body {
            margin: 0;
            padding: 0;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #1f2937;
            line-height: 1.4;
        }
        .document {
            width: 100%;
            max-width: 100%;
            padding: 0;
        }
        .header-wrap { text-align: center; margin-bottom: 10px; }
        .header-logo { margin: 0 auto 4px; }
        .header-logo img { width: 44px; height: auto; display: inline-block; }
        .header-org { font-size: 11px; font-weight: bold; color: #14532d; }
        .header-dept { font-size: 9px; color: #374151; margin-top: 2px; }
        .header-title { font-size: 10px; font-weight: bold; color: #166534; margin-top: 3px; }
        .meta {
            margin: 6px 0 8px;
            font-size: 8px;
            color: #6b7280;
            text-align: center;
        }
        .summary-block { margin-bottom: 8px; page-break-inside: avoid; }
        .summary-heading {
            font-size: 8px;
            font-weight: bold;
            color: #166534;
            margin-bottom: 2px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .summary {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }
        .summary td {
            border: 1px solid #bbf7d0;
            background: #f0fdf4;
            padding: 4px 6px;
            font-size: 7px;
            text-align: center;
            vertical-align: top;
        }
        .summary .value {
            display: block;
            font-size: 10px;
            font-weight: bold;
            color: #166534;
            margin-top: 1px;
        }
        .summary-status-data .lengkap { background: #f0fdf4; border-color: #86efac; }
        .summary-status-data .belum-lengkap { background: #fffbeb; border-color: #fcd34d; }
        .summary-status-data .lengkap .value { color: #166534; }
        .summary-status-data .belum-lengkap .value { color: #b45309; }
        .section { margin-bottom: 10px; page-break-inside: avoid; }
        .section-title {
            background: rgba(236, 253, 245, 0.95);
            border: 1px solid rgba(134, 239, 172, 0.55);
            border-bottom: none;
            color: #166534;
            font-size: 9px;
            font-weight: bold;
            padding: 4px 8px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 2px; }
        table.data th, table.data td {
            border: 1px solid #d1d5db;
            padding: 4px 6px;
            vertical-align: top;
            font-size: 8px;
        }
        table.data th {
            background: #ecfdf5;
            color: #14532d;
            font-weight: bold;
            text-align: left;
        }
        table.data td.num { text-align: right; white-space: nowrap; }
        table.data td.center { text-align: center; }
        .footer {
            margin-top: 10px;
            padding-top: 6px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 7px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="document">
    @php
        $logoPath = public_path('images/logo-pemkot-batam.png');
        $logoBase64 = is_readable($logoPath)
            ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath))
            : null;
    @endphp

    <div class="header-wrap">
        @if ($logoBase64)
            <div class="header-logo">
                <img src="{{ $logoBase64 }}" alt="Logo Pemkot Batam">
            </div>
        @endif
        <div class="header-org">PEMERINTAH KOTA BATAM</div>
        <div class="header-dept">DINAS PERUMAHAN, KAWASAN PERMUKIMAN DAN PERTAMANAN</div>
        <div class="header-title">Daftar Ruang Terbuka Hijau (RTH)</div>
    </div>

    <p class="meta">
        Dicetak: <strong>{{ $generatedAt->translatedFormat('d F Y H:i') }} WIB</strong>
        · Total: <strong>{{ number_format($totalTaman) }} RTH</strong>
        @if ($search)
            · Filter: <strong>{{ $search }}</strong>
        @endif
    </p>

    @if ($tamansPerKategori->isNotEmpty())
        <div class="summary-block">
            <div class="summary-heading">Rekap per Kategori</div>
            <table class="summary">
                <tr>
                    @foreach ($tamansPerKategori as $kategori => $items)
                        <td>
                            {{ $kategori }}
                            <span class="value">{{ number_format($items->count()) }}</span>
                        </td>
                    @endforeach
                </tr>
            </table>
        </div>

        <div class="summary-block">
            <div class="summary-heading">Rekap per Wilayah (Kecamatan)</div>
            <table class="summary">
                <tr>
                    @foreach ($tamansPerWilayah as $wilayah => $jumlah)
                        <td>
                            {{ $wilayah }}
                            <span class="value">{{ number_format($jumlah) }}</span>
                        </td>
                    @endforeach
                </tr>
            </table>
        </div>

        <div class="summary-block">
            <div class="summary-heading">Rekap Status Data</div>
            <table class="summary summary-status-data">
                <tr>
                    <td class="lengkap">
                        Lengkap
                        <span class="value">{{ number_format($rekapStatusData['Lengkap'] ?? 0) }}</span>
                    </td>
                    <td class="belum-lengkap">
                        Belum Lengkap
                        <span class="value">{{ number_format($rekapStatusData['Belum Lengkap'] ?? 0) }}</span>
                    </td>
                </tr>
            </table>
        </div>

        @foreach ($tamansPerKategori as $kategori => $items)
            <div class="section">
                <div class="section-title">{{ $kategori }} ({{ number_format($items->count()) }})</div>
                <table class="data">
                    <thead>
                        <tr>
                            <th style="width:4%">No</th>
                            <th style="width:18%">Nama RTH</th>
                            <th style="width:14%">Wilayah</th>
                            <th style="width:9%">Luasan (m²)</th>
                            <th>Alamat</th>
                            <th style="width:12%">Koordinat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $index => $taman)
                            <tr>
                                <td class="center">{{ $index + 1 }}</td>
                                <td><strong>{{ $taman->nama_taman }}</strong></td>
                                <td>
                                    @if ($taman->kelurahan)
                                        {{ $taman->kelurahan->kecamatan->nama ?? '—' }}<br>
                                        <span style="color:#6b7280;">{{ $taman->kelurahan->nama }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="num">{{ number_format($taman->luasan, 0, ',', '.') }}</td>
                                <td>{{ $taman->alamat ?? '—' }}</td>
                                <td class="center">
                                    @if ($taman->latitude && $taman->longitude)
                                        {{ $taman->latitude }}, {{ $taman->longitude }}
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @else
        <p style="text-align:center;color:#9ca3af;font-style:italic;padding:16px;">Tidak ada data RTH.</p>
    @endif

    <div class="footer">
        Dicetak otomatis dari Sistem {{ config('app.name') }} · {{ $generatedAt->format('d/m/Y H:i') }} WIB
    </div>
    </div>
</body>
</html>
