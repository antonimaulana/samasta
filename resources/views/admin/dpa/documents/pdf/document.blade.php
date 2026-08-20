<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $judul }} — {{ $paket->nama_paket }}</title>
    <style>
        @page { size: A4 portrait; margin: 18mm 12mm 15mm 12mm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body {
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            font-size: 10px;
            line-height: 1.45;
        }
        .header-wrap { text-align: center; margin-bottom: 10px; }
        .header-logo img { width: 46px; height: auto; }
        .header-org { font-size: 12px; font-weight: bold; color: #14532d; }
        .header-dept { font-size: 10px; color: #374151; margin-top: 2px; }
        .header-title { font-size: 11px; font-weight: bold; color: #166534; margin-top: 4px; }
        .doc-title {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            margin: 10px 0 12px;
            text-transform: uppercase;
        }
        .info { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .info th, .info td {
            border: 1px solid #cbd5e1;
            padding: 5px 8px;
            font-size: 10px;
            vertical-align: top;
        }
        .info th {
            width: 28%;
            background: #ecfdf5;
            text-align: left;
            font-weight: bold;
            color: #14532d;
        }
        .section-title {
            font-size: 10px;
            font-weight: bold;
            color: #14532d;
            margin: 10px 0 6px;
        }
        .content-block {
            border: 1px solid #e5e7eb;
            padding: 8px;
            margin-bottom: 8px;
            min-height: 40px;
            white-space: pre-wrap;
        }
        .items { width: 100%; border-collapse: collapse; margin: 8px 0 12px; }
        .items th, .items td {
            border: 1px solid #cbd5e1;
            padding: 4px 6px;
            font-size: 9px;
        }
        .items th { background: #ecfdf5; color: #14532d; }
        .items tfoot td { font-weight: bold; background: #f9fafb; }
        .signature {
            margin-top: 24px;
            width: 100%;
        }
        .signature td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 10px;
            padding-top: 8px;
        }
        .signature .name {
            margin-top: 48px;
            font-weight: bold;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="header-wrap">
        @if ($logoBase64)
            <div class="header-logo"><img src="{{ $logoBase64 }}" alt="Logo"></div>
        @endif
        <div class="header-org">PEMERINTAH KOTA BATAM</div>
        <div class="header-dept">DINAS PERUMAHAN, KAWASAN PERMUKIMAN DAN PERTANAMAN</div>
        <div class="header-title">{{ \App\Support\DpaMonitoring::KEGIATAN_UTAMA }}</div>
    </div>

    <div class="doc-title">{{ $judul }}</div>

    <table class="info">
        <tr>
            <th>Tahun Anggaran</th>
            <td>{{ $paket->dpa->tahunAnggaran->tahun ?? '—' }}</td>
        </tr>
        <tr>
            <th>Nama Paket Pekerjaan</th>
            <td>{{ $paket->nama_paket }}</td>
        </tr>
        <tr>
            <th>Sub Kegiatan</th>
            <td>{{ $paket->dpa->subKegiatanLabel() }}</td>
        </tr>
        <tr>
            <th>Pagu Anggaran</th>
            <td>Rp {{ number_format($paket->pagu_anggaran, 0, ',', '.') }}</td>
        </tr>
        @if ($paket->penyedia)
            <tr>
                <th>Penyedia</th>
                <td>{{ $paket->penyedia->nama }}@if ($paket->penyedia->pic) (PIC: {{ $paket->penyedia->pic }})@endif</td>
            </tr>
        @endif
        @foreach ($fields as $field)
            @php $value = $input[$field['name']] ?? null; @endphp
            @if (filled($value))
                <tr>
                    <th>{{ $field['label'] }}</th>
                    <td>
                        @if ($field['type'] === 'number')
                            Rp {{ number_format((int) $value, 0, ',', '.') }}
                        @elseif ($field['type'] === 'date')
                            {{ \Illuminate\Support\Carbon::parse($value)->translatedFormat('d F Y') }}
                        @else
                            {{ $value }}
                        @endif
                    </td>
                </tr>
            @endif
        @endforeach
    </table>

    @foreach ($fields as $field)
        @if ($field['type'] === 'textarea' && filled($input[$field['name']] ?? null))
            <div class="section-title">{{ $field['label'] }}</div>
            <div class="content-block">{{ $input[$field['name']] }}</div>
        @endif
    @endforeach

    @if ($items->isNotEmpty())
        <div class="section-title">Rincian Item</div>
        <table class="items">
            <thead>
                <tr>
                    <th style="width:5%">No</th>
                    <th>Uraian</th>
                    <th style="width:10%">Vol</th>
                    <th style="width:10%">Satuan</th>
                    <th style="width:15%">Harga Satuan</th>
                    <th style="width:15%">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $index => $item)
                    <tr>
                        <td style="text-align:center">{{ $index + 1 }}</td>
                        <td>{{ $item->uraian }}</td>
                        <td style="text-align:right">{{ number_format((float) $item->volume, 2, ',', '.') }}</td>
                        <td>{{ $item->satuan }}</td>
                        <td style="text-align:right">{{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                        <td style="text-align:right">{{ number_format($item->jumlah, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" style="text-align:right">Total</td>
                    <td style="text-align:right">Rp {{ number_format($items->sum('jumlah'), 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    @endif

    @if (filled($input['pejabat_nama'] ?? null))
        <table class="signature">
            <tr>
                <td></td>
                <td>Batam, {{ filled($input['tanggal'] ?? null) ? \Illuminate\Support\Carbon::parse($input['tanggal'])->translatedFormat('d F Y') : '………………' }}</td>
            </tr>
            <tr>
                <td></td>
                <td>{{ $input['pejabat_jabatan'] ?? 'Pejabat' }}</td>
            </tr>
            <tr>
                <td></td>
                <td><div class="name">{{ $input['pejabat_nama'] }}</div></td>
            </tr>
        </table>
    @endif
</body>
</html>
