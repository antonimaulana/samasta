<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Stok Keluar #{{ $batch->id }}</title>
    @include('admin.partials.pdf_styles')
</head>
<body>
    <h1>Detail Stok Keluar Bibit</h1>
    <p class="meta">
        Dinas Perumahan, Kawasan Permukiman dan Pertamanan Kota Batam<br>
        Transaksi #{{ $batch->id }} · {{ \App\Support\OperasionalPelaksanaanTime::display($batch->tanggal_keluar) }}
    </p>

    <table>
        <tr>
            <th style="width:28%">Peruntukan</th>
            <td>{{ $batch->peruntukan }}</td>
        </tr>
        @if ($batch->requiresLokasi())
            <tr>
                <th>Lokasi</th>
                <td>{{ $batch->lokasiLabel() }}</td>
            </tr>
        @endif
        <tr>
            <th>Total keluar</th>
            <td><strong>-{{ $batch->totalJumlah() }}</strong> bibit · {{ $batch->items->count() }} jenis</td>
        </tr>
    </table>

    <h2>Rincian Bibit</h2>
    <table>
        <thead>
            <tr>
                <th>Nama Tanaman</th>
                <th>Jenis</th>
                <th>Sumber</th>
                <th class="num">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($batch->items as $item)
                <tr>
                    <td><x-admin.bibit-nama :bibit="$item->bibit" pdf /></td>
                    <td>{{ $item->bibit->jenis }}</td>
                    <td>{{ $item->sumber ?: '—' }}</td>
                    <td class="num">-{{ number_format($item->jumlah) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dicetak otomatis dari {{ config('app.name') }} · {{ now()->format('d/m/Y H:i') }} WIB
    </div>
</body>
</html>
