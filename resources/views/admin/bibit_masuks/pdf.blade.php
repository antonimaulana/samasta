<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Stok Masuk #{{ $batch->id }}</title>
    @include('admin.partials.pdf_styles')
</head>
<body>
    <h1>Detail Stok Masuk Bibit</h1>
    <p class="meta">
        Dinas Perumahan, Kawasan Permukiman dan Pertamanan Kota Batam<br>
        Transaksi #{{ $batch->id }} · {{ \App\Support\OperasionalPelaksanaanTime::display($batch->tanggal_masuk) }}
    </p>

    <table>
        <tr>
            <th style="width:28%">Sumber</th>
            <td>{{ $batch->sumber ?? $batch->items->first()?->sumber ?? '—' }}</td>
        </tr>
        <tr>
            <th>Siap tanam</th>
            <td>{{ ($batch->status_siap_tanam ?? $batch->items->first()?->status_siap_tanam) ? 'Ya' : 'Belum' }}</td>
        </tr>
        <tr>
            <th>Total masuk</th>
            <td><strong>+{{ $batch->totalJumlah() }}</strong> bibit · {{ $batch->items->count() }} jenis</td>
        </tr>
    </table>

    <h2>Rincian Bibit</h2>
    <table>
        <thead>
            <tr>
                <th>Nama Tanaman</th>
                <th>Jenis</th>
                <th class="num">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($batch->items as $item)
                <tr>
                    <td><x-admin.bibit-nama :bibit="$item->bibit" pdf /></td>
                    <td>{{ $item->bibit->jenis }}</td>
                    <td class="num">+{{ number_format($item->jumlah) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dicetak otomatis dari {{ config('app.name') }} · {{ now()->format('d/m/Y H:i') }} WIB
    </div>
</body>
</html>
