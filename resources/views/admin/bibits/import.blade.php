@extends('layouts.admin')



@section('title', 'Import Bibit')

@section('header', 'Import Bibit (CSV / Excel)')



@section('content')

    <div class="max-w-3xl space-y-6">

        <div class="rounded-xl border border-teal-100 bg-teal-50 p-5 text-sm text-teal-900">

            <p class="font-semibold">Import massal data bibit + stok awal</p>

            <p class="mt-2 leading-relaxed">

                Unggah file CSV berisi data bibit sekaligus. Setiap baris akan membuat data bibit

                (jika belum ada) dan transaksi <strong>stok masuk</strong> sebagai stok awal.

                Foto bukti stok tidak disertakan — dapat ditambahkan manual setelah import.

            </p>

            <a href="{{ route('admin.bibits.import.template') }}"

               class="mt-4 inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">

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



        @if ($result = session('import_result'))

            <div class="rounded-xl border p-5 text-sm {{ ($result['imported'] ?? 0) > 0 ? 'border-emerald-200 bg-emerald-50 text-emerald-900' : 'border-amber-200 bg-amber-50 text-amber-900' }}">

                <p class="font-semibold">{{ ($result['imported'] ?? 0) > 0 ? 'Import selesai' : 'Import gagal — tidak ada data masuk' }}</p>

                <ul class="mt-3 space-y-1">

                    <li><strong>{{ number_format($result['imported']) }}</strong> baris berhasil diimport</li>

                    <li><strong>{{ number_format($result['skipped']) }}</strong> baris dilewati</li>

                </ul>

                @if (! empty($result['errors']))

                    <details class="mt-4" {{ ($result['imported'] ?? 0) === 0 ? 'open' : '' }}>

                        <summary class="cursor-pointer font-semibold text-amber-800">Lihat detail baris dilewati ({{ count($result['errors']) }})</summary>

                        <ul class="mt-2 max-h-64 space-y-1 overflow-y-auto text-xs text-amber-900">

                            @foreach ($result['errors'] as $error)

                                <li>{{ $error }}</li>

                            @endforeach

                        </ul>

                    </details>

                    @if (($result['imported'] ?? 0) === 0)

                        <p class="mt-3 text-xs text-amber-800">

                            Periksa: jenis harus persis

                            @foreach (\App\Models\Bibit::JENIS as $jenis)

                                <strong>{{ $jenis }}</strong>@if (! $loop->last), @endif

                            @endforeach;

                            stok awal harus angka bulat minimal 1.

                        </p>

                    @endif

                @endif

                <a href="{{ route('admin.bibits.index') }}"

                   class="mt-4 inline-flex rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">

                    Lihat Daftar Bibit

                </a>

            </div>

        @endif



        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

            <h3 class="text-base font-semibold text-gray-900">Unggah File CSV</h3>

            <p class="mt-1 text-sm text-gray-600">

                Kolom wajib:

                <code class="rounded bg-gray-100 px-1">nama_tanaman</code>,

                <code class="rounded bg-gray-100 px-1">jenis</code>,

                <code class="rounded bg-gray-100 px-1">stok_awal</code>.

                Kolom opsional:

                <code class="rounded bg-gray-100 px-1">nama_ilmiah</code>,

                <code class="rounded bg-gray-100 px-1">sumber</code> (default: Produksi),

                <code class="rounded bg-gray-100 px-1">tanggal_masuk</code> (default: hari ini),

                <code class="rounded bg-gray-100 px-1">status_siap_tanam</code> (0/1).

            </p>

            <p class="mt-2 text-xs text-gray-500">

                {{ count(\App\Models\Bibit::JENIS) }} jenis tanaman tersedia — lihat daftar lengkap di template CSV atau form tambah bibit.

                Sumber stok: {{ implode(', ', \App\Models\Bibit::SUMBER) }}.

            </p>

            <p class="mt-2 text-xs text-amber-700">

                Buka template langsung di Microsoft Excel (locale Indonesia), isi data, lalu simpan sebagai CSV UTF-8.

                Template memakai pemisah titik koma (<code>;</code>) agar setiap kolom tampil terpisah.

            </p>



            <form action="{{ route('admin.bibits.import.store') }}" method="POST" enctype="multipart/form-data" class="mt-5">

                @csrf

                <label for="file" class="mb-2 block text-sm font-medium text-gray-700">File CSV *</label>

                <input type="file" name="file" id="file" accept=".csv,text/csv" required

                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-green-50 file:px-3 file:py-1.5 file:text-green-700">

                <p class="mt-2 text-xs text-gray-500">

                    Maks. 4 MB · UTF-8 · jika bibit sudah ada, stok awal ditambahkan sebagai transaksi masuk baru.

                </p>



                <div class="mt-6 flex flex-wrap gap-3">

                    <button type="submit"

                            class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">

                        Mulai Import

                    </button>

                    <a href="{{ route('admin.bibits.index') }}"

                       class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">

                        Batal

                    </a>

                </div>

            </form>

        </div>

    </div>

@endsection

