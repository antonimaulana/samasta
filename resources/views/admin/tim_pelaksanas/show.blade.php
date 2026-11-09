@extends('layouts.admin')

@section('title', 'Daftar Petugas — '.$timPelaksana->nama)
@section('header', 'Daftar Petugas: '.$timPelaksana->nama)

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.tim-pelaksanas.index') }}" class="text-sm font-medium text-green-700 hover:underline">
            ← Kembali ke tim kerja
        </a>
    </div>

    <div class="mb-6 grid gap-4 lg:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">Profil tim</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between gap-4 border-b border-gray-100 pb-3">
                    <dt class="text-gray-500">Nama tim</dt>
                    <dd class="font-medium text-gray-900">{{ $timPelaksana->nama }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-gray-100 pb-3">
                    <dt class="text-gray-500">Pengawas</dt>
                    <dd class="font-medium text-gray-900">{{ $timPelaksana->nama_pengawas ?: '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-gray-100 pb-3">
                    <dt class="text-gray-500">Wilayah kerja</dt>
                    <dd>
                        @if ($timPelaksana->memiliki_wilayah_kerja)
                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">Per kelurahan</span>
                        @else
                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">Lintas wilayah / khusus</span>
                        @endif
                    </dd>
                </div>
                @if ($timPelaksana->memiliki_wilayah_kerja && $timPelaksana->kelurahans->isNotEmpty())
                    <div>
                        <dt class="text-gray-500">Kelurahan ({{ $timPelaksana->kelurahans->count() }})</dt>
                        <dd class="mt-2 text-gray-800">
                            {{ $timPelaksana->kelurahans->sortBy('nama')->pluck('nama')->implode(', ') }}
                        </dd>
                    </div>
                @endif
            </dl>
        </div>

        <div class="rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-green-50 p-6 shadow-sm">
            <p class="text-sm font-medium text-emerald-800">Total anggota tercatat</p>
            <p class="mt-2 text-4xl font-bold text-emerald-900">{{ number_format($timPelaksana->petugas->count()) }}</p>
            <p class="mt-2 text-sm text-emerald-700/80">
                {{ number_format($timPelaksana->petugas->where('aktif', true)->count()) }} aktif
            </p>
        </div>
    </div>

    <x-admin.data-table>
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Nama</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">NIP</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Jabatan</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Peran</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Status</th>
                <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($timPelaksana->petugas as $petugas)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $petugas->nama }}</td>
                    <td class="px-4 py-3 font-mono text-sm text-gray-700">{{ $petugas->nip ?: '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $petugas->jabatan ?: '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                        @if ($petugas->is_pengawas)
                            <span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-800">Pengawas</span>
                        @elseif ($petugas->is_inti)
                            <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-800">Inti</span>
                        @else
                            <span class="text-gray-500">Anggota</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if ($petugas->aktif)
                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800">Aktif</span>
                        @else
                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600">Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right text-sm">
                        <x-admin.table-action-link
                            :href="route('admin.tim-pelaksanas.petugas.show', [$timPelaksana, $petugas])"
                            label="Riwayat"
                        />
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">Belum ada petugas terdaftar untuk tim ini.</td>
                </tr>
            @endforelse
        </tbody>
    </x-admin.data-table>

    <x-admin.can-manage-users>
        <div class="mt-6">
            <a href="{{ route('admin.tim-pelaksanas.edit', $timPelaksana) }}"
               class="inline-flex rounded-lg border border-blue-300 px-4 py-2 text-sm font-medium text-blue-700 hover:bg-blue-50">
                Ubah tim &amp; anggota
            </a>
        </div>
    </x-admin.can-manage-users>
@endsection
