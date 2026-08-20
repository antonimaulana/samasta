@extends('layouts.admin')

@section('title', 'Aduan Masyarakat')
@section('header', 'Aduan Masyarakat')

@section('content')
    <div class="mb-6">
        <p class="text-sm text-gray-600">Kelola laporan masyarakat tentang kondisi taman, tanaman rusak, atau berbahaya.</p>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-5">
        @foreach ($ringkasanStatus as $status => $jumlah)
            <div class="rounded-xl border border-gray-200 bg-white p-3 text-center shadow-sm">
                <p class="text-xs font-bold text-gray-500">{{ $status }}</p>
                <p class="mt-1 text-2xl font-black text-green-700">{{ $jumlah }}</p>
            </div>
        @endforeach
    </div>

    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3">
        <div class="min-w-[200px] flex-1 sm:max-w-md">
            <label for="search" class="mb-1 block text-xs font-medium text-gray-600">Cari</label>
            <input type="search" name="search" id="search" value="{{ request('search') }}"
                   placeholder="Nomor aduan, lokasi, pelapor..."
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
        </div>
        <div>
            <label for="status" class="mb-1 block text-xs font-medium text-gray-600">Status</label>
            <select name="status" id="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <option value="">Semua</option>
                @foreach (\App\Models\AduanMasyarakat::STATUS as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="jenis" class="mb-1 block text-xs font-medium text-gray-600">Jenis</label>
            <select name="jenis" id="jenis" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <option value="">Semua</option>
                @foreach (\App\Models\AduanMasyarakat::JENIS as $jenis)
                    <option value="{{ $jenis }}" @selected(request('jenis') === $jenis)>{{ $jenis }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">Terapkan</button>
        @if (request()->anyFilled(['search', 'status', 'jenis']))
            <a href="{{ route('admin.aduan-masyarakats.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Reset</a>
        @endif
    </form>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">No. Aduan</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Jenis</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Lokasi</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Pelapor</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($aduans as $aduan)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <p class="font-mono text-xs font-bold text-gray-900">{{ $aduan->nomor_aduan }}</p>
                                <p class="text-xs text-gray-500">{{ $aduan->created_at->format('d M Y H:i') }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ \App\Models\AduanMasyarakat::jenisBadgeClass($aduan->jenis_aduan) }}">
                                    {{ $aduan->jenis_aduan }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium">{{ $aduan->lokasi }}</p>
                                @if ($aduan->taman)
                                    <p class="text-xs text-gray-500">{{ $aduan->taman->nama_taman }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <p>{{ $aduan->nama_pelapor }}</p>
                                @if ($aduan->kontak_pelapor)
                                    <p class="text-xs text-gray-500">{{ $aduan->kontak_pelapor }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ \App\Models\AduanMasyarakat::statusBadgeClass($aduan->status) }}">
                                    {{ $aduan->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.aduan-masyarakats.show', $aduan) }}"
                                   class="rounded border border-blue-300 px-3 py-1 text-blue-700 hover:bg-blue-50">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-gray-500">Belum ada aduan masuk.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($aduans->hasPages())
            <div class="border-t border-gray-200 px-4 py-3">{{ $aduans->links() }}</div>
        @endif
    </div>
@endsection
