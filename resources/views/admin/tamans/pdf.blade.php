<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Data Taman — {{ config('app.name') }}</title>
    <style>
        @page { size: A4 landscape; margin: 12mm 8mm 12mm 8mm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #1f2937;
            line-height: 1.4;
        }
        .header-wrap { text-align: center; margin-bottom: 8px; }
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
        .summary {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .summary td {
            border: 1px solid #bbf7d0;
            background: #f0fdf4;
            padding: 4px 8px;
            font-size: 8px;
            text-align: center;
        }
        .summary .value {
            display: block;
            font-size: 11px;
            font-weight: bold;
            color: #166534;
            margin-top: 1px;
        }
        .section { margin-bottom: 8px; page-break-inside: avoid; }
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
            margin-top: 6px;
            padding-top: 4px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 7px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
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
        <div class="header-title">Data Taman Terdaftar</div>
    </div>

    <p class="meta">
        Dicetak: <strong>{{ $generatedAt->translatedFormat('d F Y H:i') }} WIB</strong>
        · Total: <strong>{{ number_format($totalTaman) }} taman</strong>
        @if ($search)
            · Filter: <strong>{{ $search }}</strong>
        @endif
    </p>

    @if ($tamansPerKategori->isNotEmpty())
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

        @foreach ($tamansPerKategori as $kategori => $items)
            <div class="section">
                <div class="section-title">{{ $kategori }} ({{ number_format($items->count()) }})</div>
                <table class="data">
                    <thead>
                        <tr>
                            <th style="width:4%">No</th>
                            <th style="width:18%">Nama Taman</th>
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
        <p style="text-align:center;color:#9ca3af;font-style:italic;padding:16px;">Tidak ada data taman.</p>
    @endif

    <div class="footer">
        Dicetak otomatis dari Sistem {{ config('app.name') }} · {{ $generatedAt->format('d/m/Y H:i') }} WIB
    </div>
</body>
</html>
