<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Operasional Lengkap — {{ $permohonan->lokasi_pohon }}</title>
    <style>
        @page { size: A4 portrait; margin: 22mm 5mm 15mm 5mm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body {
            width: 100%;
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            font-size: 10px;
            line-height: 1.45;
        }

        .header-wrap { text-align: center; margin-bottom: 8px; padding-top: 6mm; }
        .header-logo { margin: 0 auto 4px; }
        .header-logo img { width: 48px; height: auto; display: inline-block; }
        .header-org {
            font-size: 12px;
            font-weight: bold;
            color: #14532d;
            letter-spacing: 0.3px;
            line-height: 1.25;
        }
        .header-dept {
            font-size: 10px;
            color: #374151;
            line-height: 1.3;
            margin-top: 2px;
        }
        .header-title {
            font-size: 10px;
            font-weight: bold;
            color: #166534;
            margin-top: 3px;
            line-height: 1.3;
        }
        .header-meta {
            text-align: center;
            margin: 4px 0 5px;
        }
        .badge {
            display: inline-block;
            background: #ecfdf5;
            color: #166534;
            border: 1px solid #86efac;
            padding: 3px 14px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 0.2px;
            line-height: 1.2;
        }
        .info { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .info th, .info td {
            border: 1px solid #cbd5e1;
            padding: 5px 8px;
            font-size: 10px;
            vertical-align: top;
        }
        .info th {
            width: 27%;
            background: #ecfdf5;
            text-align: left;
            font-weight: bold;
            color: #14532d;
        }
        .info td { color: #111827; }

        .rekap-heading {
            font-size: 13px;
            margin: 14px 0 8px;
            color: #15803d;
            border-bottom: 2px solid #bbf7d0;
            padding-bottom: 4px;
        }
        .rekap-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .rekap-table th, .rekap-table td {
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
            vertical-align: top;
            font-size: 10px;
        }
        .rekap-table th { background: #f0fdf4; text-align: left; font-size: 10px; }
        .foto-group { margin-bottom: 4px; }
        .foto-group-label {
            font-size: 8px;
            font-weight: bold;
            color: #166534;
            margin-bottom: 2px;
            text-transform: uppercase;
        }
        .foto-wrap { display: inline-block; width: 48%; vertical-align: top; text-align: center; padding: 2px; }
        .foto-wrap img { border: 1px solid #d1d5db; display: inline-block; }
        .rekap-empty { color: #9ca3af; font-style: italic; font-size: 9px; }

        .footer {
            margin-top: 6px;
            padding-top: 5px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 8px;
            color: #9ca3af;
        }
        .sopir-name { font-weight: 300; font-style: italic; }
    </style>
</head>
<body>
    @php
        $includeProgresPhotos = $includeProgresPhotos ?? true;
        $logoPath = public_path('images/logo-pemkot-batam.png');
        $logoBase64 = is_readable($logoPath)
            ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath))
            : null;
        $isTumbang = $permohonan->jenis_layanan === 'Penanganan Pohon Tumbang';
    @endphp

    <div class="header-wrap">
        @if ($logoBase64)
            <div class="header-logo">
                <img src="{{ $logoBase64 }}" alt="Logo Pemkot Batam">
            </div>
        @endif
        <div class="header-org">PEMERINTAH KOTA BATAM</div>
        <div class="header-dept">DINAS PERUMAHAN, KAWASAN PERMUKIMAN DAN PERTAMANAN</div>
        <div class="header-title">Laporan Operasional {{ $permohonan->jenis_layanan }} (Gabungan)</div>
        <div class="header-meta">
            <span class="badge">{{ $permohonan->pelaksanaLabel() ?: 'Operasional' }}</span>
        </div>
    </div>

    <table class="info">
        <tr>
            <th>Status</th>
            <td>{{ $permohonan->status }}</td>
        </tr>
        <tr>
            <th>{{ $isTumbang ? 'Tanggal Laporan' : 'Tanggal Permohonan' }}</th>
            <td>{{ \App\Support\OperasionalPelaksanaanTime::displayDateLong($permohonan->tanggal_permohonan) }}</td>
        </tr>
        <tr>
            <th>Jadwal Pelaksanaan</th>
            <td>{{ \App\Support\PemangkasanSchedule::labelRentang($permohonan) }} · {{ \App\Support\PemangkasanSchedule::progressSummary($permohonan) }}</td>
        </tr>
        <tr>
            <th>{{ $isTumbang ? 'Asal Laporan' : 'Asal Permohonan' }}</th>
            <td>{{ $permohonan->asalPermohonanPdfLabel() }}</td>
        </tr>
        <tr>
            <th>Lokasi Pelaksanaan</th>
            <td>{{ $permohonan->lokasiPelaksanaanPdfLabel() }}</td>
        </tr>
        <tr>
            <th>Kategori</th>
            <td>{{ $permohonan->kategori ?: '—' }}</td>
        </tr>
    </table>

    @include('admin.pemangkasans._pdf_progres_rekap_table', [
        'permohonan' => $permohonan,
        'includeProgresPhotos' => $includeProgresPhotos,
    ])

    <div class="footer">
        Dicetak otomatis dari Sistem {{ config('app.name') }} · {{ now()->format('d/m/Y H:i') }} WIB
    </div>
</body>
</html>
