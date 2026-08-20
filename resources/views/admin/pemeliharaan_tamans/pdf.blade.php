<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Operasional Pemeliharaan Taman</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; margin: 24px; }
        h1 { font-size: 18px; margin: 0 0 4px; color: #166534; }
        h2 { font-size: 13px; margin: 20px 0 8px; color: #15803d; border-bottom: 2px solid #bbf7d0; padding-bottom: 4px; }
        .meta { font-size: 10px; color: #6b7280; margin-bottom: 16px; }
        .badge { display: inline-block; background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #e5e7eb; padding: 6px 8px; vertical-align: top; }
        th { background: #f0fdf4; text-align: left; font-size: 10px; }
        .foto-group { margin-bottom: 4px; }
        .foto-group-label { font-size: 8px; font-weight: bold; color: #166534; margin-bottom: 2px; text-transform: uppercase; }
        .foto-wrap { display: inline-block; width: 48%; vertical-align: top; text-align: center; padding: 2px; }
        .foto-wrap img { border: 1px solid #d1d5db; display: inline-block; }
        .footer { margin-top: 24px; font-size: 9px; color: #9ca3af; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 8px; }
        .empty { color: #9ca3af; font-style: italic; padding: 12px; }
        .sopir-name { font-weight: 300; font-style: italic; }
    </style>
</head>
<body>
    @php
        $fotoGroups = [
            'Sebelum' => ['foto_sebelum_1', 'foto_sebelum_2'],
            'Saat' => ['foto_saat_1', 'foto_saat_2'],
            'Sesudah' => ['foto_sesudah_1', 'foto_sesudah_2'],
        ];
    @endphp

    @php
        $periodeSatuHari = $tanggalMulai->isSameDay($tanggalSelesai);
    @endphp

    <h1>Laporan Operasional Pemeliharaan Taman</h1>
    <p class="meta">
        Dinas Perumahan, Kawasan Permukiman dan Pertamanan Kota Batam<br>
        @if ($periodeSatuHari)
            Tanggal: <strong>{{ $tanggalMulai->translatedFormat('d F Y') }}</strong>
        @else
            Periode: <strong>{{ $tanggalMulai->translatedFormat('d M Y') }} – {{ $tanggalSelesai->translatedFormat('d M Y') }}</strong>
        @endif
        @if ($timFilter)
            · Tim: <span class="badge">{{ $timFilter }}</span>
        @else
            · <span class="badge">Semua Tim</span>
        @endif
        · Total: <strong>{{ $totalOperasional }}</strong> lokasi
    </p>

    @forelse ($kinerjasPerTim as $tim => $items)
        <h2>{{ $items->first()?->namaPengawas() ?? $tim }} ({{ $items->count() }} operasional)</h2>
        <table>
            <thead>
                <tr>
                    <th style="width:4%">No</th>
                    @unless ($periodeSatuHari)
                        <th style="width:10%">Tanggal</th>
                    @endunless
                    <th style="width:18%">Lokasi Pelaksanaan</th>
                    <th style="width:26%">Sebelum Pelaksanaan</th>
                    <th style="width:26%">Saat Pelaksanaan</th>
                    <th style="width:26%">Sesudah Pelaksanaan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        @unless ($periodeSatuHari)
                            <td>{{ $item->tanggal->translatedFormat('d/m/Y') }}</td>
                        @endunless
                        <td>
                            ({{ $item->lokasiKategoriLabel() }}) {{ $item->lokasi_pelaksanaan }}
                            @if ($item->namaPengawas())
                                <br><span style="color:#6b7280;font-size:9px;">Pengawas: {{ $item->namaPengawas() }}</span>
                            @endif
                            @if ($item->jumlah_personil)
                                <br><span style="color:#6b7280;font-size:9px;">Personil: {{ $item->jumlah_personil }}</span>
                            @endif
                            @if ($item->progressSummaryLabel())
                                <br><span style="color:#6b7280;font-size:9px;">Progres: {{ $item->progressSummaryLabel() }}</span>
                            @endif
                            @if ($item->uraian_pekerjaan)
                                <br><span style="color:#6b7280;font-size:9px;">{{ $item->uraian_pekerjaan }}</span>
                            @endif
                            @if ($item->armadaPdfHtml())
                                <br><span style="color:#6b7280;font-size:9px;">Armada: {!! $item->armadaPdfHtml() !!}</span>
                            @endif
                        </td>
                        @foreach ($fotoGroups as $groupLabel => $fields)
                            <td>
                                @php $hasFoto = collect($fields)->contains(fn ($field) => $item->fotoBase64($field)); @endphp
                                @if ($hasFoto)
                                    <div class="foto-group">
                                        <div class="foto-group-label">{{ $groupLabel }}</div>
                                        @foreach ($fields as $field)
                                            @if ($item->fotoBase64($field))
                                                @php $size = $item->fotoPdfSize($field, 115, 68); @endphp
                                                <div class="foto-wrap">
                                                    @if ($size)
                                                        <img src="{{ $item->fotoBase64($field) }}"
                                                             alt="{{ $groupLabel }}"
                                                             width="{{ $size['width'] }}"
                                                             height="{{ $size['height'] }}">
                                                    @endif
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <span class="empty">—</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @empty
        <p class="empty">Tidak ada data operasional pemeliharaan taman pada periode ini.</p>
    @endforelse

    <div class="footer">
        Dicetak otomatis dari Sistem {{ config('app.name') }} · {{ now()->format('d/m/Y H:i') }} WIB
    </div>
</body>
</html>
