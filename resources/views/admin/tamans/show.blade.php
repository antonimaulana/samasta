@extends('layouts.admin')

@section('title', 'Profil Taman')
@section('header', 'Profil Taman')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.tamans.index') }}" class="text-sm text-green-700 hover:underline">← Kembali ke Data Taman</a>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('tamans.show', $taman) }}" target="_blank" rel="noopener"
               class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Lihat Halaman Publik
            </a>
            <x-admin.can-write>
            <a href="{{ route('admin.tamans.edit', $taman) }}"
               class="rounded-lg border border-blue-300 px-4 py-2 text-sm font-medium text-blue-700 hover:bg-blue-50">
                Edit Taman
            </a>
            </x-admin.can-write>
        </div>
    </div>

    <div class="mb-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="grid gap-6 p-6 lg:grid-cols-[240px_1fr]">
            <div>
                @if ($taman->foto_url)
                    <img src="{{ $taman->foto_url }}" alt="{{ $taman->nama_taman }}"
                         class="aspect-[4/3] w-full rounded-xl border object-cover">
                @else
                    <div class="flex aspect-[4/3] w-full items-center justify-center rounded-xl border bg-gray-100 text-sm text-gray-400">
                        Tidak ada foto
                    </div>
                @endif
            </div>
            <div>
                <p class="text-xs font-semibold uppercase text-green-600">Profil Taman</p>
                <h2 class="mt-1 text-2xl font-bold text-gray-900">{{ $taman->nama_taman }}</h2>
                <span class="mt-2 inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800">
                    {{ $taman->kategori }}
                </span>

                <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-500">Luasan</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ number_format($taman->luasan, 0, ',', '.') }} m²</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-500">Koordinat</dt>
                        <dd class="mt-1 text-sm text-gray-700">
                            @if ($taman->latitude && $taman->longitude)
                                {{ $taman->latitude }}, {{ $taman->longitude }}
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold uppercase text-gray-500">Alamat</dt>
                        <dd class="mt-1 text-gray-700">{{ $taman->alamat ?: '—' }}</dd>
                    </div>
                    @if ($taman->deskripsi)
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-semibold uppercase text-gray-500">Deskripsi</dt>
                            <dd class="mt-1 whitespace-pre-line text-sm text-gray-700">{{ $taman->deskripsi }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 bg-amber-50 px-5 py-4">
            <h3 class="font-semibold text-amber-900">Riwayat Pemeliharaan</h3>
            <p class="text-xs text-amber-800/80">{{ $kinerjas->total() }} catatan operasional</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Tanggal</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Tim</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Uraian Pekerjaan</th>
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
                            <td class="max-w-xs px-4 py-3 text-gray-600">
                                {{ $kinerja->uraian_pekerjaan ? Str::limit($kinerja->uraian_pekerjaan, 80) : '—' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.pemeliharaan-tamans.export-pdf-operasional', $kinerja) }}"
                                   class="rounded border border-red-300 px-3 py-1 text-red-700 hover:bg-red-50">PDF</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-gray-500">
                                Belum ada riwayat pemeliharaan untuk taman ini.
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
