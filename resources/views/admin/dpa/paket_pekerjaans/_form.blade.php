@php
    $isEdit = isset($paketPekerjaan);
    $action = $isEdit
        ? route('admin.dpa.paket-pekerjaans.update', $paketPekerjaan)
        : route('admin.dpa.paket-pekerjaans.store', $dpa);
@endphp

<form action="{{ $action }}" method="POST" class="space-y-4">
    @csrf
    @if ($isEdit) @method('PUT') @endif

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="nomor_rekening" class="mb-1 block text-sm font-medium text-gray-700">Nomor Rekening</label>
            <input type="text" name="nomor_rekening" id="nomor_rekening"
                   value="{{ old('nomor_rekening', $paketPekerjaan->nomor_rekening ?? '') }}"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
        </div>
        <div>
            <label for="nama_rekening" class="mb-1 block text-sm font-medium text-gray-700">Nama Rekening</label>
            <input type="text" name="nama_rekening" id="nama_rekening"
                   value="{{ old('nama_rekening', $paketPekerjaan->nama_rekening ?? '') }}"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
        </div>
    </div>

    <div>
        <label for="nama_paket" class="mb-1 block text-sm font-medium text-gray-700">Nama Paket Pekerjaan *</label>
        <input type="text" name="nama_paket" id="nama_paket" required
               value="{{ old('nama_paket', $paketPekerjaan->nama_paket ?? '') }}"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
        @error('nama_paket')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="pagu_anggaran" class="mb-1 block text-sm font-medium text-gray-700">Pagu Anggaran (Rp) *</label>
            <input type="number" name="pagu_anggaran" id="pagu_anggaran" required min="0"
                   value="{{ old('pagu_anggaran', $paketPekerjaan->pagu_anggaran ?? 0) }}"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
        </div>
        <div>
            <label for="anggaran_kas" class="mb-1 block text-sm font-medium text-gray-700">Anggaran Kas (Rp)</label>
            <input type="number" name="anggaran_kas" id="anggaran_kas" min="0"
                   value="{{ old('anggaran_kas', $paketPekerjaan->anggaran_kas ?? '') }}"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
        </div>
    </div>

    <div>
        <label for="rincian_item_belanja" class="mb-1 block text-sm font-medium text-gray-700">Rincian Item Belanja</label>
        <textarea name="rincian_item_belanja" id="rincian_item_belanja" rows="2"
                  class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">{{ old('rincian_item_belanja', $paketPekerjaan->rincian_item_belanja ?? '') }}</textarea>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="kode_rup" class="mb-1 block text-sm font-medium text-gray-700">Kode RUP</label>
            <input type="text" name="kode_rup" id="kode_rup"
                   value="{{ old('kode_rup', $paketPekerjaan->kode_rup ?? '') }}"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
        </div>
        <div>
            <label for="masa_pelaksanaan" class="mb-1 block text-sm font-medium text-gray-700">Masa Pelaksanaan</label>
            <input type="text" name="masa_pelaksanaan" id="masa_pelaksanaan"
                   value="{{ old('masa_pelaksanaan', $paketPekerjaan->masa_pelaksanaan ?? '') }}"
                   placeholder="Januari - Desember 2026"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="jenis_pengadaan" class="mb-1 block text-sm font-medium text-gray-700">Jenis Pengadaan</label>
            <input type="text" name="jenis_pengadaan" id="jenis_pengadaan"
                   value="{{ old('jenis_pengadaan', $paketPekerjaan->jenis_pengadaan ?? '') }}"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
        </div>
        <div>
            <label for="metode_pemilihan" class="mb-1 block text-sm font-medium text-gray-700">Metode Pemilihan</label>
            <input type="text" name="metode_pemilihan" id="metode_pemilihan"
                   value="{{ old('metode_pemilihan', $paketPekerjaan->metode_pemilihan ?? '') }}"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
        </div>
    </div>

    <div>
        <label for="dpa_penyedia_id" class="mb-1 block text-sm font-medium text-gray-700">Penyedia</label>
        <select name="dpa_penyedia_id" id="dpa_penyedia_id"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
            <option value="">— Belum ditetapkan —</option>
            @foreach ($penyedias as $penyedia)
                <option value="{{ $penyedia->id }}" @selected(old('dpa_penyedia_id', $paketPekerjaan->dpa_penyedia_id ?? '') == $penyedia->id)>
                    {{ $penyedia->nama }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">Simpan</button>
        <a href="{{ $isEdit ? route('admin.dpa.paket-pekerjaans.show', $paketPekerjaan) : route('admin.dpa.dpas.show', $dpa) }}"
           class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</a>
    </div>
</form>
