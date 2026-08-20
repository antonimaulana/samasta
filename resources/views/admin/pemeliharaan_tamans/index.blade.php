@extends('layouts.admin')

@section('title', $taman ? 'Riwayat Pemeliharaan — '.$taman->nama_taman : 'Pemeliharaan Taman')
@section('header', $taman ? 'Riwayat Pemeliharaan: '.$taman->nama_taman : 'Pemeliharaan Taman')
@section('content')
    @if ($taman)
        <div class="mb-4">
            <a href="{{ route('admin.tamans.show', $taman) }}" class="text-sm text-green-700 hover:underline">← Kembali ke profil taman</a>
        </div>
    @endif

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            @if ($taman)
                <p class="text-sm text-gray-600">Riwayat operasional pemeliharaan untuk <strong>{{ $taman->nama_taman }}</strong>.</p>
                @if ($taman->alamat)
                    <p class="mt-1 text-xs text-gray-500">{{ $taman->alamat }}</p>
                @endif
            @else
                <p class="text-sm text-gray-600">Input operasional pemeliharaan taman harian per tim pelaksana.</p>
                <p class="mt-1 text-xs text-gray-500">6 Tim: Wilayah 1–4, Nursery, dan Armada</p>
            @endif
        </div>
        <div class="flex flex-wrap gap-2">
        <x-admin.can-write>
            <a href="{{ route('admin.pemeliharaan-tamans.create', $taman ? ['taman_id' => $taman->id] : []) }}"
               class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                + Input Operasional
            </a>
        </x-admin.can-write>
        </div>
    </div>

    {{-- Filter & Export --}}
    <div class="mb-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 bg-green-50 px-5 py-3">
            <h3 class="text-sm font-bold text-green-800">Filter & Export PDF</h3>
        </div>
        <form method="GET" class="flex flex-wrap items-end gap-3 p-5">
            @if ($taman)
                <input type="hidden" name="taman_id" value="{{ $taman->id }}">
            @endif
            <div>
                <label for="tanggal_mulai" class="mb-1 block text-xs font-medium text-gray-600">
                    Dari tanggal{{ $taman ? ' (opsional)' : '' }}
                </label>
                <input type="date" name="tanggal_mulai" id="tanggal_mulai"
                       value="{{ request('tanggal_mulai', $taman ? '' : $tanggalMulai) }}"
                       class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
            </div>
            <div>
                <label for="tanggal_selesai" class="mb-1 block text-xs font-medium text-gray-600">
                    Sampai tanggal{{ $taman ? ' (opsional)' : '' }}
                </label>
                <input type="date" name="tanggal_selesai" id="tanggal_selesai"
                       value="{{ request('tanggal_selesai', $taman ? '' : $tanggalSelesai) }}"
                       class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
            </div>
            <div>
                <label for="tim" class="mb-1 block text-xs font-medium text-gray-600">Tim</label>
                <select name="tim" id="tim"
                        class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                    <option value="">Semua tim</option>
                    @foreach (\App\Models\PemeliharaanTaman::timNames() as $tim)
                        <option value="{{ $tim }}" @selected(request('tim') === $tim)>{{ $tim }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[200px] flex-1 sm:max-w-xs">
                <label for="search" class="mb-1 block text-xs font-medium text-gray-600">Cari lokasi</label>
                <input type="search" name="search" id="search" value="{{ request('search') }}"
                       placeholder="Nama lokasi..."
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
            </div>
            <button type="submit" class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-900">Terapkan</button>
            @if (request()->anyFilled(['tim', 'search', 'tanggal_mulai', 'tanggal_selesai']))
                <a href="{{ route('admin.pemeliharaan-tamans.index', $taman ? ['taman_id' => $taman->id] : []) }}"
                   class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Reset</a>
            @endif
        </form>
        @unless ($taman)
        <div class="border-t border-gray-100 px-5 py-3">
            <a href="{{ route('admin.pemeliharaan-tamans.export-pdf', [
                'tanggal_mulai' => request('tanggal_mulai', $tanggalMulai),
                'tanggal_selesai' => request('tanggal_selesai', $tanggalSelesai),
                'tim' => request('tim'),
            ]) }}"
               class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-bold text-white hover:bg-red-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export PDF
            </a>
        </div>
        @endunless
    </div>

    @if (! $taman)
    {{-- Ringkasan per tim --}}
    <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
        @foreach ($ringkasanTim as $tim => $jumlah)
            <div class="rounded-xl border border-green-100 bg-white p-3 text-center shadow-sm">
                <p class="text-xs font-bold text-gray-500">{{ $tim }}</p>
                <p class="mt-1 text-2xl font-black text-green-700">{{ $jumlah }}</p>
                <p class="text-[10px] text-gray-400">operasional</p>
            </div>
        @endforeach
    </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Tanggal</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Tim</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Lokasi</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($kinerjas as $kinerja)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-600">{{ $kinerja->tanggal->format('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-800">{{ $kinerja->tim }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium">{{ $kinerja->lokasi_pelaksanaan }}</p>
                                @if ($kinerja->uraian_pekerjaan)
                                    <p class="mt-0.5 text-xs text-gray-500">{{ Str::limit($kinerja->uraian_pekerjaan, 60) }}</p>
                                @endif
                                @if ($kinerja->persentase_progres !== null)
                                    <p class="mt-0.5 text-xs text-green-700">Progres {{ $kinerja->persentase_progres }}%</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <a href="{{ route('admin.pemeliharaan-tamans.export-pdf-operasional', $kinerja) }}"
                                       class="rounded border border-red-300 px-3 py-1 text-red-700 hover:bg-red-50"
                                       title="Export PDF operasional ini">PDF</a>
                                    <x-admin.can-write>
                                        <a href="{{ route('admin.pemeliharaan-tamans.edit', $kinerja) }}"
                                           class="rounded border border-blue-300 px-3 py-1 text-blue-700 hover:bg-blue-50">Edit</a>
                                    </x-admin.can-write>
                                    <x-admin.can-delete>
                                        <form action="{{ route('admin.pemeliharaan-tamans.destroy', $kinerja) }}" method="POST"
                                              onsubmit="return confirm('Hapus data operasional ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded border border-red-300 px-3 py-1 text-red-700 hover:bg-red-50">Hapus</button>
                                        </form>
                                    </x-admin.can-delete>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-gray-500">
                                @if ($taman)
                                    Belum ada riwayat pemeliharaan untuk taman ini.
                                @else
                                    Belum ada operasional untuk periode ini.
                                @endif
                                @if ($canWrite ?? auth()->user()?->canWrite())
                                    <a href="{{ route('admin.pemeliharaan-tamans.create', $taman ? ['taman_id' => $taman->id] : []) }}" class="text-green-700 underline">Input sekarang</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($kinerjas->hasPages())
            <div class="border-t border-gray-200 px-4 py-3">{{ $kinerjas->links() }}</div>
        @endif
    </div>
@endsection
