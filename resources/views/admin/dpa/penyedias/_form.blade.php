@php
    $isEdit = isset($penyedia);
    $action = $isEdit ? route('admin.dpa.penyedias.update', $penyedia) : route('admin.dpa.penyedias.store');
@endphp

<form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="space-y-4">
    @csrf
    @if ($isEdit) @method('PUT') @endif

    <div>
        <label for="nama" class="mb-1 block text-sm font-medium text-gray-700">Nama Penyedia *</label>
        <input type="text" name="nama" id="nama" required value="{{ old('nama', $penyedia->nama ?? '') }}"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
        @error('nama')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="pic" class="mb-1 block text-sm font-medium text-gray-700">PIC</label>
            <input type="text" name="pic" id="pic" value="{{ old('pic', $penyedia->pic ?? '') }}"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
        </div>
        <div>
            <label for="jabatan" class="mb-1 block text-sm font-medium text-gray-700">Jabatan</label>
            <input type="text" name="jabatan" id="jabatan" value="{{ old('jabatan', $penyedia->jabatan ?? '') }}"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="npwp" class="mb-1 block text-sm font-medium text-gray-700">No. NPWP</label>
            <input type="text" name="npwp" id="npwp" value="{{ old('npwp', $penyedia->npwp ?? '') }}"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
        </div>
        <div>
            <label for="no_rekening" class="mb-1 block text-sm font-medium text-gray-700">No. Rekening</label>
            <input type="text" name="no_rekening" id="no_rekening" value="{{ old('no_rekening', $penyedia->no_rekening ?? '') }}"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
        </div>
    </div>

    <div>
        <label for="company_profile" class="mb-1 block text-sm font-medium text-gray-700">Company Profile (teks)</label>
        <textarea name="company_profile" id="company_profile" rows="4"
                  class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">{{ old('company_profile', $penyedia->company_profile ?? '') }}</textarea>
    </div>

    <div>
        <label for="company_profile_file" class="mb-1 block text-sm font-medium text-gray-700">File Company Profile</label>
        @if ($isEdit && $penyedia->company_profile_file)
            <p class="mb-2 text-xs text-gray-500">
                File saat ini: <a href="{{ asset($penyedia->company_profile_file) }}" target="_blank" class="text-green-700">Lihat</a>
            </p>
        @endif
        <input type="file" name="company_profile_file" id="company_profile_file"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-green-50 file:px-3 file:py-1.5 file:text-green-700">
    </div>

    <div class="flex gap-3">
        <button type="submit" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">Simpan</button>
        <a href="{{ route('admin.dpa.penyedias.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</a>
    </div>
</form>
