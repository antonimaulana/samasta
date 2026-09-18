@extends('layouts.admin')

@section('title', 'Anggota '.$timPelaksana->nama)
@section('header', 'Anggota Tim — '.$timPelaksana->nama)

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.tim-pelaksanas.index') }}" class="text-sm text-green-700 hover:underline">← Kembali ke Tim Pelaksana</a>
        <p class="mt-2 text-sm text-gray-600">
            Daftar petugas untuk pemilihan saat input pemeliharaan &amp; permohonan.
            Ketik sebagian nama di form lapangan/admin untuk memilih petugas.
        </p>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-8 overflow-hidden rounded-2xl border border-green-100 bg-white shadow-sm">
        <div class="border-b border-green-50 bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-4">
            <h2 class="text-lg font-semibold text-green-800">Tambah Petugas</h2>
        </div>
        <form action="{{ route('admin.tim-pelaksanas.petugas.store', $timPelaksana) }}" method="POST" class="grid gap-4 p-6 md:grid-cols-2 lg:grid-cols-4">
            @csrf
            <div class="lg:col-span-2">
                <label for="nama" class="mb-1 block text-sm font-medium text-gray-700">Nama *</label>
                <input type="text" name="nama" id="nama" required value="{{ old('nama') }}"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
            </div>
            <div>
                <label for="jabatan" class="mb-1 block text-sm font-medium text-gray-700">Jabatan</label>
                <input type="text" name="jabatan" id="jabatan" value="{{ old('jabatan') }}" placeholder="Mandor, Operator..."
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">
                    Tambah
                </button>
            </div>
        </form>
    </div>

    <x-admin.data-table>
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Nama</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Jabatan</th>
                <th class="px-4 py-3 text-center text-sm font-medium text-gray-700">Pengawas</th>
                <th class="px-4 py-3 text-center text-sm font-medium text-gray-700">Aktif</th>
                <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($petugas as $row)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $row->nama }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $row->jabatan ?? '—' }}</td>
                    <td class="px-4 py-3 text-center text-sm">{{ $row->is_pengawas ? '✓' : '—' }}</td>
                    <td class="px-4 py-3 text-center">
                        <span @class([
                            'rounded-full px-2 py-0.5 text-xs font-semibold',
                            'bg-green-100 text-green-800' => $row->aktif,
                            'bg-gray-100 text-gray-600' => ! $row->aktif,
                        ])>{{ $row->aktif ? 'Aktif' : 'Nonaktif' }}</span>
                    </td>
                    <td class="px-4 py-3 text-right text-sm">
                        <details class="inline-block text-left">
                            <summary class="cursor-pointer rounded border border-gray-300 px-3 py-1 text-gray-700 hover:bg-gray-50">Edit</summary>
                            <div class="absolute z-10 mt-2 w-72 rounded-xl border border-gray-200 bg-white p-4 shadow-lg">
                                <form action="{{ route('admin.tim-pelaksanas.petugas.update', [$timPelaksana, $row]) }}" method="POST" class="space-y-3">
                                    @csrf
                                    @method('PUT')
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-gray-700">Nama</label>
                                        <input type="text" name="nama" value="{{ $row->nama }}" required
                                               class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-gray-700">Jabatan</label>
                                        <input type="text" name="jabatan" value="{{ $row->jabatan }}"
                                               class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
                                    </div>
                                    <label class="flex items-center gap-2 text-xs text-gray-700">
                                        <input type="checkbox" name="is_pengawas" value="1" @checked($row->is_pengawas) class="rounded border-gray-300 text-green-600">
                                        Pengawas
                                    </label>
                                    <label class="flex items-center gap-2 text-xs text-gray-700">
                                        <input type="checkbox" name="aktif" value="1" @checked($row->aktif) class="rounded border-gray-300 text-green-600">
                                        Aktif
                                    </label>
                                    <button type="submit" class="w-full rounded-lg bg-green-600 px-3 py-1.5 text-xs font-semibold text-white">Simpan</button>
                                </form>
                                <form action="{{ route('admin.tim-pelaksanas.petugas.destroy', [$timPelaksana, $row]) }}" method="POST" class="mt-2"
                                      onsubmit="return confirm('Hapus petugas ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50">Hapus</button>
                                </form>
                            </div>
                        </details>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada petugas. Tambahkan minimal satu anggota tim.</td>
                </tr>
            @endforelse
        </tbody>
    </x-admin.data-table>
@endsection
