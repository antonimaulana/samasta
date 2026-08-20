@extends('layouts.admin')

@section('title', $paketPekerjaan->nama_paket)
@section('header', 'Monitoring Paket Pekerjaan')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.dpa.dpas.show', $paketPekerjaan->dpa) }}" class="text-sm text-green-700 hover:text-green-900">&larr; {{ $paketPekerjaan->dpa->nama_dpa }}</a>
        <h2 class="mt-2 text-lg font-semibold text-gray-900">{{ $paketPekerjaan->nama_paket }}</h2>
        <p class="text-sm text-gray-600">
            Pagu Rp {{ number_format($paketPekerjaan->pagu_anggaran, 0, ',', '.') }}
            @if ($paketPekerjaan->kode_rup) · RUP {{ $paketPekerjaan->kode_rup }} @endif
        </p>
    </div>

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap gap-2">
            @foreach ($tahapOptions as $tahap)
                <a href="{{ route('admin.dpa.paket-pekerjaans.show', ['paket_pekerjaan' => $paketPekerjaan, 'tahap' => $tahap]) }}"
                   class="rounded-full px-4 py-1.5 text-sm font-medium {{ $activeTahap === $tahap ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    {{ \App\Support\DpaMonitoring::tahapLabel($tahap) }}
                </a>
            @endforeach
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.dpa.paket-pekerjaans.edit', $paketPekerjaan) }}"
               class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50">Edit Data</a>
            <form action="{{ route('admin.dpa.paket-pekerjaans.update-tahap', $paketPekerjaan) }}" method="POST" class="flex items-center gap-2">
                @csrf
                @method('PATCH')
                <select name="tahap" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
                    @foreach ($tahapOptions as $tahap)
                        <option value="{{ $tahap }}" @selected($paketPekerjaan->tahap === $tahap)>Set tahap: {{ \App\Support\DpaMonitoring::tahapLabel($tahap) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-lg bg-gray-800 px-3 py-1.5 text-sm text-white hover:bg-gray-900">Update</button>
            </form>
        </div>
    </div>

    <div class="mb-8 grid gap-4 lg:grid-cols-2">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm text-sm">
            <h3 class="font-semibold text-gray-900">Informasi Paket</h3>
            <dl class="mt-3 space-y-2">
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Rekening</dt><dd>{{ $paketPekerjaan->nomor_rekening ?: '—' }} {{ $paketPekerjaan->nama_rekening }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Jenis Pengadaan</dt><dd>{{ $paketPekerjaan->jenis_pengadaan ?: '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Metode</dt><dd>{{ $paketPekerjaan->metode_pemilihan ?: '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Masa Pelaksanaan</dt><dd>{{ $paketPekerjaan->masa_pelaksanaan ?: '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Penyedia</dt><dd>{{ $paketPekerjaan->penyedia?->nama ?: '—' }}</dd></div>
            </dl>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <h3 class="mb-4 font-semibold text-gray-900">Dokumen — {{ \App\Support\DpaMonitoring::tahapLabel($activeTahap) }}</h3>
            <div class="space-y-4">
                @foreach ($dokumenDefinitions as $def)
                    @php $doc = $dokumens->get($def['kode']); @endphp
                    @include('admin.dpa.paket_pekerjaans._dokumen-form', [
                        'def' => $def,
                        'doc' => $doc,
                        'paketPekerjaan' => $paketPekerjaan,
                        'activeTahap' => $activeTahap,
                    ])
                @endforeach
            </div>
        </div>
    </div>

    @if ($activeTahap === \App\Support\DpaMonitoring::TAHAP_PENGADAAN)
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <h3 class="font-semibold text-gray-900">Item Rinci HPS</h3>
            @if ($hpsItems->isNotEmpty())
                <table class="mt-4 min-w-full text-sm">
                    <thead><tr class="border-b text-left text-gray-600">
                        <th class="py-2 pr-4">Uraian</th><th class="py-2 pr-4">Vol</th><th class="py-2 pr-4">Satuan</th><th class="py-2 pr-4">Harga</th><th class="py-2 pr-4">Jumlah</th><th></th>
                    </tr></thead>
                    <tbody>
                        @foreach ($hpsItems as $item)
                            <tr class="border-b border-gray-100">
                                <td class="py-2 pr-4">{{ $item->uraian }}</td>
                                <td class="py-2 pr-4">{{ $item->volume }}</td>
                                <td class="py-2 pr-4">{{ $item->satuan }}</td>
                                <td class="py-2 pr-4">{{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                                <td class="py-2 pr-4">{{ number_format($item->jumlah, 0, ',', '.') }}</td>
                                <td class="py-2">
                                    <form action="{{ route('admin.dpa.paket-pekerjaans.hps-items.destroy', [$paketPekerjaan, $item]) }}" method="POST" onsubmit="return confirm('Hapus item?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs text-red-600 hover:underline">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
            <form action="{{ route('admin.dpa.paket-pekerjaans.hps-items.store', $paketPekerjaan) }}" method="POST" class="mt-4 grid gap-3 sm:grid-cols-5">
                @csrf
                <input type="text" name="uraian" placeholder="Uraian *" required class="rounded border px-2 py-1.5 text-sm sm:col-span-2">
                <input type="number" name="volume" placeholder="Volume *" step="0.01" min="0" required class="rounded border px-2 py-1.5 text-sm">
                <input type="text" name="satuan" placeholder="Satuan" class="rounded border px-2 py-1.5 text-sm">
                <input type="number" name="harga_satuan" placeholder="Harga satuan *" min="0" required class="rounded border px-2 py-1.5 text-sm">
                <button type="submit" class="rounded bg-green-600 px-3 py-1.5 text-sm text-white hover:bg-green-700 sm:col-span-5 sm:w-fit">+ Item HPS</button>
            </form>
        </div>
    @endif

    @if ($activeTahap === \App\Support\DpaMonitoring::TAHAP_KONTRAK)
        <div class="mb-8 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <h3 class="font-semibold text-gray-900">Item Rinci SPK / Kontrak</h3>
            @if ($spkItems->isNotEmpty())
                <table class="mt-4 min-w-full text-sm">
                    <thead><tr class="border-b text-left text-gray-600">
                        <th class="py-2 pr-4">Uraian</th><th class="py-2 pr-4">Vol</th><th class="py-2 pr-4">Satuan</th><th class="py-2 pr-4">Harga</th><th class="py-2 pr-4">Jumlah</th><th></th>
                    </tr></thead>
                    <tbody>
                        @foreach ($spkItems as $item)
                            <tr class="border-b border-gray-100">
                                <td class="py-2 pr-4">{{ $item->uraian }}</td>
                                <td class="py-2 pr-4">{{ $item->volume }}</td>
                                <td class="py-2 pr-4">{{ $item->satuan }}</td>
                                <td class="py-2 pr-4">{{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                                <td class="py-2 pr-4">{{ number_format($item->jumlah, 0, ',', '.') }}</td>
                                <td class="py-2">
                                    <form action="{{ route('admin.dpa.paket-pekerjaans.spk-items.destroy', [$paketPekerjaan, $item]) }}" method="POST" onsubmit="return confirm('Hapus item?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs text-red-600 hover:underline">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
            <form action="{{ route('admin.dpa.paket-pekerjaans.spk-items.store', $paketPekerjaan) }}" method="POST" class="mt-4 grid gap-3 sm:grid-cols-5">
                @csrf
                <input type="text" name="uraian" placeholder="Uraian *" required class="rounded border px-2 py-1.5 text-sm sm:col-span-2">
                <input type="number" name="volume" placeholder="Volume *" step="0.01" min="0" required class="rounded border px-2 py-1.5 text-sm">
                <input type="text" name="satuan" placeholder="Satuan" class="rounded border px-2 py-1.5 text-sm">
                <input type="number" name="harga_satuan" placeholder="Harga satuan *" min="0" required class="rounded border px-2 py-1.5 text-sm">
                <button type="submit" class="rounded bg-green-600 px-3 py-1.5 text-sm text-white hover:bg-green-700 sm:col-span-5 sm:w-fit">+ Item SPK</button>
            </form>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <h3 class="font-semibold text-gray-900">Progres Pekerjaan</h3>
            @forelse ($progresItems as $progres)
                <div class="mt-3 rounded-lg border border-gray-100 p-3 text-sm">
                    <p class="font-medium">{{ $progres->tanggal->format('d M Y') }} — {{ $progres->persentase }}%</p>
                    @if ($progres->keterangan)<p class="text-gray-600">{{ $progres->keterangan }}</p>@endif
                    @if ($progres->dokumentasi_path)
                        <a href="{{ asset($progres->dokumentasi_path) }}" target="_blank" class="text-xs text-green-700">Dokumentasi</a>
                    @endif
                </div>
            @empty
                <p class="mt-3 text-sm text-gray-500">Belum ada progres.</p>
            @endforelse
            <form action="{{ route('admin.dpa.paket-pekerjaans.progres.store', $paketPekerjaan) }}" method="POST" enctype="multipart/form-data" class="mt-4 grid gap-3 sm:grid-cols-2">
                @csrf
                <input type="date" name="tanggal" required class="rounded border px-2 py-1.5 text-sm">
                <input type="number" name="persentase" min="0" max="100" placeholder="Persentase *" required class="rounded border px-2 py-1.5 text-sm">
                <textarea name="keterangan" placeholder="Keterangan" rows="2" class="rounded border px-2 py-1.5 text-sm sm:col-span-2"></textarea>
                <input type="file" name="dokumentasi" class="text-sm sm:col-span-2">
                <button type="submit" class="rounded bg-green-600 px-3 py-1.5 text-sm text-white hover:bg-green-700 sm:w-fit">+ Progres</button>
            </form>
        </div>
    @endif

    @if ($activeTahap === \App\Support\DpaMonitoring::TAHAP_SELESAI)
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <h3 class="font-semibold text-gray-900">Output Hasil Pekerjaan</h3>
            @forelse ($outputItems as $output)
                <div class="mt-3 rounded-lg border border-gray-100 p-3 text-sm">
                    <p class="font-medium">{{ $output->judul }}</p>
                    @if ($output->keterangan)<p class="text-gray-600">{{ $output->keterangan }}</p>@endif
                    @if ($output->file_path)
                        <a href="{{ asset($output->file_path) }}" target="_blank" class="text-xs text-green-700">Unduh file</a>
                    @endif
                </div>
            @empty
                <p class="mt-3 text-sm text-gray-500">Belum ada output.</p>
            @endforelse
            <form action="{{ route('admin.dpa.paket-pekerjaans.outputs.store', $paketPekerjaan) }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-3">
                @csrf
                <input type="text" name="judul" placeholder="Judul output *" required class="w-full rounded border px-2 py-1.5 text-sm">
                <textarea name="keterangan" placeholder="Keterangan" rows="2" class="w-full rounded border px-2 py-1.5 text-sm"></textarea>
                <input type="file" name="file" class="text-sm">
                <button type="submit" class="rounded bg-green-600 px-3 py-1.5 text-sm text-white hover:bg-green-700">+ Output</button>
            </form>
        </div>
    @endif
@endsection
