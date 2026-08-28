@extends('layouts.admin')

@section('title', 'Import Taman')
@section('header', 'Import Taman (CSV)')

@section('content')
    <div class="max-w-3xl space-y-6">
        <div class="rounded-xl border border-blue-100 bg-blue-50 p-5 text-sm text-blue-900">
            <p class="font-semibold">Import massal dari spreadsheet</p>
            <p class="mt-2 leading-relaxed">
                Unggah file CSV berisi ratusan taman sekaligus. Foto tidak disertakan di CSV —
                unggah foto per taman setelah import selesai.
            </p>
            <a href="{{ route('admin.tamans.import.template') }}"
               class="mt-4 inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                Unduh Template CSV
            </a>
        </div>

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($warning = session('warning'))
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                {{ $warning }}
            </div>
        @endif

        @if ($result = session('import_result'))
            <div class="rounded-xl border p-5 text-sm {{ (($result['imported'] ?? 0) + ($result['updated'] ?? 0)) > 0 ? 'border-emerald-200 bg-emerald-50 text-emerald-900' : 'border-amber-200 bg-amber-50 text-amber-900' }}">
                <p class="font-semibold">{{ (($result['imported'] ?? 0) + ($result['updated'] ?? 0)) > 0 ? 'Import selesai' : 'Import gagal — tidak ada data masuk' }}</p>
                <ul class="mt-3 space-y-1">
                    <li><strong>{{ number_format($result['imported'] ?? 0) }}</strong> taman baru ditambahkan</li>
                    <li><strong>{{ number_format($result['updated'] ?? 0) }}</strong> taman diperbarui</li>
                    <li><strong>{{ number_format($result['skipped']) }}</strong> baris dilewati</li>
                </ul>
                @if (! empty($result['errors']))
                    <details class="mt-4" {{ (($result['imported'] ?? 0) + ($result['updated'] ?? 0)) === 0 ? 'open' : '' }}>
                        <summary class="cursor-pointer font-semibold text-amber-800">Lihat detail baris dilewati ({{ count($result['errors']) }})</summary>
                        <ul class="mt-2 max-h-64 space-y-1 overflow-y-auto text-xs text-amber-900">
                            @foreach ($result['errors'] as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </details>
                    @if (($result['imported'] ?? 0) + ($result['updated'] ?? 0) === 0)
                        <p class="mt-3 text-xs text-amber-800">
                            Periksa: nama taman wajib; kategori (jika diisi) harus persis
                            @foreach (\App\Models\Taman::KATEGORI as $kat)
                                <strong>{{ $kat }}</strong>@if (! $loop->last), @endif
                            @endforeach;
                            luasan harus angka bulat jika diisi.
                        </p>
                    @endif
                @endif
                <a href="{{ route('admin.tamans.index') }}"
                   class="mt-4 inline-flex rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                    Lihat Daftar Taman
                </a>
            </div>
        @endif

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-base font-semibold text-gray-900">Unggah File CSV</h3>
            <p class="mt-1 text-sm text-gray-600">
                Hanya <code class="rounded bg-gray-100 px-1">nama_taman</code> wajib diisi per baris.
                Kolom lain boleh dikosongkan — data akan masuk dengan status <strong>Belum Lengkap</strong>.
                Jika nama sudah ada di sistem, baris tersebut <strong>diperbarui</strong> (hanya kolom yang diisi di CSV).
                Wilayah isi lewat <code class="rounded bg-gray-100 px-1">kecamatan</code> + <code class="rounded bg-gray-100 px-1">kelurahan</code>
                <em>atau</em> <code class="rounded bg-gray-100 px-1">latitude</code> + <code class="rounded bg-gray-100 px-1">longitude</code> (otomatis).
                Kolom tambahan:
                <code class="rounded bg-gray-100 px-1">tahun_pembangunan</code>,
                <code class="rounded bg-gray-100 px-1">nilai_pembangunan</code>,
                <code class="rounded bg-gray-100 px-1">kontraktor</code>,
                <code class="rounded bg-gray-100 px-1">konsultan_perencana</code>,
                <code class="rounded bg-gray-100 px-1">data_verified_at</code>.
                Kategori (jika diisi): {{ implode(', ', \App\Models\Taman::KATEGORI) }}.
            </p>
            <p class="mt-2 text-xs text-gray-500">
                Fasilitas: pisahkan dengan koma atau titik koma. Format <code class="rounded bg-gray-100 px-1">Nama:Kondisi</code>
                (contoh: <code class="rounded bg-gray-100 px-1">Playground:Baik; Jogging Track:Rusak Ringan</code>).
                Kondisi: Baik, Rusak Ringan, Rusak Berat. Tanpa kondisi otomatis <strong>Baik</strong>.
            </p>
            <p class="mt-2 text-xs text-amber-700">
                Pastikan data wilayah sudah di-seed (<code>php artisan db:seed --class=WilayahBatamSeeder</code>).
                Nama kecamatan/kelurahan harus cocok dengan master (12 kecamatan, 64 kelurahan Kota Batam).
                Deteksi wilayah dari koordinat hanya dilakukan jika kolom latitude/longitude diisi di CSV
                (koordinat default tidak memicu pencarian eksternal).
            </p>

            <form action="{{ route('admin.tamans.import.store') }}" method="POST" enctype="multipart/form-data" class="mt-5">
                @csrf
                <label for="file" class="mb-2 block text-sm font-medium text-gray-700">File CSV *</label>
                <input type="file" name="file" id="file" accept=".csv,text/csv" required
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-green-50 file:px-3 file:py-1.5 file:text-green-700">
                <p class="mt-2 text-xs text-gray-500">
                    Template memakai pemisah titik koma (<code>;</code>) agar setiap kolom tampil terpisah di Microsoft Excel (locale Indonesia).
                    Jika mengedit di Excel, simpan kembali sebagai <strong>CSV (pemisah titik koma)(*.csv)</strong> — bukan CSV koma.
                    Baris pertama template berisi <code>sep=;</code>; jangan dihapus.
                    Maks. 4 MB · UTF-8 · baris duplikat (nama sama) otomatis dilewati.
                </p>

                <div class="mt-6 flex flex-wrap gap-3">
                    <button type="submit"
                            class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">
                        Mulai Import
                    </button>
                    <a href="{{ route('admin.tamans.index') }}"
                       class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
