<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Operasional — {{ $kinerja->lokasi_pelaksanaan }}</title>
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
        .sopir-name { font-weight: 300; font-style: italic; color: #374151; }

        .photo-section {
            margin-bottom: 9px;
            page-break-inside: avoid;
            border: 1px solid rgba(134, 239, 172, 0.55);
            background: #fff;
        }
        .photo-section-head {
            width: 100%;
            border-collapse: collapse;
        }
        .photo-section-head td { border: none; padding: 0; vertical-align: middle; }
        .photo-section-accent { width: 4px; background: rgba(134, 239, 172, 0.45); }
        .photo-section-bar {
            background: rgba(236, 253, 245, 0.85);
            border-bottom: 1px solid rgba(134, 239, 172, 0.55);
            padding: 5px 10px;
            text-align: center;
        }
        .photo-section-label {
            font-size: 10px;
            font-weight: bold;
            color: #166534;
            letter-spacing: 0.6px;
            text-transform: uppercase;
        }
        .photos { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .photos td {
            width: 50%;
            vertical-align: middle;
            text-align: center;
            padding: 5px 4px 6px;
            border-top: 1px solid rgba(134, 239, 172, 0.35);
        }
        .photo-box {
            border: 1px solid rgba(134, 239, 172, 0.4);
            background: #f8fafc;
            padding: 4px;
            min-height: 178px;
            text-align: center;
        }
        .photo-box img { display: inline-block; max-width: 100%; }
        .photo-empty {
            min-height: 178px;
            color: #9ca3af;
            font-style: italic;
            font-size: 9px;
            line-height: 178px;
            text-align: center;
        }

        .footer {
            margin-top: 6px;
            padding-top: 5px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 8px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    @php
        $fotoGroups = [
            'Sebelum Pelaksanaan' => ['foto_sebelum_1', 'foto_sebelum_2'],
            'Saat Pelaksanaan' => ['foto_saat_1', 'foto_saat_2'],
            'Sesudah Pelaksanaan' => ['foto_sesudah_1', 'foto_sesudah_2'],
        ];
        $logoPath = public_path('images/logo-pemkot-batam.png');
        $logoBase64 = is_readable($logoPath)
            ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath))
            : null;
        $photoMaxWidth = 392;
        $photoMaxHeight = 178;
    @endphp

    <div class="header-wrap">
        @if ($logoBase64)
            <div class="header-logo">
                <img src="{{ $logoBase64 }}" alt="Logo Pemkot Batam">
            </div>
        @endif
        <div class="header-org">PEMERINTAH KOTA BATAM</div>
        <div class="header-dept">DINAS PERUMAHAN, KAWASAN PERMUKIMAN DAN PERTAMANAN</div>
        <div class="header-title">Laporan Operasional Pemeliharaan Taman</div>
        <div class="header-meta">
            <span class="badge">{{ $kinerja->tim }}</span>
        </div>
    </div>

    <table class="info">
        <tr>
            <th>Tanggal Pelaksanaan</th>
            <td>{{ \App\Support\OperasionalPelaksanaanTime::displayLong($kinerja->tanggal) }}</td>
        </tr>
        <tr>
            <th>Pengawas</th>
            <td>{{ $kinerja->namaPengawas() ?? '—' }}</td>
        </tr>
        <tr>
            <th>Lokasi Pelaksanaan</th>
            <td>({{ $kinerja->lokasiKategoriLabel() }}) {{ $kinerja->lokasi_pelaksanaan }}</td>
        </tr>
        @if ($kinerja->jumlah_personil)
            <tr>
                <th>Jumlah Personil</th>
                <td>{{ $kinerja->jumlah_personil }} orang</td>
            </tr>
        @endif
        @if ($kinerja->progressSummaryLabel())
            <tr>
                <th>Progres Pekerjaan</th>
                <td>{{ $kinerja->progressSummaryLabel() }}</td>
            </tr>
        @endif
        @if ($kinerja->uraian_pekerjaan)
            <tr>
                <th>Uraian Pekerjaan</th>
                <td>{{ $kinerja->uraian_pekerjaan }}</td>
            </tr>
        @endif
        @if ($kinerja->armadaPdfHtml())
            <tr>
                <th>Armada</th>
                <td>{!! $kinerja->armadaPdfHtml() !!}</td>
            </tr>
        @endif
    </table>

    @foreach ($fotoGroups as $groupLabel => $fields)
        <div class="photo-section">
            <table class="photo-section-head">
                <tr>
                    <td class="photo-section-accent"></td>
                    <td class="photo-section-bar">
                        <span class="photo-section-label">{{ $groupLabel }}</span>
                    </td>
                </tr>
            </table>
            <table class="photos">
                <tr>
                    @foreach ($fields as $field)
                        @php
                            $base64 = $kinerja->fotoBase64($field);
                            $size = $kinerja->fotoPdfSize($field, $photoMaxWidth, $photoMaxHeight);
                        @endphp
                        <td>
                            <div class="photo-box">
                                @if ($base64 && $size)
                                    <img src="{{ $base64 }}"
                                         alt="{{ $groupLabel }}"
                                         width="{{ $size['width'] }}"
                                         height="{{ $size['height'] }}">
                                @else
                                    <div class="photo-empty">Tidak ada foto</div>
                                @endif
                            </div>
                        </td>
                    @endforeach
                </tr>
            </table>
        </div>
    @endforeach

    <div class="footer">
        Dicetak otomatis dari Sistem {{ config('app.name') }} · {{ now()->format('d/m/Y H:i') }} WIB · ID #{{ $kinerja->id }}
    </div>
</body>
</html>
