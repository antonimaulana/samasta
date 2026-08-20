@extends('layouts.admin')

@section('title', 'Detail Aduan')
@section('header', 'Detail Aduan Masyarakat')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.aduan-masyarakats.index') }}" class="text-sm text-green-700 hover:underline">← Kembali ke daftar aduan</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="font-mono text-sm font-bold text-gray-500">{{ $aduan->nomor_aduan }}</p>
                        <h2 class="mt-1 text-xl font-bold text-gray-900">{{ $aduan->jenis_aduan }}</h2>
                        <p class="mt-1 text-sm text-gray-500">Diterima {{ $aduan->created_at->translatedFormat('d F Y, H:i') }} WIB</p>
                    </div>
                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ \App\Models\AduanMasyarakat::statusBadgeClass($aduan->status) }}">
                        {{ $aduan->status }}
                    </span>
                </div>

                <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-500">Lokasi</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $aduan->lokasi }}</dd>
                    </div>
                    @if ($aduan->taman)
                        <div>
                            <dt class="text-xs font-semibold uppercase text-gray-500">Taman terdaftar</dt>
                            <dd class="mt-1 font-medium text-gray-900">{{ $aduan->taman->nama_taman }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-500">Pelapor</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $aduan->nama_pelapor }}</dd>
                        @if ($aduan->kontak_pelapor)
                            <dd class="text-sm text-gray-600">{{ $aduan->kontak_pelapor }}</dd>
                        @endif
                    </div>
                    @if ($aduan->latitude && $aduan->longitude)
                        <div>
                            <dt class="text-xs font-semibold uppercase text-gray-500">Koordinat GPS</dt>
                            <dd class="mt-1 text-sm">
                                <a href="https://www.google.com/maps?q={{ $aduan->latitude }},{{ $aduan->longitude }}" target="_blank" rel="noopener"
                                   class="font-medium text-green-700 hover:underline">
                                    {{ $aduan->latitude }}, {{ $aduan->longitude }} → Maps
                                </a>
                            </dd>
                        </div>
                    @endif
                </dl>

                <div class="mt-6">
                    <h3 class="text-sm font-semibold text-gray-700">Uraian aduan</h3>
                    <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-gray-700">{{ $aduan->deskripsi }}</p>
                </div>

                @if ($aduan->foto_url)
                    <div class="mt-6">
                        <h3 class="text-sm font-semibold text-gray-700">Foto bukti</h3>
                        <a href="{{ $aduan->foto_url }}" target="_blank" rel="noopener">
                            <img src="{{ $aduan->foto_url }}" alt="Foto aduan" class="mt-2 max-h-80 rounded-lg border object-cover">
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <x-admin.can-write>
            <form method="POST" action="{{ route('admin.aduan-masyarakats.update', $aduan) }}"
                  class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                @csrf
                @method('PUT')

                <h3 class="font-bold text-gray-900">Update Status</h3>

                <div class="mt-4">
                    <label for="status" class="mb-1 block text-sm font-medium text-gray-700">Status *</label>
                    <select name="status" id="status" required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                        @foreach (\App\Models\AduanMasyarakat::STATUS as $status)
                            <option value="{{ $status }}" @selected(old('status', $aduan->status) === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mt-4">
                    <label for="catatan_admin" class="mb-1 block text-sm font-medium text-gray-700">Catatan admin</label>
                    <textarea name="catatan_admin" id="catatan_admin" rows="4"
                              placeholder="Catatan tindak lanjut internal..."
                              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">{{ old('catatan_admin', $aduan->catatan_admin) }}</textarea>
                </div>

                <button type="submit" class="mt-4 w-full rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                    Simpan Perubahan
                </button>
            </form>

            <x-admin.can-delete>
            <form method="POST" action="{{ route('admin.aduan-masyarakats.destroy', $aduan) }}"
                  onsubmit="return confirm('Hapus aduan ini permanen?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50">
                    Hapus Aduan
                </button>
            </form>
            </x-admin.can-delete>
            </x-admin.can-write>

            @unless ($canWrite ?? auth()->user()?->canWrite())
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="font-bold text-gray-900">Status</h3>
                <p class="mt-2">
                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ \App\Models\AduanMasyarakat::statusBadgeClass($aduan->status) }}">
                        {{ $aduan->status }}
                    </span>
                </p>
                @if ($aduan->catatan_admin)
                    <h3 class="mt-4 font-bold text-gray-900">Catatan admin</h3>
                    <p class="mt-2 whitespace-pre-line text-sm text-gray-700">{{ $aduan->catatan_admin }}</p>
                @endif
            </div>
            @endunless
        </div>
    </div>
@endsection
