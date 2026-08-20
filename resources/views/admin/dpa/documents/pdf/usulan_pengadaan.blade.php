<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Usulan Pengadaan — {{ $input['nama_pekerjaan'] ?? $paket->nama_paket }}</title>
    <style>
        @page { size: A4 portrait; margin: 20mm 18mm 18mm 25mm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            line-height: 1.55;
            color: #111;
        }
        .meta { margin-bottom: 14px; }
        .meta p { margin-bottom: 2px; }
        .recipient { margin: 14px 0; }
        .recipient p { margin-bottom: 2px; }
        .indent { padding-left: 28px; }
        .subject { margin: 14px 0; font-weight: bold; }
        .body-text {
            text-align: justify;
            margin-bottom: 12px;
        }
        .detail-list {
            margin: 0 0 14px 18px;
            padding: 0;
        }
        .detail-list li {
            margin-bottom: 4px;
            list-style: decimal;
        }
        .lampiran-list {
            margin: 0 0 14px 18px;
            padding: 0;
        }
        .lampiran-list li {
            margin-bottom: 3px;
            list-style: decimal;
        }
        .closing { margin-top: 10px; text-align: justify; }
        .signature {
            margin-top: 24px;
            width: 100%;
        }
        .signature td {
            width: 55%;
            text-align: left;
            vertical-align: top;
            font-size: 11px;
            line-height: 1.45;
        }
        .signature .spacer { width: 45%; }
        .signature .name {
            margin-top: 52px;
            font-weight: bold;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    @php
        $fmtDate = fn ($value) => filled($value)
            ? \Illuminate\Support\Carbon::parse($value)->translatedFormat('d F Y')
            : '………………';
        $fmtRupiah = fn ($value) => 'Rp '.number_format((int) $value, 0, ',', '.');
    @endphp

    <div class="meta">
        <p>Nomor &nbsp;&nbsp;&nbsp;&nbsp;: {{ $input['nomor_permohonan'] ?? '………………' }}</p>
        <p>Lampiran : {{ $input['jumlah_lampiran'] ?? '……' }} ( lembar )</p>
    </div>

    <div class="recipient">
        <p>Kepada Yth.</p>
        <p>Pejabat Pengadaan Barang dan Jasa</p>
        <p>Sekretariat Daerah Kota Batam</p>
        <p>Di-</p>
        <p class="indent">Batam</p>
    </div>

    <p class="subject">
        Perihal : Permintaan Pelaksanaan Pengadaan Barang/Jasa {{ $input['metode_pengadaan'] ?? '………………' }}
    </p>

    <p class="body-text">
        Sehubungan dengan Anggaran Pendapatan dan Belanja Daerah (APBD) Tahun Anggaran
        {{ $input['tahun_anggaran'] ?? '………………' }} yang telah ditetapkan, dan alokasi anggaran belanja yang
        diterima oleh Dinas Perumahan, Kawasan Permukiman dan Pertamanan Kota Batam, maka dengan ini kami
        mengajukan permohonan pelaksanaan pengadaan barang/jasa dengan data sebagai berikut:
    </p>

    <ol class="detail-list">
        <li>Nama Kegiatan &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $input['nama_kegiatan'] ?? '………………' }}</li>
        <li>Nama Sub Kegiatan : {{ $input['nama_sub_kegiatan'] ?? '………………' }}</li>
        <li>Tahun Anggaran &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $input['tahun_anggaran'] ?? '………………' }}</li>
        <li>Pagu Anggaran &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ isset($input['pagu_anggaran']) ? $fmtRupiah($input['pagu_anggaran']) : '………………' }}</li>
        <li>Kode Rekening &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $input['kode_rekening'] ?? '………………' }}</li>
        <li>Nama Pekerjaan &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $input['nama_pekerjaan'] ?? '………………' }}</li>
        <li>Kode RUP/SiRUP &nbsp;&nbsp;&nbsp;&nbsp;: {{ $input['kode_rup'] ?? '………………' }}</li>
        <li>Lokasi &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $input['lokasi'] ?? '………………' }}</li>
        <li>Waktu Pelaksanaan : {{ $input['tanggal_pelaksanaan'] ?? '………………' }}</li>
        <li>Jenis Kontrak &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $input['jenis_kontrak'] ?? '………………' }}</li>
    </ol>

    <p class="body-text">Sebagai bahan pertimbangan, bersama surat ini kami sampaikan lampiran sebagai berikut:</p>
    <ol class="lampiran-list">
        <li>Spesifikasi Teknis;</li>
        <li>Harga Perkiraan Sendiri (HPS);</li>
        <li>Dokumen Pelaksanaan Anggaran (DPA);</li>
        <li>Dokumen SiRUP/RUP.</li>
    </ol>

    <p class="closing">
        Demikian permohonan ini kami sampaikan. Atas perhatian dan kerjasamanya kami ucapkan terima kasih.
    </p>

    <table class="signature">
        <tr>
            <td class="spacer"></td>
            <td>Batam, {{ $fmtDate($input['tanggal_permohonan'] ?? null) }}</td>
        </tr>
        <tr>
            <td class="spacer"></td>
            <td>Pejabat Pembuat Komitmen Sub Kegiatan {{ $input['sub_kegiatan'] ?? '………………' }}</td>
        </tr>
        <tr>
            <td class="spacer"></td>
            <td>Disperakimtan Kota Batam</td>
        </tr>
        <tr>
            <td class="spacer"></td>
            <td><div class="name">{{ $input['nama_ppk'] ?? '………………' }}</div></td>
        </tr>
        <tr>
            <td class="spacer"></td>
            <td>NIP. {{ $input['nip_ppk'] ?? '………………' }}</td>
        </tr>
    </table>
</body>
</html>
