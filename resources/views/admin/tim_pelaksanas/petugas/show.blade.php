@extends('layouts.admin')

@section('title', 'Riwayat Pelaksanaan — '.$petugas->nama)
@section('header', 'Riwayat Pelaksanaan: '.$petugas->nama)

@section('content')
    <div class="mb-6 flex flex-wrap items-center gap-4">
        <a href="{{ route('admin.tim-pelaksanas.show', $timPelaksana) }}" class="text-sm font-medium text-green-700 hover:underline">
            ← Kembali ke {{ $timPelaksana->nama }}
        </a>
    </div>

    <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-900">Profil petugas</h2>
        <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
            <div>
                <dt class="text-gray-500">Nama</dt>
                <dd class="font-medium text-gray-900">{{ $petugas->nama }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Tim kerja</dt>
                <dd class="font-medium text-gray-900">{{ $timPelaksana->nama }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">NIP</dt>
                <dd class="font-mono text-gray-800">{{ $petugas->nip ?: '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Jabatan</dt>
                <dd class="text-gray-800">{{ $petugas->jabatan ?: '—' }}</dd>
            </div>
        </dl>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-6 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Riwayat pelaksanaan pekerjaan</h2>
            <p class="mt-1 text-sm text-gray-500">
                Kegiatan pemeliharaan rutin dan progres permohonan yang tercatat melibatkan petugas ini.
            </p>
        </div>

        <x-admin.data-table>
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Tanggal</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Jenis</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Lokasi</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Tim</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Ringkasan</th>
                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($riwayat as $baris)
                    <tr class="hover:bg-gray-50">
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-800">
                            {{ $baris->tanggal?->format('d/m/Y H:i') ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-sm">
                            @if ($baris->jenis === 'pemeliharaan')
                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800">{{ $baris->jenis_label }}</span>
                            @else
                                <span class="rounded-full bg-sky-100 px-2 py-0.5 text-xs font-semibold text-sky-800">{{ $baris->jenis_label }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-800">{{ $baris->lokasi }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $baris->tim ?: '—' }}</td>
                        <td class="max-w-xs px-4 py-3 text-sm text-gray-600">{{ $baris->ringkasan ?: '—' }}</td>
                        <td class="px-4 py-3 text-right text-sm">
                            @if ($baris->detail_url)
                                <a href="{{ $baris->detail_url }}" class="font-medium text-green-700 hover:underline">Detail</a>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">
                            Belum ada riwayat pelaksanaan pekerjaan untuk petugas ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-admin.data-table>
    </div>
@endsection
